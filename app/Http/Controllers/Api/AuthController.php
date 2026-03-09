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
        try {
            $data = $request->validate([
                'name'      => 'required|string|max:20',
                'email'     => 'required|email|unique:users,email',
                'password'  => 'required|string|min:8|confirmed',
                'role'      => 'required|in:artisan,acheteur',
                'telephone' => 'nullable|string|max:15',
                'avatar'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            DB::beginTransaction();

            if ($request->hasFile('avatar')) {
                $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }

            $data['actif'] = true;

            $user = User::create($data);

            switch ($user->role) {
                case 'artisan':
                    Artisan::create(['user_id' => $user->id, 'compte_valide' => false]);
                    break;
                case 'acheteur':
                    Acheteur::create(['user_id' => $user->id]);
                    break;
            }

            // Connexion session ET token
            Auth::login($user);
            $token = $user->createToken('authToken')->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Utilisateur créé avec succès',
                'user'    => $user,
                'token'   => $token,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'  => 'Une erreur est survenue',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($data, true)) {
            return response()->json([
                'error' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        $user = User::find(Auth::id());
        $user->tokens()->delete();
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'user'    => $user,
            'token'   => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ], 200);
    }

    public function profil(Request $request)
    {
        $user = $request->user();
        $user->load($user->role);

        return response()->json([
            'message' => 'Utilisateur connecté',
            'user'    => $user,
        ], 200);
    }
}
