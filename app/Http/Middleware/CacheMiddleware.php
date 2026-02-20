<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class CacheMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $duration = 300)
    {
        // Générer une clé de cache unique
        $cacheKey = $this->generateCacheKey($request);
        
        // Vérifier si la réponse est déjà en cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // Exécuter la requête
        $response = $next($request);
        
        // Mettre en cache uniquement les réponses réussies
        if ($response->getStatusCode() === 200) {
            Cache::put($cacheKey, $response, $duration);
        }
        
        return $response;
    }
    
    /**
     * Générer une clé de cache unique basée sur la requête
     */
    private function generateCacheKey(Request $request): string
    {
        $key = 'api_cache_' . md5(
            $request->fullUrl() . 
            $request->method() . 
            ($request->user()?->id ?? 'guest')
        );
        
        return $key;
    }
}
