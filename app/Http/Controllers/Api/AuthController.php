<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Artisan;
use App\Models\Acheteur;
use App\Models\Administrateur;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $registrationStep = 'validation';
        $reference = (string) Str::uuid();
        $registrationQueries = [];

        DB::listen(function ($query) use (&$registrationQueries, &$registrationStep): void {
            if (count($registrationQueries) < 20) {
                $registrationQueries[] = [
                    'step' => $registrationStep,
                    'sql' => $query->sql,
                ];
            }
        });

        try {
            $data = $request->validate([
                'name'      => 'required|string|max:20',
                'email'     => 'required|email|unique:users,email',
                'password'  => 'required|string|min:8|confirmed',
                'role'      => 'required|in:artisan,acheteur',
                'telephone' => 'nullable|string|max:15',
                'avatar'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $registrationStep = 'begin_transaction';
            DB::beginTransaction();

            if ($request->hasFile('avatar')) {
                $registrationStep = 'store_avatar';
                $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }

            $data['actif'] = true;

            $registrationStep = 'create_user';
            $user = User::create($data);

            $registrationStep = 'verify_transaction_after_user';
            DB::select('SELECT 1');

            switch ($user->role) {
                case 'artisan':
                    $registrationStep = 'create_artisan_profile';
                    Artisan::create(['user_id' => $user->id, 'compte_valide' => false]);
                    break;
                case 'acheteur':
                    $registrationStep = 'create_buyer_profile';
                    Acheteur::create(['user_id' => $user->id]);
                    break;
            }

            // Email de vérification (RG05 : obligatoire avant soumission d'œuvre)
            $registrationStep = 'send_verification_email';
            $user->sendEmailVerificationNotification();

            // CORRECTION : Auth::login() retiré — inutile en API pure Sanctum
            // Sanctum gère l'auth via token uniquement, pas de session
            $registrationStep = 'create_access_token';
            $token = $user->createToken('authToken')->plainTextToken;

            $registrationStep = 'commit_transaction';
            DB::commit();

            // CORRECTION : format de réponse harmonisé { success, data, message }
            return response()->json([
                'success' => true,
                'data'    => [
                    'user'  => $user,
                    'token' => $token,
                ],
                'message' => 'Utilisateur créé avec succès',
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // CORRECTION : format de réponse harmonisé
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Erreur de validation',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            $databaseError = $e->getPrevious()?->getMessage() ?? $e->getMessage();
            $databaseError = preg_replace(
                '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i',
                '[email redacted]',
                $databaseError
            );

            Log::error('Échec de l’inscription', [
                'reference' => $reference,
                'step' => $registrationStep,
                'exception' => get_class($e),
                'sqlstate' => (string) $e->getCode(),
                'previous_exception' => $e->getPrevious() ? get_class($e->getPrevious()) : null,
                'previous_sqlstate' => $e->getPrevious() ? (string) $e->getPrevious()->getCode() : null,
                'database_error' => $databaseError,
                'successful_queries' => $registrationQueries,
            ]);

            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Une erreur est survenue pendant l’inscription.',
                'reference' => $reference,
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // CORRECTION : le 2ème paramètre "true" (remember me) retiré
        // Inutile en API Sanctum — les tokens ont leur propre durée de vie
        if (!Auth::attempt($data)) {
            // CORRECTION : format de réponse harmonisé
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Email ou mot de passe incorrect',
            ], 401);
        }

        $user = User::find(Auth::id());
        $user->tokens()->delete();
        $token = $user->createToken('authToken')->plainTextToken;

        // CORRECTION : format de réponse harmonisé
        return response()->json([
            'success' => true,
            'data'    => [
                'user'  => $user,
                'token' => $token,
            ],
            'message' => 'Connexion réussie',
        ], 200);
    }

    public function logout(Request $request)
    {
        // CORRECTION : Auth::logout() retiré — inutile en API pure Sanctum
        // En API, on révoque uniquement le token courant, pas de session à détruire
        $request->user()->currentAccessToken()->delete();

        // CORRECTION : format de réponse harmonisé
        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Déconnexion réussie',
        ], 200);
    }

    public function profil(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'artisan') {
            $user->load('artisan');
        } elseif ($user->role === 'acheteur') {
            $user->load('acheteur');
        } elseif ($user->role === 'administrateur') {
            $user->load('administrateur');
        }

        // CORRECTION : message corrigé — était "Profil mis à jour" alors que c'est un GET
        return response()->json([
            'success' => true,
            'data'    => ['user' => $user],
            'message' => 'Profil récupéré avec succès',
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'      => 'sometimes|string|max:20',
            'telephone' => 'sometimes|string|max:15',

            'bio'             => 'sometimes|string|max:255',
            'specialite'      => 'sometimes|string|max:30',
            'region'          => 'sometimes|string|max:30',
            'atelier_adresse' => 'sometimes|string|max:30',

            'adresse_livraison' => 'sometimes|string|max:30',
            'preferences'       => 'sometimes|string|max:20',
        ]);

        // Mise à jour des champs communs (table users)
        $user->update(array_intersect_key($data, array_flip(['name', 'telephone'])));

        // CORRECTION : vérification du rôle ET de l'existence de la relation avant update
        // Sans ce check, si $user->artisan est null → Call to member function update() on null
        if ($user->role === 'artisan' && $user->artisan) {
            $user->artisan->update(array_intersect_key($data, array_flip([
                'bio', 'specialite', 'region', 'atelier_adresse'
            ])));
        }

        // CORRECTION : $user->artisan remplacé par $user->acheteur (copier/coller raté)
        // + vérification du rôle pour ne pas appeler acheteur sur un artisan
        if ($user->role === 'acheteur' && $user->acheteur) {
            $user->acheteur->update(array_intersect_key($data, array_flip([
                'adresse_livraison', 'preferences'
            ])));
        }

        // CORRECTION : return manquant — la méthode ne retournait rien du tout
        // $user->fresh() recharge depuis la DB pour retourner les données réellement sauvegardées
        return response()->json([
            'success' => true,
            'data'    => ['user' => $user->fresh()],
            'message' => 'Profil mis à jour',
        ], 200);
    }

    public function uploadAvatar(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png|max:2048'
        ]);

        // Suppression de l'ancien avatar pour éviter les fichiers orphelins en storage
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        // CORRECTION : format de réponse harmonisé
        return response()->json([
            'success' => true,
            'data'    => ['avatar_url' => asset('storage/' . $path)],
            'message' => 'Avatar mis à jour',
        ], 200);
    }


    public function revokeAllTokens(Request $request)
    {

        $request->user()->tokens()->delete();
        return response()->json([
           'success' => true,
            'data'    =>null,
            'message' => 'tous les appareils sont déconectés',
        ]);

    }

    public function changePassword(Request $request)
    {
      $user=$request->user();
      $data=$request->validate([
      'current_password'=>'required',
      'password'=>'required|min:8|confirmed',
      ]);

      if(Hash::check($data['current_password'],$user->password))
        {
         $user->update(['password'=>Hash::make($data['password'])]);
        }
    else
    {
        return response()->json([
           'success' => false,
            'data'    =>null,
            'message' => 'erreur,veuillez réessayez',
        ],422);
    }

    $request->user()->tokens()->delete();
        return response()->json([
           'success' => true,
            'data'    =>null,
            'message' => 'mot de passe modifié',
        ],200);
}
}
