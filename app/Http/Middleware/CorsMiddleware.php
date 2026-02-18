<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Origines autorisées
        $allowedOrigins = [
            'https://artisanconnect.ci',
            'https://www.artisanconnect.ci',
            'https://app.artisanconnect.ci',
            'https://admin.artisanconnect.ci',
            'http://localhost:3000',
            'http://localhost:8080',
        ];

        $origin = $request->header('Origin');
        
        if (in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400'); // 24 hours
        }

        // Gérer les requêtes OPTIONS (pre-flight)
        if ($request->isMethod('OPTIONS')) {
            return response('', 200);
        }

        return $next($request);
    }
}
