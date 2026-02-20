<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:utilisateurs,email',
            'mot_de_passe' => 'required|string|min:8|confirmed',
            'role' => 'required|in:artisan,acheteur,administrateur',
            'telephone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $utilisateur = Utilisateur::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'mot_de_passe' => Hash::make($request->mot_de_passe),
                'role' => $request->role,
                'telephone' => $request->telephone,
                'email_verifie_le' => now(), // Temporaire, à implémenter avec email verification
                'actif' => true,
            ]);

            // Créer le profil spécifique selon le rôle
            switch ($request->role) {
                case 'artisan':
                    $utilisateur->artisan()->create([
                        'compte_valide' => false, // Nécessite validation admin
                    ]);
                    break;
                case 'acheteur':
                    $utilisateur->acheteur()->create([]);
                    break;
                case 'administrateur':
                    $utilisateur->administrateur()->create([
                        'niveau_acces' => 'moderateur',
                    ]);
                    break;
            }

            // Créer le token Sanctum
            $token = $utilisateur->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Inscription réussie',
                'data' => [
                    'utilisateur' => $utilisateur->loadMissing(['artisan', 'acheteur', 'administrateur']),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'inscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Connexion d'un utilisateur
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'mot_de_passe' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Vérifier les identifiants manuellement
            $utilisateur = \App\Models\Utilisateur::where('email', $request->email)->first();
            
            if (!$utilisateur || !\Illuminate\Support\Facades\Hash::check($request->mot_de_passe, $utilisateur->mot_de_passe)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiants incorrects'
                ], 401);
            }

            // Vérifier si le compte est actif
            if (!$utilisateur->actif) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compte désactivé'
                ], 403);
            }

            // Vérifier si l'email est vérifié
            if (!$utilisateur->email_verifie_le) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email non vérifié'
                ], 403);
            }

            // Révoquer les anciens tokens
            $utilisateur->tokens()->delete();

            // Créer le nouveau token
            $token = $utilisateur->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'data' => [
                    'utilisateur' => $utilisateur->loadMissing(['artisan', 'acheteur', 'administrateur']),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la connexion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Déconnexion d'un utilisateur
     */
    public function logout(Request $request)
    {
        try {
            // Révoquer le token actuel
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la déconnexion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Révoquer tous les tokens de l'utilisateur
     */
    public function revokeAllTokens(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tous les tokens ont été révoqués'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la révocation des tokens',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rafraîchir le token
     */
    public function refresh(Request $request)
    {
        try {
            $utilisateur = $request->user();

            // Révoquer l'ancien token
            $request->user()->currentAccessToken()->delete();

            // Créer le nouveau token
            $token = $utilisateur->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token rafraîchi',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rafraîchissement du token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le profil de l'utilisateur connecté
     */
    public function profile(Request $request)
    {
        try {
            $utilisateur = $request->user()->loadMissing(['artisan', 'acheteur', 'administrateur']);

            return response()->json([
                'success' => true,
                'data' => $utilisateur
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le profil de l'utilisateur
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:100',
            'prenom' => 'sometimes|string|max:100',
            'telephone' => 'sometimes|nullable|string|max:20',
            'avatar' => 'sometimes|nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $utilisateur = $request->user();
            $utilisateur->update($request->only(['nom', 'prenom', 'telephone', 'avatar']));

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour',
                'data' => $utilisateur->loadMissing(['artisan', 'acheteur', 'administrateur'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mot_de_passe_actuel' => 'required|string',
            'mot_de_passe' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $utilisateur = $request->user();

            // Vérifier l'ancien mot de passe
            if (!Hash::check($request->mot_de_passe_actuel, $utilisateur->mot_de_passe)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mot de passe actuel incorrect'
                ], 400);
            }

            // Mettre à jour le mot de passe
            $utilisateur->update([
                'mot_de_passe' => Hash::make($request->mot_de_passe)
            ]);

            // Révoquer tous les tokens sauf l'actuel
            $utilisateur->tokens()->where('id', '!=', $utilisateur->currentAccessToken()->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe changé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de mot de passe',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
