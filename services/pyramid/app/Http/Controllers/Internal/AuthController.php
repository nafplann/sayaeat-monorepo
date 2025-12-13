<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Validate a Sanctum token
     */
    public function validateToken(Request $request): JsonResponse
    {
        $token = $request->input('token');
        
        if (!$token) {
            return response()->json([
                'valid' => false,
                'message' => 'Token is required'
            ], 400);
        }

        $accessToken = PersonalAccessToken::findToken($token);
        
        if (!$accessToken) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid token'
            ], 401);
        }

        $tokenable = $accessToken->tokenable;

        return response()->json([
            'valid' => true,
            'user_id' => $tokenable->id,
            'user_type' => get_class($tokenable),
            'user' => $tokenable,
            'token_name' => $accessToken->name,
        ]);
    }

    /**
     * Validate session credentials
     */
    public function validateSession(Request $request): JsonResponse
    {
        $sessionId = $request->input('session_id');
        $userId = $request->input('user_id');
        
        if (!$sessionId || !$userId) {
            return response()->json([
                'valid' => false,
                'message' => 'Session ID and User ID are required'
            ], 400);
        }

        // For session-based auth, the BFF should maintain the session
        // This endpoint is for validation only
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'valid' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'user_id' => $user->id,
            'user' => $user,
        ]);
    }

    /**
     * Validate user credentials (for login)
     */
    public function validateCredentials(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        return response()->json([
            'valid' => true,
            'user' => $user,
        ]);
    }

    /**
     * Get user by ID
     */
    public function getUser(Request $request, string $id): JsonResponse
    {
        $userType = $request->input('user_type', 'user');

        $user = match ($userType) {
            'customer' => Customer::find($id),
            'driver' => Driver::find($id),
            default => User::find($id),
        };
        
        if (!$user) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        return response()->json([
            'user' => $user
        ]);
    }
}

