<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;
use Illuminate\Support\Facades\Cache;

class AuthService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    /**
     * Validate user credentials
     */
    public function validateCredentials(string $email, string $password): ?array
    {
        try {
            $response = $this->pyramidClient->post('internal/auth/validate-credentials', [
                'email' => $email,
                'password' => $password,
            ]);

            if ($response['valid'] ?? false) {
                return $response['user'];
            }
        } catch (\Exception $e) {
            logger()->error('Auth validation failed', [
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Get user by ID
     */
    public function getUser(string $id, string $userType = 'user'): ?array
    {
        $cacheKey = "user:{$userType}:{$id}";

        return Cache::remember($cacheKey, config('pyramid.cache_ttl'), function () use ($id, $userType) {
            try {
                $response = $this->pyramidClient->get("internal/auth/user/{$id}", [
                    'user_type' => $userType
                ]);
                
                return $response['user'] ?? null;
            } catch (\Exception $e) {
                logger()->error('Failed to get user', [
                    'user_id' => $id,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });
    }

    /**
     * Invalidate user cache
     */
    public function invalidateUserCache(string $id, string $userType = 'user'): void
    {
        Cache::forget("user:{$userType}:{$id}");
    }
}

