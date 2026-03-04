<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Artisan;
use App\Models\Acheteur;
use App\Models\Administrateur;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:artisan,acheteur',
            'telephone' => 'nullable|string|max:15',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'actif' => 'required|boolean',
        ]);

        try {

            DB::beginTransaction();

            // ✅ CORRECTION 1 : gestion avatar AVANT création
            if ($request->hasFile('avatar')) {
                $data['avatar'] = $request->file('avatar')
                    ->store('avatars', 'public');
            }

            // ✅ CORRECTION 2 : PAS de Hash::make()
            // Le password est hashé automatiquement via cast dans User model
            $user = User::create($data);

            // Création profil selon rôle
            switch ($user->role) {
                case 'administrateur':
                    Administrateur::create([
                        'user_id' => $user->id,
                    ]);
                    break;

                case 'artisan':
                    Artisan::create([
                        'user_id' => $user->id,
                        'compte_valide' => false,
                    ]);
                    break;

                case 'acheteur':
                    Acheteur::create([
                        'user_id' => $user->id,
                    ]);
                    break;
            }

            $user->sendEmailVerificationNotification();

            // ✅ CORRECTION 3 : createToken fonctionne normalement
            $token = $user->createToken('authToken')->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Utilisateur créé avec succès',
                'user' => $user,
                'token' => $token,
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => 'une erreur estsurvenue,veuillez réesseyez',
            ], 500);
        }
    }

    public function login(Request $request)
    {
        // ✅ CORRECTION 4 : validation propre (pas de unique, pas de confirmed)
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Tentative d’authentification
        if (!Auth::attempt($data)) {
            return response()->json([
                'error' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        // ✅ CORRECTION 5 : récupération propre du modèle User
        $user = User::find(Auth::id());

        // ✅ CORRECTION 6 : suppression via relation ()
        $user->tokens()->delete();

        // Nouveau token
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request )
    {

     $request->user()->currentAccessToken()->delete();
     return response()->json([
        'message'=>'Deconnexion réussie',
     ],200);
    }
}
