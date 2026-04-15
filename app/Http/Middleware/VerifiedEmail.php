<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class VerifiedEmail
{
    public function handle(Request $request, Closure $next)
    {
        // Si la vérification email est désactivée (pas de SMTP en prod), on passe directement
        if (!config('app.email_verification', true)) {
            return $next($request);
        }

        $user = Auth::user();

        if (!$user->email_verified_at) {
            return response()->json([
                'success'               => false,
                'message'               => 'Email non vérifié. Vérifiez votre boîte mail.',
                'email'                 => $user->email,
                'verification_required' => true,
            ], 403);
        }

        return $next($request);
    }
}
