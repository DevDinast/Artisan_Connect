<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifiedEmail
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();


        if (!$user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email non vérifié',
                'email' => $user->email,
                'verification_required' => true
            ], 403);
        }

        return $next($request);
    }
}
