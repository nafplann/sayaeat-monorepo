# BFF Migration - Quick Start Guide

This is a practical, step-by-step guide to start the BFF migration immediately.

## Prerequisites

- PHP 8.2+
- Composer
- Redis (for caching)
- Docker (optional, for local development)

---

## Step 1: Create Shared Package (Day 1-2)

### 1.1 Create Package Structure

```bash
cd /Users/abdul.manaf/Documents/appdev/sayaeat-monorepo
mkdir -p packages/sayaeat-shared/src/{DTOs,Enums,Utils,Contracts,Clients}
mkdir -p packages/sayaeat-shared/tests
```

### 1.2 Create composer.json

**File:** `packages/sayaeat-shared/composer.json`

```json
{
    "name": "sayaeat/shared",
    "description": "Shared package for SayaEat services",
    "type": "library",
    "require": {
        "php": "^8.2",
        "illuminate/support": "^11.0"
    },
    "autoload": {
        "psr-4": {
            "SayaEat\\Shared\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "SayaEat\\Shared\\Tests\\": "tests/"
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

### 1.3 Copy Enums

```bash
# Copy all enums from Pyramid
cp -r services/pyramid/app/Enums/* packages/sayaeat-shared/src/Enums/

# Update namespace in all copied files
# Change: namespace App\Enums;
# To: namespace SayaEat\Shared\Enums;
```

### 1.4 Copy Utils

```bash
# Copy utilities that don't depend on Models
cp services/pyramid/app/Utils/DistanceCalculator.php packages/sayaeat-shared/src/Utils/
cp services/pyramid/app/Utils/FeeCalculator.php packages/sayaeat-shared/src/Utils/
# ... copy other utils as needed

# Update namespaces
# Change: namespace App\Utils;
# To: namespace SayaEat\Shared\Utils;
```

### 1.5 Create Base DTO

**File:** `packages/sayaeat-shared/src/DTOs/BaseDTO.php`

```php
<?php

namespace SayaEat\Shared\DTOs;

use JsonSerializable;

abstract class BaseDTO implements JsonSerializable
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }
}
```

### 1.6 Create Pyramid Client Interface

**File:** `packages/sayaeat-shared/src/Contracts/PyramidClientInterface.php`

```php
<?php

namespace SayaEat\Shared\Contracts;

interface PyramidClientInterface
{
    public function get(string $endpoint, array $params = []): array;
    public function post(string $endpoint, array $data = []): array;
    public function put(string $endpoint, array $data = []): array;
    public function delete(string $endpoint): array;
}
```

### 1.7 Create Pyramid Client

**File:** `packages/sayaeat-shared/src/Clients/PyramidClient.php`

```php
<?php

namespace SayaEat\Shared\Clients;

use SayaEat\Shared\Contracts\PyramidClientInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PyramidClient implements PyramidClientInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;
    protected int $cacheTtl;

    public function __construct(
        string $baseUrl,
        string $apiKey,
        int $timeout = 30,
        int $cacheTtl = 600
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
        $this->cacheTtl = $cacheTtl;
    }

    public function get(string $endpoint, array $params = []): array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->timeout($this->timeout)
            ->get($this->buildUrl($endpoint), $params);

        return $this->handleResponse($response);
    }

    public function post(string $endpoint, array $data = []): array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->timeout($this->timeout)
            ->post($this->buildUrl($endpoint), $data);

        return $this->handleResponse($response);
    }

    public function put(string $endpoint, array $data = []): array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->timeout($this->timeout)
            ->put($this->buildUrl($endpoint), $data);

        return $this->handleResponse($response);
    }

    public function delete(string $endpoint): array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->timeout($this->timeout)
            ->delete($this->buildUrl($endpoint));

        return $this->handleResponse($response);
    }

    protected function getHeaders(): array
    {
        return [
            'X-Api-Key' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function buildUrl(string $endpoint): string
    {
        return $this->baseUrl . '/' . ltrim($endpoint, '/');
    }

    protected function handleResponse($response): array
    {
        if ($response->failed()) {
            throw new \Exception(
                "Pyramid API error: {$response->status()} - {$response->body()}"
            );
        }

        return $response->json();
    }
}
```

### 1.8 Update Root composer.json

Add to `/composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/sayaeat-shared"
        }
    ]
}
```

---

## Step 2: Transform Pyramid to Data Service (Day 3-7)

### 2.1 Create API Key Middleware

**File:** `services/pyramid/app/Http/Middleware/ApiKeyMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Api-Key');
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
```

### 2.2 Register Middleware

**File:** `services/pyramid/bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'api-key' => \App\Http\Middleware\ApiKeyMiddleware::class,
    ]);
})
```

### 2.3 Create Internal API Routes

**File:** `services/pyramid/routes/internal-api.php` (NEW FILE)

```php
<?php

use App\Http\Controllers\Internal\AuthController;
use App\Http\Controllers\Internal\MerchantsController;
use App\Http\Controllers\Internal\OrdersController;
use App\Http\Controllers\Internal\CustomersController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'internal', 'middleware' => ['api-key']], function () {
    
    // Authentication
    Route::group(['prefix' => 'auth', 'controller' => AuthController::class], function () {
        Route::post('validate-token', 'validateToken');
        Route::post('validate-session', 'validateSession');
        Route::post('validate-credentials', 'validateCredentials');
        Route::get('user/{id}', 'getUser');
    });

    // Merchants
    Route::apiResource('merchants', MerchantsController::class);
    Route::get('merchants/{id}/menus', [MerchantsController::class, 'menus']);
    Route::get('merchants/{id}/categories', [MerchantsController::class, 'categories']);
    Route::post('merchants/{id}/toggle-status', [MerchantsController::class, 'toggleStatus']);

    // Orders
    Route::apiResource('orders', OrdersController::class);
    Route::post('orders/{id}/process', [OrdersController::class, 'process']);
    Route::post('orders/{id}/cancel', [OrdersController::class, 'cancel']);
    Route::post('orders/{id}/reject', [OrdersController::class, 'reject']);

    // Customers
    Route::apiResource('customers', CustomersController::class);
    Route::get('customers/{id}/addresses', [CustomersController::class, 'addresses']);
    Route::get('customers/{id}/orders', [CustomersController::class, 'orders']);

    // ... Add more as needed
});
```

### 2.4 Register Routes

**File:** `services/pyramid/bootstrap/app.php`

Add to the web configuration:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    then: function () {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/internal-api.php'));
    }
)
```

### 2.5 Create Internal Auth Controller

**File:** `services/pyramid/app/Http/Controllers/Internal/AuthController.php`

```php
<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function validateToken(Request $request)
    {
        $token = $request->input('token');
        
        $accessToken = PersonalAccessToken::findToken($token);
        
        if (!$accessToken) {
            return response()->json(['valid' => false], 401);
        }

        $tokenable = $accessToken->tokenable;

        return response()->json([
            'valid' => true,
            'user_id' => $tokenable->id,
            'user_type' => get_class($tokenable),
            'user' => $tokenable,
        ]);
    }

    public function validateSession(Request $request)
    {
        $sessionId = $request->input('session_id');
        
        // Validate session from session store
        // This depends on your session driver
        
        return response()->json([
            'valid' => true,
            'user_id' => $userId ?? null,
        ]);
    }

    public function validateCredentials(Request $request)
    {
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

    public function getUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        return response()->json(['user' => $user]);
    }
}
```

### 2.6 Create Internal Merchants Controller

**File:** `services/pyramid/app/Http/Controllers/Internal/MerchantsController.php`

```php
<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Http\Request;

class MerchantsController extends Controller
{
    public function index(Request $request)
    {
        $query = Merchant::query();

        // Apply filters from request
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $merchants = $query->paginate($perPage);

        return response()->json($merchants);
    }

    public function store(Request $request)
    {
        $merchant = Merchant::create($request->all());
        
        return response()->json($merchant, 201);
    }

    public function show($id)
    {
        $merchant = Merchant::with(['menus', 'categories'])->findOrFail($id);
        
        return response()->json($merchant);
    }

    public function update(Request $request, $id)
    {
        $merchant = Merchant::findOrFail($id);
        $merchant->update($request->all());
        
        return response()->json($merchant);
    }

    public function destroy($id)
    {
        $merchant = Merchant::findOrFail($id);
        $merchant->delete();
        
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function menus($id)
    {
        $merchant = Merchant::findOrFail($id);
        $menus = $merchant->menus;
        
        return response()->json($menus);
    }

    public function categories($id)
    {
        $merchant = Merchant::findOrFail($id);
        $categories = $merchant->categories;
        
        return response()->json($categories);
    }

    public function toggleStatus($id)
    {
        $merchant = Merchant::findOrFail($id);
        // Toggle logic
        
        return response()->json($merchant);
    }
}
```

### 2.7 Add API Keys to Config

**File:** `services/pyramid/config/services.php`

```php
return [
    // ... existing config

    'internal_api_keys' => [
        env('MERCHANT_BFF_API_KEY'),
        env('HAPI_BFF_API_KEY'),
    ],
];
```

**File:** `services/pyramid/.env`

```env
MERCHANT_BFF_API_KEY=merchant-bff-secret-key-change-in-production
HAPI_BFF_API_KEY=hapi-bff-secret-key-change-in-production
```

### 2.8 Test Internal API

```bash
# Test auth validation
curl -X POST http://localhost:8000/api/internal/auth/validate-token \
  -H "X-Api-Key: merchant-bff-secret-key-change-in-production" \
  -H "Content-Type: application/json" \
  -d '{"token": "your-sanctum-token"}'

# Test merchants endpoint
curl -X GET http://localhost:8000/api/internal/merchants \
  -H "X-Api-Key: merchant-bff-secret-key-change-in-production"
```

---

## Step 3: Setup Merchant BFF (Day 8-14)

### 3.1 Install Shared Package

```bash
cd services/merchant-bff

# Add to composer.json
composer config repositories.sayaeat-shared path ../../packages/sayaeat-shared
composer require sayaeat/shared:@dev
```

### 3.2 Create Pyramid Config

**File:** `services/merchant-bff/config/pyramid.php`

```php
<?php

return [
    'base_url' => env('PYRAMID_API_URL', 'http://localhost:8000/api'),
    'api_key' => env('PYRAMID_API_KEY'),
    'timeout' => env('PYRAMID_TIMEOUT', 30),
    'cache_ttl' => env('PYRAMID_CACHE_TTL', 600),
    'retry' => [
        'times' => 3,
        'sleep' => 100,
    ],
];
```

**File:** `services/merchant-bff/.env`

```env
PYRAMID_API_URL=http://pyramid:8000/api
PYRAMID_API_KEY=merchant-bff-secret-key-change-in-production
```

### 3.3 Register Pyramid Service

**File:** `services/merchant-bff/app/Providers/PyramidServiceProvider.php`

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SayaEat\Shared\Clients\PyramidClient;
use SayaEat\Shared\Contracts\PyramidClientInterface;

class PyramidServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PyramidClientInterface::class, function ($app) {
            return new PyramidClient(
                baseUrl: config('pyramid.base_url'),
                apiKey: config('pyramid.api_key'),
                timeout: config('pyramid.timeout'),
                cacheTtl: config('pyramid.cache_ttl')
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
```

**File:** `services/merchant-bff/bootstrap/app.php`

```php
->withProviders([
    App\Providers\PyramidServiceProvider::class,
])
```

### 3.4 Create Merchant Service

**File:** `services/merchant-bff/app/Services/MerchantService.php`

```php
<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class MerchantService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->pyramidClient->get('internal/merchants', $filters);
    }

    public function getById($id)
    {
        return $this->pyramidClient->get("internal/merchants/{$id}");
    }

    public function create(array $data)
    {
        return $this->pyramidClient->post('internal/merchants', $data);
    }

    public function update($id, array $data)
    {
        return $this->pyramidClient->put("internal/merchants/{$id}", $data);
    }

    public function delete($id)
    {
        return $this->pyramidClient->delete("internal/merchants/{$id}");
    }

    public function getMenus($id)
    {
        return $this->pyramidClient->get("internal/merchants/{$id}/menus");
    }

    public function toggleStatus($id)
    {
        return $this->pyramidClient->post("internal/merchants/{$id}/toggle-status");
    }
}
```

### 3.5 Create Merchant Controller

**File:** `services/merchant-bff/app/Http/Controllers/MerchantsController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Services\MerchantService;
use Illuminate\Http\Request;

class MerchantsController extends Controller
{
    public function __construct(
        protected MerchantService $merchantService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'category', 'per_page']);
        $merchants = $this->merchantService->getAll($filters);

        return view('merchants.index', compact('merchants'));
    }

    public function show($id)
    {
        $merchant = $this->merchantService->getById($id);

        return view('merchants.show', compact('merchant'));
    }

    public function create()
    {
        return view('merchants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string',
            // ... validation rules
        ]);

        $merchant = $this->merchantService->create($validated);

        return redirect()->route('merchants.index')
            ->with('success', 'Merchant created successfully');
    }

    public function edit($id)
    {
        $merchant = $this->merchantService->getById($id);

        return view('merchants.edit', compact('merchant'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            // ... validation rules
        ]);

        $merchant = $this->merchantService->update($id, $validated);

        return redirect()->route('merchants.index')
            ->with('success', 'Merchant updated successfully');
    }

    public function destroy($id)
    {
        $this->merchantService->delete($id);

        return redirect()->route('merchants.index')
            ->with('success', 'Merchant deleted successfully');
    }

    public function toggleStatus($id)
    {
        $merchant = $this->merchantService->toggleStatus($id);

        return response()->json($merchant);
    }
}
```

### 3.6 Copy Routes

Copy relevant routes from `services/pyramid/routes/web.php` to `services/merchant-bff/routes/web.php`.

### 3.7 Copy Views

```bash
# Copy all views from pyramid to merchant-bff
cp -r services/pyramid/resources/views/* services/merchant-bff/resources/views/
```

---

## Step 4: Authentication Setup (Day 14-16)

### 4.1 Create Auth Service in Merchant BFF

**File:** `services/merchant-bff/app/Services/AuthService.php`

```php
<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;
use Illuminate\Support\Facades\Cache;

class AuthService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    public function validateCredentials(string $email, string $password): ?array
    {
        try {
            $response = $this->pyramidClient->post('internal/auth/validate-credentials', [
                'email' => $email,
                'password' => $password,
            ]);

            if ($response['valid']) {
                return $response['user'];
            }
        } catch (\Exception $e) {
            logger()->error('Auth validation failed', [
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    public function getUser($id)
    {
        $cacheKey = "user:{$id}";

        return Cache::remember($cacheKey, 600, function () use ($id) {
            $response = $this->pyramidClient->get("internal/auth/user/{$id}");
            return $response['user'] ?? null;
        });
    }
}
```

### 4.2 Update Auth Controller in Merchant BFF

**File:** `services/merchant-bff/app/Http/Controllers/AuthController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function login()
    {
        return view('auth.login');
    }

    public function loginRequest(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = $this->authService->validateCredentials(
            $request->input('email'),
            $request->input('password')
        );

        if ($user) {
            Session::regenerate();
            Session::put('user_id', $user['id']);
            Session::put('user', $user);

            return response()->json([
                'status' => true,
                'user' => $user,
                'redirectTo' => route('dashboard')
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Incorrect email or password'
        ], 401);
    }

    public function logout()
    {
        Session::forget('user_id');
        Session::forget('user');
        Session::flush();

        return redirect('/');
    }
}
```

---

## Next Steps

1. ✅ Complete Pyramid internal API controllers (Orders, Stores, Products, etc.)
2. ✅ Complete Merchant BFF services and controllers
3. ✅ Test Merchant BFF thoroughly
4. ✅ Deploy Merchant BFF to staging
5. Move to Hapi BFF migration

---

## Useful Commands

```bash
# Install shared package in BFF
composer require sayaeat/shared:@dev

# Update shared package
composer update sayaeat/shared

# Test internal API
php artisan serve --host=0.0.0.0 --port=8000

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## Troubleshooting

### Issue: Shared package not found

```bash
# Make sure repository is configured
composer config repositories.sayaeat-shared path ../../packages/sayaeat-shared

# Update composer
composer update
```

### Issue: API key authentication failing

Check:
1. API key in `.env` matches Pyramid config
2. `X-Api-Key` header is being sent
3. Middleware is registered correctly

### Issue: Pyramid API returning 404

Check:
1. Internal routes are registered
2. Route prefix is correct (`/api/internal`)
3. Pyramid is running

---

**Ready to start? Begin with Step 1!**

