<?php

use App\Http\Controllers\Api\ArtisanController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pages publiques
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('home'))->name('home');
Route::get('/catalogue', fn() => view('catalogue.categories'))->name('catalogue.categories');
Route::get('/catalogue/oeuvres/{id}', fn($id) => view('catalogue.show', ['id' => $id]))->name('catalogue.oeuvre');

// Profil public artisan — charge le modèle User et le passe à la vue
Route::get('/catalogue/artisans/{id}', function ($id) {
    $artisan = User::where('role', 'artisan')->findOrFail($id);
    return view('catalogue.artisan', ['artisan' => $artisan]);
})->name('catalogue.artisan');

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::get('/auth/register', fn() => view('auth.register'))->name('auth.register');
Route::get('/auth/login',    fn() => view('auth.login'))->name('auth.login');
Route::get('/login',         fn() => view('auth.login'))->name('login');

/*
|--------------------------------------------------------------------------
| Dashboards
|--------------------------------------------------------------------------
*/
Route::get('/dashboard/acheteur', fn() => view('acheteur.dashboard'))->name('dashboard.acheteur');
Route::get('/dashboard/artisan',  fn() => view('artisan.dashboard'))->name('dashboard.artisan');

/*
|--------------------------------------------------------------------------
| Profil
|--------------------------------------------------------------------------
*/
Route::get('/profil', fn() => view('profil.edit'))->name('me.profil');

/*
|--------------------------------------------------------------------------
| Artisan — gestion des œuvres (vues)
|--------------------------------------------------------------------------
*/
Route::get('/artisan/oeuvres/create',    fn() => view('artisan.oeuvres.create'))->name('artisan.oeuvres.create');
Route::get('/artisan/oeuvres/{id}/edit', fn($id) => view('artisan.oeuvres.edit', ['id' => $id]))->name('artisan.oeuvres.edit');
Route::get('/artisan/{id}',              [ArtisanController::class, 'show'])->name('artisan.show');

/*
|--------------------------------------------------------------------------
| Vérification email
|--------------------------------------------------------------------------
*/
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    $role = $request->user()->role;
    return redirect($role === 'artisan' ? '/dashboard/artisan' : '/dashboard/acheteur');
})->middleware(['auth', 'signed'])->name('verification.verify');
