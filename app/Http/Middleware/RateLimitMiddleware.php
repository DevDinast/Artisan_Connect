<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 100, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);
        
        // Vérifier le rate limiting
        if ($this->hasTooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts);
        }
        
        // Incrémenter le compteur
        $this->hit($key, $decayMinutes);
        
        // Ajouter les headers de rate limiting
        $response = $next($request);
        
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - $this->getAttempts($key)));
        $response->headers->set('X-RateLimit-Reset', $this->availableIn($key));
        
        return $response;
    }
    
    /**
     * Résoudre la signature de la requête pour le rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $identifier = $request->ip();
        
        // Si l'utilisateur est authentifié, utiliser son ID
        if ($request->user()) {
            $identifier = 'user:' . $request->user()->id;
        }
        
        // Ajouter le chemin de la route pour un rate limiting plus précis
        $route = $request->route();
        if ($route) {
            $identifier .= ':' . $route->getName();
        }
        
        return 'rate_limit:' . sha1($identifier);
    }
    
    /**
     * Vérifier si trop de tentatives
     */
    protected function hasTooManyAttempts(string $key, int $maxAttempts): bool
    {
        return $this->getAttempts($key) >= $maxAttempts;
    }
    
    /**
     * Incrémenter le compteur de tentatives
     */
    protected function hit(string $key, int $decayMinutes): void
    {
        Redis::add($key . ':timer', $decayMinutes * 60);
        Redis::incr($key);
        Redis::expire($key, $decayMinutes * 60);
    }
    
    /**
     * Obtenir le nombre de tentatives
     */
    protected function getAttempts(string $key): int
    {
        return (int) Redis::get($key) ?? 0;
    }
    
    /**
     * Obtenir le temps avant réinitialisation
     */
    protected function availableIn(string $key): int
    {
        return Redis::ttl($key . ':timer');
    }
    
    /**
     * Construire la réponse de rate limit exceeded
     */
    protected function buildResponse(string $key, int $maxAttempts)
    {
        $seconds = $this->availableIn($key);
        
        return Response::json([
            'success' => false,
            'message' => 'Too many attempts. Please try again later.',
            'errors' => [
                'rate_limit' => [
                    'Too many attempts. Please try again in ' . $seconds . ' seconds.'
                ]
            ],
            'retry_after' => $seconds,
        ], 429)->header('Retry-After', $seconds)
        ->header('X-RateLimit-Limit', $maxAttempts)
        ->header('X-RateLimit-Remaining', 0)
        ->header('X-RateLimit-Reset', $seconds);
    }
}
