<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }

        // Vérifier si l'utilisateur a l'un des rôles requis
        foreach ($roles as $role) {
            if ($this->hasRole($user, $role)) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Accès non autorisé',
        ], 403);
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    private function hasRole($user, string $role): bool
    {
        switch ($role) {
            case 'artisan':
                return $user->role === 'artisan';
            case 'acheteur':
                return $user->role === 'acheteur';
            case 'administrateur':
                return $user->role === 'administrateur';
            case 'admin':
                return in_array($user->role, ['administrateur']);
            case 'all':
                return true; // Tous les rôles authentifiés
            default:
                return false;
        }
    }
}
