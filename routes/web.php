<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('home'))->name('home');

Route::get('/auth/register', fn() => view('auth.register'))->name('auth.register');
Route::get('/auth/login', fn() => view('auth.login'))->name('auth.login');
Route::get('/login', fn() => view('auth.login'))->name('login');

Route::get('/dashboard/acheteur', fn() => view('acheteur.dashboard'))->name('dashboard.acheteur');
Route::get('/dashboard/artisan', fn() => view('artisan.dashboard'))->name('dashboard.artisan');
Route::get('/artisan/{id}', [ArtisanController::class, 'show']);
