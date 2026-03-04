<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidatedArtisan
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }

        if ($user->role !== 'artisan') {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé aux artisans'
            ], 403);
        }

        // Vérifier si l'artisan a un profil validé
        if (!$user->artisan || !$user->artisan->compte_valide) {
            return response()->json([
                'success' => false,
                'message' => 'Compte artisan non validé',
                'validation_required' => true
            ], 403);
        }

        return $next($request);
    }
}
