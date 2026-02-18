<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ArtisanController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\ValidationController;
use App\Http\Controllers\Api\PanierController;
use App\Http\Controllers\Api\CommandeController;

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

// Routes publiques du catalogue
Route::prefix('catalog')->group(function () {
    Route::get('categories', [CatalogController::class, 'categories']);
    Route::get('oeuvres', [CatalogController::class, 'oeuvres']);
    Route::get('oeuvres/{id}', [CatalogController::class, 'showOeuvre']);
    Route::get('oeuvres/{id}/similar', [CatalogController::class, 'similarOeuvres']);
    Route::get('stats', [CatalogController::class, 'stats']);
});

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
    Route::get('oeuvres', [ArtisanController::class, 'mesOeuvres']);
    Route::post('oeuvres', [ArtisanController::class, 'creerOeuvre']);
    Route::get('oeuvres/{id}', [ArtisanController::class, 'detailOeuvre']);
    Route::put('oeuvres/{id}', [ArtisanController::class, 'mettreAJourOeuvre']);
    Route::delete('oeuvres/{id}', [ArtisanController::class, 'supprimerOeuvre']);
    Route::post('oeuvres/{id}/soumettre', [ArtisanController::class, 'soumettreOeuvre']);
    
    // Routes pour la gestion des images
    Route::post('oeuvres/{id}/images', [ImageController::class, 'uploadImages']);
    Route::delete('images/{imageId}', [ImageController::class, 'supprimerImage']);
    Route::put('images/{imageId}/ordre', [ImageController::class, 'reorganiserImages']);
    Route::put('images/{imageId}/principale', [ImageController::class, 'definirImagePrincipale']);
    Route::get('images/{imageId}', [ImageController::class, 'getImageInfo']);
    Route::post('images/{imageId}/optimiser', [ImageController::class, 'optimiserImage']);
});

// Routes pour les acheteurs
Route::middleware(['auth:sanctum', 'verified', 'role:acheteur'])->prefix('acheteur')->group(function () {
    Route::get('dashboard', function () {
        return response()->json([
            'success' => true,
            'message' => 'Dashboard Acheteur',
            'data' => [
                'stats' => [
                    'commandes_count' => 5,
                    'favoris_count' => 12,
                    'total_depense' => 250000,
                ]
            ]
        ]);
    });
    
    // Routes du panier
    Route::get('panier', [PanierController::class, 'getPanier']);
    Route::post('panier/ajouter', [PanierController::class, 'ajouter']);
    Route::put('panier/{id}', [PanierController::class, 'mettreAJour']);
    Route::delete('panier/{id}', [PanierController::class, 'supprimer']);
    Route::delete('panier', [PanierController::class, 'vider']);
    Route::get('panier/stats', [PanierController::class, 'getStats']);
    
    // Routes des commandes
    Route::get('commandes', [CommandeController::class, 'getCommandes']);
    Route::post('commandes', [CommandeController::class, 'creerCommande']);
    Route::get('commandes/{id}', [CommandeController::class, 'getDetailCommande']);
    Route::put('commandes/{id}/annuler', [CommandeController::class, 'annulerCommande']);
    Route::put('commandes/{id}/confirmer-reception', [CommandeController::class, 'confirmerReception']);
    Route::get('commandes/{id}/transactions', [CommandeController::class, 'getTransactionsCommande']);
    
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
                    'total_utilisateurs' => 150,
                    'total_artisans' => 45,
                    'total_oeuvres' => 320,
                    'total_ventes' => 89,
                ]
            ]
        ]);
    });
    
    Route::get('artisans', function () {
        return response()->json([
            'success' => true,
            'message' => 'Liste des artisans',
            'data' => []
        ]);
    });
    
    Route::get('oeuvres', function () {
        return response()->json([
            'success' => true,
            'message' => 'Liste des œuvres',
            'data' => []
        ]);
    });
    
    // Routes de validation
    Route::get('oeuvres/en-attente', [ValidationController::class, 'getOeuvresEnAttente']);
    Route::put('oeuvres/{id}/valider', [ValidationController::class, 'validerOeuvre']);
    Route::put('oeuvres/{id}/refuser', [ValidationController::class, 'refuserOeuvre']);
    Route::get('validation/statistiques', [ValidationController::class, 'getStatistiquesValidation']);
    Route::get('validation/historique', [ValidationController::class, 'getHistoriqueValidations']);
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
            'catalog' => [
                'GET /api/catalog/categories' => 'Liste des catégories',
                'GET /api/catalog/oeuvres' => 'Liste des œuvres avec filtres',
                'GET /api/catalog/oeuvres/{id}' => 'Détails d\'une œuvre',
                'GET /api/catalog/oeuvres/{id}/similar' => 'Œuvres similaires',
                'GET /api/catalog/stats' => 'Statistiques du catalogue',
            ],
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
