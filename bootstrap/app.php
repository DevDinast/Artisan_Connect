<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\CheckRole::class,
            \App\Http\Middleware\VerifiedEmail::class,
            \App\Http\Middleware\ValidatedArtisan::class,
        ]);
        
        $middleware->api(append: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        
        // Alias pour les middlewares personnalisés
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'verified' => \App\Http\Middleware\VerifiedEmail::class,
            'validated.artisan' => \App\Http\Middleware\ValidatedArtisan::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
