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
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | INFO API
    |--------------------------------------------------------------------------
    */
    Route::get('info', fn() => response()->json([
        'message' => 'ArtisanConnect API v1',
        'version' => '1.0.0'
    ]));

    /*
    |--------------------------------------------------------------------------
    | ROUTES DE TEST
    |--------------------------------------------------------------------------
    */
    Route::prefix('test')->group(function () {
        Route::get('public',   fn() => response()->json(['message' => 'Public']));
        Route::middleware('auth:sanctum')->get('auth', fn() => response()->json(['message' => 'Auth']));
        Route::middleware(['auth:sanctum', 'email.verified'])->get('verified', fn() => response()->json(['message' => 'Verified']));
        Route::middleware(['auth:sanctum', 'role:artisan'])->get('artisan', fn() => response()->json(['message' => 'Artisan']));
        Route::middleware(['auth:sanctum', 'role:acheteur'])->get('acheteur', fn() => response()->json(['message' => 'Acheteur']));
        Route::middleware(['auth:sanctum', 'role:administrateur'])->get('admin', fn() => response()->json(['message' => 'Admin']));
    });

    /*
    |--------------------------------------------------------------------------
    | CATALOGUE (Public - sans authentification)
    |--------------------------------------------------------------------------
    */
    Route::prefix('catalog')->group(function () {
        Route::get('categories',               [CatalogController::class, 'categories']);
        Route::get('oeuvres',                  [CatalogController::class, 'oeuvres']);
        Route::get('oeuvres/{id}',             [CatalogController::class, 'showOeuvre']);
        Route::get('oeuvres/{id}/similar',     [CatalogController::class, 'similarOeuvres']);
        Route::get('stats',                    [CatalogController::class, 'stats']);
        Route::get('oeuvres/{id}/avis',        [AvisController::class, 'getAvisOeuvre']);
        Route::get('artisans/{id}/avis/stats', [AvisController::class, 'getStatistiquesAvisArtisan']);
    });

    /*
    |--------------------------------------------------------------------------
    | WEBHOOKS (Public mais sécurisé par signature)
    |--------------------------------------------------------------------------
    */
    Route::prefix('webhooks')->group(function () {
        Route::post('paiement', [PaiementController::class, 'webhookConfirmation']);
    });

    /*
    |--------------------------------------------------------------------------
    | AUTH (Public - register & login)
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login',    [AuthController::class, 'login']);
    });

    /*
    |--------------------------------------------------------------------------
    | AUTH (Protégé - logout, revoke)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
        Route::post('logout',          [AuthController::class, 'logout']);
        Route::post('revoke-all',      [AuthController::class, 'revokeAllTokens']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });

    /*
    |--------------------------------------------------------------------------
    | PROFIL CONNECTÉ (/me)
    | Accessible à tous les rôles authentifiés avec email vérifié
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'email.verified'])->prefix('me')->group(function () {
        Route::get('/',       [AuthController::class, 'profil']);
        Route::put('/',       [AuthController::class, 'updateProfile']);
        Route::post('avatar', [AuthController::class, 'uploadAvatar']);
    });

    /*
    |--------------------------------------------------------------------------
    | ARTISAN
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'email.verified', 'role:artisan'])
        ->prefix('artisan')
        ->group(function () {
            Route::get('dashboard',              [ArtisanController::class, 'dashboard']);
            Route::get('ventes',                 [ArtisanController::class, 'mesVentes']);

            Route::get('oeuvres',                [ArtisanController::class, 'mesOeuvres']);
            Route::post('oeuvres',               [ArtisanController::class, 'creerOeuvre']);
            Route::get('oeuvres/{id}',           [ArtisanController::class, 'detailOeuvre']);
            Route::put('oeuvres/{id}',           [ArtisanController::class, 'mettreAJourOeuvre']);
            Route::delete('oeuvres/{id}',        [ArtisanController::class, 'supprimerOeuvre']);
            Route::put('oeuvres/{id}/soumettre', [ArtisanController::class, 'soumettre']);

            Route::post('oeuvres/{id}/images',   [ImageController::class, 'uploadImages']);
            Route::delete('images/{imageId}',    [ImageController::class, 'supprimerImage']);
        });

    /*
    |--------------------------------------------------------------------------
    | ACHETEUR
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'email.verified', 'role:acheteur'])
        ->prefix('acheteur')
        ->group(function () {
            // Panier
            Route::get('panier',                   [PanierController::class, 'getPanier']);
            Route::post('panier',                  [PanierController::class, 'ajouter']);
            Route::put('panier/{id}',              [PanierController::class, 'modifierQuantite']);
            Route::delete('panier/{id}',           [PanierController::class, 'supprimer']);

            // Commandes
            Route::get('commandes',                [CommandeController::class, 'getCommandes']);
            Route::post('commandes',               [CommandeController::class, 'creerCommande']);

            // Paiement
            Route::post('paiement/initier',        [PaiementController::class, 'initierPaiement']);
            Route::post('paiement/mock-confirmer', [PaiementController::class, 'mockConfirmer']);

            // Avis
            Route::post('avis',                    [AvisController::class, 'creerAvis']);

            // Favoris
            Route::get('favoris',                  [FavoriController::class, 'getFavoris']);
            Route::post('favoris',                 [FavoriController::class, 'ajouter']);
            Route::delete('favoris/{oeuvreId}',    [FavoriController::class, 'supprimer']);

            // Notifications
            Route::get('notifications',            [NotificationController::class, 'getNotifications']);
        });

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'email.verified', 'role:administrateur'])
        ->prefix('admin')
        ->group(function () {
            Route::get('dashboard',            [ValidationController::class, 'dashboard']);
            Route::get('oeuvres/en-attente',   [ValidationController::class, 'getOeuvresEnAttente']);
            Route::put('oeuvres/{id}/valider', [ValidationController::class, 'validerOeuvre']);
            Route::put('oeuvres/{id}/refuser', [ValidationController::class, 'refuserOeuvre']);
        });
});
