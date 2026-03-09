<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes publiques d'authentification
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Routes protégées par authentification
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('revoke-all', [AuthController::class, 'revokeAllTokens']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
});

// Routes protégées par authentification et email vérifié
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    
    // Routes pour tous les utilisateurs vérifiés
    Route::prefix('user')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('refresh-token', [AuthController::class, 'refresh']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('revoke-tokens', [AuthController::class, 'revokeAllTokens']);
    });
});

// Routes pour les artisans
Route::middleware(['auth:sanctum', 'verified', 'role:artisan'])->prefix('artisan')->group(function () {
    Route::get('dashboard', function () {
        return response()->json([
            'success' => true,
            'message' => 'Dashboard Artisan',
            'data' => [
                'stats' => [
                    'oeuvres_count' => 0,
                    'ventes_count' => 0,
                    'revenus_total' => 0,
                ]
            ]
        ]);
    });
    
    Route::get('profile', function (Request $request) {
        $user = $request->user()->loadMissing('artisan');
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    });
});

// Routes pour les artisans validés uniquement
Route::middleware(['auth:sanctum', 'verified', 'validated.artisan'])->prefix('artisan')->group(function () {
    Route::get('oeuvres', function () {
        return response()->json([
            'success' => true,
            'message' => 'Liste des œuvres (artisan validé)',
            'data' => []
        ]);
    });
    
    Route::post('oeuvres', function () {
        return response()->json([
            'success' => true,
            'message' => 'Création d\'œuvre (artisan validé)',
            'data' => []
        ]);
    });
});

// Routes pour les acheteurs
Route::middleware(['auth:sanctum', 'verified', 'role:acheteur'])->prefix('acheteur')->group(function () {
    Route::get('dashboard', function () {
        return response()->json([
            'success' => true,
            'message' => 'Dashboard Acheteur',
            'data' => [
                'stats' => [
                    'commandes_count' => 0,
                    'favoris_count' => 0,
                    'total_depense' => 0,
                ]
            ]
        ]);
    });
    
    Route::get('orders', function () {
        return response()->json([
            'success' => true,
            'message' => 'Historique des commandes',
            'data' => []
        ]);
    });
    
    Route::get('favorites', function () {
        return response()->json([
            'success' => true,
            'message' => 'Liste des favoris',
            'data' => []
        ]);
    });
});

// Routes pour les administrateurs
Route::middleware(['auth:sanctum', 'verified', 'role:administrateur'])->prefix('admin')->group(function () {
    Route::get('dashboard', function () {
        return response()->json([
            'success' => true,
            'message' => 'Dashboard Administrateur',
            'data' => [
                'stats' => [
                    'users_count' => 0,
                    'artisans_count' => 0,
                    'oeuvres_count' => 0,
                    'transactions_count' => 0,
                ]
            ]
        ]);
    });
    
    Route::get('users', function () {
        return response()->json([
            'success' => true,
            'message' => 'Liste des utilisateurs',
            'data' => []
        ]);
    });
    
    Route::get('artisans', function () {
        return response()->json([
            'success' => true,
            'message' => 'Liste des artisans',
            'data' => []
        ]);
    });
    
    Route::get('oeuvres/pending', function () {
        return response()->json([
            'success' => true,
            'message' => 'Œuvres en attente de validation',
            'data' => []
        ]);
    });
});

// Routes de test pour vérifier les middlewares
Route::prefix('test')->group(function () {
    Route::get('public', function () {
        return response()->json([
            'success' => true,
            'message' => 'Route publique - accessible à tous'
        ]);
    });
    
    Route::middleware('auth:sanctum')->get('auth', function () {
        return response()->json([
            'success' => true,
            'message' => 'Route authentifiée - nécessite un token valide'
        ]);
    });
    
    Route::middleware(['auth:sanctum', 'verified'])->get('verified', function () {
        return response()->json([
            'success' => true,
            'message' => 'Route vérifiée - nécessite un email vérifié'
        ]);
    });
    
    Route::middleware(['auth:sanctum', 'role:artisan'])->get('artisan', function () {
        return response()->json([
            'success' => true,
            'message' => 'Route artisan - accès réservé aux artisans'
        ]);
    });
    
    Route::middleware(['auth:sanctum', 'role:acheteur'])->get('acheteur', function () {
        return response()->json([
            'success' => true,
            'message' => 'Route acheteur - accès réservé aux acheteurs'
        ]);
    });
    
    Route::middleware(['auth:sanctum', 'role:administrateur'])->get('admin', function () {
        return response()->json([
            'success' => true,
            'message' => 'Route admin - accès réservé aux administrateurs'
        ]);
    });
    
    Route::middleware(['auth:sanctum', 'role:artisan,acheteur'])->get('multi-role', function () {
        return response()->json([
            'success' => true,
            'message' => 'Route multi-rôles - accès pour artisans ou acheteurs'
        ]);
    });
});

// Route d'information sur l'API
Route::get('info', function () {
    return response()->json([
        'success' => true,
        'message' => 'ArtisanConnect API v1.0',
        'version' => '1.0.0',
        'endpoints' => [
            'auth' => [
                'POST /api/auth/register' => 'Inscription',
                'POST /api/auth/login' => 'Connexion',
                'POST /api/auth/logout' => 'Déconnexion (authentifié)',
                'GET /api/auth/profile' => 'Profil utilisateur (authentifié)',
            ],
            'test' => [
                'GET /api/test/public' => 'Route publique',
                'GET /api/test/auth' => 'Route authentifiée',
                'GET /api/test/verified' => 'Route vérifiée',
                'GET /api/test/artisan' => 'Route artisan',
                'GET /api/test/acheteur' => 'Route acheteur',
                'GET /api/test/admin' => 'Route admin',
            ]
        ]
    ]);
});
