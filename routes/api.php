<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ArtisanController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\ValidationController;
use App\Http\Controllers\Api\PanierController;
use App\Http\Controllers\Api\CommandeController;
use App\Http\Controllers\Api\AvisController;
use App\Http\Controllers\Api\NotificationController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | CATALOGUE (Public - sans authentification)
    |--------------------------------------------------------------------------
    */
    Route::prefix('catalog')->group(function () {
        Route::get('categories',              [CatalogController::class, 'categories']);
        Route::get('oeuvres',                 [CatalogController::class, 'oeuvres']);
        Route::get('oeuvres/{id}',            [CatalogController::class, 'showOeuvre']);
        Route::get('oeuvres/{id}/similar',    [CatalogController::class, 'similarOeuvres']);
        Route::get('stats',                   [CatalogController::class, 'stats']);
        Route::get('oeuvres/{id}/avis',       [AvisController::class, 'getAvisOeuvre']);
    });

    /*
    |--------------------------------------------------------------------------
    | AUTH (Public - register & login sans auth)
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login',    [AuthController::class, 'login']);
    });

    /*
    |--------------------------------------------------------------------------
    | AUTH (Protégé - logout)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
        Route::post('logout',     [AuthController::class, 'logout']);
        Route::post('revoke-all', [AuthController::class, 'revokeAllTokens']);
    });

    /*
    |--------------------------------------------------------------------------
    | PROFIL UTILISATEUR CONNECTÉ (/me)
    | Accessible à tous les rôles authentifiés avec email vérifié
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'email.verified'])->prefix('me')->group(function () {
        Route::get('/',       [AuthController::class, 'profile']);
        Route::put('/',       [AuthController::class, 'updateProfile']);
        Route::post('avatar', [AuthController::class, 'uploadAvatar']);
        Route::post('password', [AuthController::class, 'changePassword']);
    });

    /*
    |--------------------------------------------------------------------------
    | ARTISAN
    | auth:sanctum + email vérifié + rôle artisan
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'email.verified', 'role:artisan'])
        ->prefix('artisan')
        ->group(function () {

            // Dashboard accessible à tous les artisans
            Route::get('dashboard', [ArtisanController::class, 'dashboard']);

            // Routes réservées aux artisans avec compte validé
            Route::middleware('validated.artisan')->group(function () {
                Route::get('oeuvres',          [ArtisanController::class, 'mesOeuvres']);
                Route::post('oeuvres',         [ArtisanController::class, 'creerOeuvre']);
                Route::get('oeuvres/{id}',     [ArtisanController::class, 'detailOeuvre']);
                Route::put('oeuvres/{id}',     [ArtisanController::class, 'mettreAJourOeuvre']);
                Route::delete('oeuvres/{id}',  [ArtisanController::class, 'supprimerOeuvre']);

                Route::post('oeuvres/{id}/images',        [ImageController::class, 'uploadImages']);
                Route::delete('images/{imageId}',         [ImageController::class, 'supprimerImage']);
            });
        });

    /*
    |--------------------------------------------------------------------------
    | ACHETEUR
    | auth:sanctum + email vérifié + rôle acheteur
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'email.verified', 'role:acheteur'])
        ->prefix('acheteur')
        ->group(function () {
            Route::get('panier',           [PanierController::class, 'getPanier']);
            Route::post('panier',          [PanierController::class, 'ajouter']);
            Route::delete('panier/{id}',   [PanierController::class, 'supprimer']);
            Route::put('panier/{id}',      [PanierController::class, 'modifierQuantite']);

            Route::get('commandes',        [CommandeController::class, 'getCommandes']);
            Route::post('commandes',       [CommandeController::class, 'creerCommande']);

            Route::post('avis',            [AvisController::class, 'creerAvis']);
            Route::get('notifications',    [NotificationController::class, 'getNotifications']);
        });

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    | auth:sanctum + email vérifié + rôle administrateur
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'email.verified', 'role:administrateur'])
        ->prefix('admin')
        ->group(function () {
            Route::get('dashboard',                     [ValidationController::class, 'dashboard']);

            Route::get('oeuvres/en-attente',            [ValidationController::class, 'getOeuvresEnAttente']);
            Route::put('oeuvres/{id}/valider',          [ValidationController::class, 'validerOeuvre']);
            Route::put('oeuvres/{id}/refuser',          [ValidationController::class, 'refuserOeuvre']);
        });

});
