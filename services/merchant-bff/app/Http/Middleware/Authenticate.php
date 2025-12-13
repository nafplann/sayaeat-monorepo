<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = Session::get('user_id');

        if (!$userId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Unauthenticated'
                ], 401);
            }

            return redirect()->guest(route('auth.login'));
        }

        return $next($request);
    }
}

