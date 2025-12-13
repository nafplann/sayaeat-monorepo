<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Api-Key');
        
        if (!$apiKey) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'API key is required'
            ], 401);
        }

        $validKeys = config('services.internal_api_keys', []);

        if (!in_array($apiKey, $validKeys, true)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid API key'
            ], 401);
        }

        return $next($request);
    }
}

