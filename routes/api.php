<?php



use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ArtisanController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\ValidationController;
use App\Http\Controllers\Api\PanierController;
use App\Http\Controllers\Api\CommandeController;
use App\Http\Controllers\Api\AvisController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\FavoriController;
use App\Http\Controllers\Api\PaiementController;





Route::prefix('v1')->group(function () {

    // -----------------------
    // AUTH (Public API)
    // -----------------------
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    // -----------------------
    // AUTH (Protégé API)
    // -----------------------
    Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('revoke-all', [AuthController::class, 'revokeAllTokens']);
        Route::get('profile', [AuthController::class, 'profil']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });

    // -----------------------
    // ADMINISTRATEUR
    // -----------------------
    Route::middleware(['auth:sanctum', 'email.verified', 'role:administrateur'])
        ->prefix('admin')
        ->group(function () {
            Route::get('dashboard', [ValidationController::class, 'dashboard']);
            Route::get('oeuvres/en-attente', [ValidationController::class, 'getOeuvresEnAttente']);
            Route::put('oeuvres/{id}/valider', [ValidationController::class, 'validerOeuvre']);
            Route::put('oeuvres/{id}/refuser', [ValidationController::class, 'refuserOeuvre']);
        });

    // -----------------------
    // Routes de test API
    // -----------------------
    Route::prefix('test')->group(function () {
        Route::get('public', fn() => response()->json(['message' => 'Public']));
        Route::middleware('auth:sanctum')->get('auth', fn() => response()->json(['message' => 'Auth']));
        Route::middleware(['auth:sanctum', 'email.verified'])->get('verified', fn() => response()->json(['message' => 'Verified']));
        Route::middleware(['auth:sanctum', 'role:artisan'])->get('artisan', fn() => response()->json(['message' => 'Artisan']));
        Route::middleware(['auth:sanctum', 'role:acheteur'])->get('acheteur', fn() => response()->json(['message' => 'Acheteur']));
        Route::middleware(['auth:sanctum', 'role:administrateur'])->get('admin', fn() => response()->json(['message' => 'Admin']));
    });

    // -----------------------
    // Info API
    // -----------------------
    Route::get('info', fn() => response()->json([
        'message' => 'ArtisanConnect API v1',
        'version' => '1.0.0'
    ]));
});
