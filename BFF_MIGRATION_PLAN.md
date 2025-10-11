# BFF Migration Plan for SayaEat Monorepo

## Overview
Migrating the Pyramid monolith service into a BFF (Backend for Frontend) pattern with:
- **Merchant BFF**: Merchant portal functionality
- **Hapi BFF**: User app functionality  
- **Pyramid**: Data service + Driver app (temporary)
- **Shared Package**: Common Models, Enums, Utils

---

## 0. Authentication Strategy

### Current State
- **Merchant Portal (web.php)**: Session-based Laravel Auth
- **Hapi User App (api.php /v1)**: Laravel Sanctum token-based
- **Horus Driver App (api.php /v1/horus)**: Laravel Sanctum token-based

### Migration Strategy

#### Option A: Token-Based Gateway Pattern (RECOMMENDED)
```
┌─────────────┐
│   Client    │
└──────┬──────┘
       │ JWT/Sanctum Token
       ▼
┌─────────────┐
│     BFF     │ ← Validates token with Pyramid
└──────┬──────┘
       │ Internal API Key
       ▼
┌─────────────┐
│   Pyramid   │ ← Authoritative auth service
└─────────────┘
```

**Implementation:**
1. **Pyramid** remains the single source of truth for authentication
2. **Pyramid exposes auth APIs**:
   - `POST /api/internal/auth/validate-token` - Validates Sanctum tokens
   - `POST /api/internal/auth/validate-session` - Validates session tokens
   - `GET /api/internal/users/{id}` - Get user details
3. **BFFs validate tokens** via Pyramid on each request
4. **BFFs use service API keys** to authenticate to Pyramid
5. **Cache validation results** (Redis) with short TTL (5-10 minutes)

**Pros:**
- Single auth source (no sync issues)
- Easy to implement
- Consistent with Laravel patterns
- Smooth migration path

**Cons:**
- Additional latency (mitigated by caching)
- Network dependency

#### Option B: Shared Database (Alternative)
- All services connect to same auth database
- Each BFF validates tokens independently using shared Sanctum config
- **Issues**: Tight coupling, harder to scale, migration complexity

**Decision: Use Option A (Token-Based Gateway)**

---

## 1. Architecture Diagram

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│ Merchant Web │     │  Hapi App    │     │  Horus App   │
└──────┬───────┘     └──────┬───────┘     └──────┬───────┘
       │                    │                     │
       │ Session            │ Sanctum             │ Sanctum
       ▼                    ▼                     ▼
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│ Merchant BFF │     │  Hapi BFF    │     │   Pyramid    │
│              │     │              │     │ (Data Layer  │
│ - Routes     │     │ - Routes     │     │  + Driver)   │
│ - Auth       │     │ - Auth       │     │              │
│ - Business   │     │ - Business   │     │ - Models     │
│   Logic      │     │   Logic      │     │ - Database   │
│              │     │              │     │ - REST APIs  │
└──────┬───────┘     └──────┬───────┘     └──────┬───────┘
       │                    │                     │
       └────────────────────┴─────────────────────┘
                            │
                            ▼
                   ┌────────────────┐
                   │ Shared Package │
                   │                │
                   │ - DTOs         │
                   │ - Enums        │
                   │ - Utils        │
                   │ - Contracts    │
                   └────────────────┘
```

---

## 2. Phase 1: Foundation Setup

### 2.1 Create Shared Package

**Location:** `/packages/sayaeat-shared`

**Structure:**
```
packages/sayaeat-shared/
├── composer.json
├── src/
│   ├── DTOs/
│   │   ├── CustomerDTO.php
│   │   ├── MerchantDTO.php
│   │   ├── OrderDTO.php
│   │   └── ...
│   ├── Enums/
│   │   ├── DayNameEnum.php
│   │   ├── OrderPaymentStatus.php
│   │   ├── ServiceEnum.php
│   │   └── ... (all from pyramid/app/Enums)
│   ├── Utils/
│   │   ├── DistanceCalculator.php
│   │   ├── FeeCalculator.php
│   │   └── ... (shared utilities)
│   ├── Contracts/
│   │   ├── PyramidClientInterface.php
│   │   └── AuthServiceInterface.php
│   └── Clients/
│       ├── PyramidClient.php
│       └── AuthClient.php
└── tests/
```

**Contents to Move from Pyramid:**
- ✅ All Enums (`app/Enums/*`)
- ✅ Shared Utils (`app/Utils/*`)
- ❌ Models (stay in Pyramid - accessed via API)
- ✅ DTOs (create new - for API responses)

**Namespace:** `SayaEat\Shared`

### 2.2 Configure Monorepo

Update `/composer.json`:
```json
{
  "require-dev": {
    "symplify/monorepo-builder": "^12.2"
  },
  "repositories": [
    {
      "type": "path",
      "url": "packages/sayaeat-shared"
    }
  ]
}
```

---

## 3. Phase 2: Transform Pyramid to Data Service

### 3.1 Create Internal API Routes

**File:** `pyramid/routes/internal-api.php`

```php
<?php
// Internal APIs for BFF consumption
// Protected by API key middleware

Route::group(['prefix' => 'internal', 'middleware' => ['api-key']], function () {
    
    // Auth
    Route::post('auth/validate-token', [InternalAuthController::class, 'validateToken']);
    Route::post('auth/validate-session', [InternalAuthController::class, 'validateSession']);
    
    // Merchants
    Route::apiResource('merchants', InternalMerchantsController::class);
    Route::get('merchants/{id}/menus', [InternalMerchantsController::class, 'menus']);
    Route::get('merchants/{id}/categories', [InternalMerchantsController::class, 'categories']);
    
    // Orders
    Route::apiResource('orders', InternalOrdersController::class);
    Route::post('orders/{id}/process', [InternalOrdersController::class, 'process']);
    Route::post('orders/{id}/cancel', [InternalOrdersController::class, 'cancel']);
    
    // Stores
    Route::apiResource('stores', InternalStoresController::class);
    Route::get('stores/{id}/products', [InternalStoresController::class, 'products']);
    
    // Products
    Route::apiResource('products', InternalProductsController::class);
    
    // Customers
    Route::apiResource('customers', InternalCustomersController::class);
    Route::get('customers/{id}/addresses', [InternalCustomersController::class, 'addresses']);
    
    // ... (complete CRUD for all resources)
});
```

### 3.2 Create API Key Middleware

**File:** `pyramid/app/Http/Middleware/ApiKeyMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-Api-Key');
        $validKeys = config('services.internal_api_keys', []);
        
        if (!in_array($apiKey, $validKeys)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        return $next($request);
    }
}
```

### 3.3 Keep Driver Routes in Pyramid (Temporary)

- Keep `routes/api.php` sections for `/v1/horus/*`
- Driver functionality remains unchanged
- Will be migrated later after spec review

---

## 4. Phase 3: Migrate Merchant BFF (FIRST)

### 4.1 Why Merchant First?
- ✅ Session-based auth (simpler)
- ✅ Clear bounded context (merchant management)
- ✅ Less real-time complexity than user app
- ✅ Fewer endpoints than Hapi

### 4.2 Merchant BFF Structure

```
services/merchant-bff/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── MerchantsController.php
│   │   │   ├── MenusController.php
│   │   │   ├── OrdersController.php
│   │   │   └── ... (all from pyramid/web.php)
│   │   └── Middleware/
│   │       ├── AuthenticateSession.php
│   │       └── PyramidAuthMiddleware.php
│   ├── Services/
│   │   ├── PyramidService.php (HTTP client)
│   │   ├── AuthService.php
│   │   └── OrderService.php
│   └── Providers/
│       └── PyramidServiceProvider.php
├── config/
│   ├── pyramid.php (Pyramid API config)
│   └── services.php
└── routes/
    └── web.php (from pyramid/web.php)
```

### 4.3 Migration Steps

1. **Install shared package**
   ```bash
   cd services/merchant-bff
   composer require sayaeat/shared:@dev
   ```

2. **Configure Pyramid client**
   ```php
   // config/pyramid.php
   return [
       'base_url' => env('PYRAMID_API_URL', 'http://pyramid:8000'),
       'api_key' => env('PYRAMID_API_KEY'),
       'timeout' => 30,
       'cache_ttl' => 600, // 10 minutes
   ];
   ```

3. **Copy routes from Pyramid**
   - Copy `pyramid/routes/web.php` → `merchant-bff/routes/web.php`
   - Remove test routes

4. **Copy controllers**
   - Copy controllers from `pyramid/app/Http/Controllers/`
   - Modify to use PyramidService instead of Models

5. **Create PyramidService**
   ```php
   class PyramidService {
       public function getMerchants($filters) {
           return $this->client->get('/internal/merchants', $filters);
       }
       
       public function createOrder($data) {
           return $this->client->post('/internal/orders', $data);
       }
   }
   ```

6. **Copy views**
   - Copy `pyramid/resources/views/` → `merchant-bff/resources/views/`

7. **Test authentication flow**
   - Login should validate against Pyramid
   - Session managed in Merchant BFF

### 4.4 Authentication Flow (Merchant BFF)

```
┌────────────┐
│   User     │
└─────┬──────┘
      │ POST /auth/login
      ▼
┌────────────────┐
│  Merchant BFF  │
└─────┬──────────┘
      │ POST /internal/auth/validate-credentials
      ▼
┌────────────────┐
│    Pyramid     │ ← Checks DB, returns user + token
└─────┬──────────┘
      │
      ▼
┌────────────────┐
│  Merchant BFF  │ ← Stores session with user data
└─────┬──────────┘
      │ Success response
      ▼
┌────────────┐
│   User     │
└────────────┘
```

---

## 5. Phase 4: Migrate Hapi BFF (SECOND)

### 5.1 Hapi BFF Structure

```
services/hapi-bff/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── CustomerAuthController.php
│   │   │       ├── OrdersController.php
│   │   │       ├── MerchantsController.php
│   │   │       └── ... (all from pyramid/api.php /v1)
│   │   └── Middleware/
│   │       └── ValidateTokenWithPyramid.php
│   ├── Services/
│   │   ├── PyramidService.php
│   │   └── AuthService.php
│   └── Providers/
│       └── PyramidServiceProvider.php
└── routes/
    └── api.php (from pyramid/api.php /v1 routes)
```

### 5.2 Migration Steps

1. **Install shared package**
   ```bash
   cd services/hapi-bff
   composer require sayaeat/shared:@dev
   ```

2. **Copy API routes**
   - Copy `/v1` routes from `pyramid/routes/api.php`
   - Exclude `/v1/horus` routes

3. **Copy controllers**
   - Copy `pyramid/app/Http/Controllers/Api/Hapi/`
   - Modify to use PyramidService

4. **Implement token validation**
   ```php
   // Middleware: ValidateTokenWithPyramid
   class ValidateTokenWithPyramid {
       public function handle($request, $next) {
           $token = $request->bearerToken();
           
           // Check cache first
           $userId = Cache::get("token:{$token}");
           
           if (!$userId) {
               // Validate with Pyramid
               $response = $this->pyramidService->validateToken($token);
               $userId = $response['user_id'];
               Cache::put("token:{$token}", $userId, 600); // 10 min
           }
           
           $request->merge(['user_id' => $userId]);
           return $next($request);
       }
   }
   ```

### 5.3 Authentication Flow (Hapi BFF)

```
┌────────────┐
│ Mobile App │
└─────┬──────┘
      │ GET /api/v1/orders (Bearer: token123)
      ▼
┌────────────────┐
│   Hapi BFF     │
└─────┬──────────┘
      │ Check cache for token123
      │ If miss: POST /internal/auth/validate-token
      ▼
┌────────────────┐
│    Pyramid     │ ← Validates token, returns user
└─────┬──────────┘
      │
      ▼
┌────────────────┐
│   Hapi BFF     │ ← Caches validation, proceeds
└─────┬──────────┘
      │ GET /internal/orders?user_id=X
      ▼
┌────────────────┐
│    Pyramid     │ ← Returns orders
└─────┬──────────┘
      │
      ▼
┌────────────────┐
│   Hapi BFF     │ ← Formats response
└─────┬──────────┘
      │ JSON response
      ▼
┌────────────┐
│ Mobile App │
└────────────┘
```

---

## 6. Phase 5: Cleanup & Optimization

### 6.1 Remove Migrated Code from Pyramid

1. **Keep in Pyramid:**
   - ✅ Models
   - ✅ Database migrations
   - ✅ Internal API controllers
   - ✅ Driver routes (`/v1/horus`)
   - ✅ Observers, Policies
   - ✅ Jobs, Queues

2. **Remove from Pyramid:**
   - ❌ `routes/web.php` (except test routes)
   - ❌ `/v1` routes in `routes/api.php`
   - ❌ Controllers for web & hapi (keep internal + horus)
   - ❌ Views (moved to Merchant BFF)
   - ❌ Frontend assets (moved to Merchant BFF)

### 6.2 Performance Optimization

1. **Caching Strategy:**
   - Redis for token validation cache
   - Cache frequently accessed data (merchants, menus)
   - Invalidation webhooks from Pyramid

2. **Connection Pooling:**
   - HTTP/2 persistent connections between BFFs and Pyramid
   - Configure Guzzle connection pool

3. **Rate Limiting:**
   - Per-BFF rate limits in Pyramid
   - Per-user rate limits in BFFs

### 6.3 Monitoring & Observability

1. **Add tracing:**
   - Request IDs across services
   - OpenTelemetry/Jaeger

2. **Metrics:**
   - BFF → Pyramid latency
   - Cache hit rates
   - Auth validation performance

3. **Logging:**
   - Structured logging
   - Correlation IDs

---

## 7. Migration Checklist

### Phase 1: Foundation ✓
- [x] Create `/packages/sayaeat-shared` package
- [x] Copy Enums to shared package
- [x] Copy Utils to shared package
- [x] Create DTOs for API responses
- [x] Create PyramidClient base class
- [x] Configure monorepo composer.json
- [x] Test shared package installation

### Phase 2: Pyramid Data Service ✓
- [x] Create `routes/internal-api.php`
- [x] Create API key middleware
- [x] Create Internal Auth controller
- [x] Create Internal Merchants controller
- [x] Create Internal Orders controller
- [x] Create Internal Stores controller
- [x] Create Internal Products controller
- [x] Create Internal Customers controller
- [x] Add API keys to config
- [ ] Test internal APIs with Postman
- [x] Document internal API endpoints

### Phase 3: Merchant BFF ✓
- [x] Install shared package
- [x] Configure pyramid.php config
- [x] Copy web routes (all routes registered)
- [x] Copy web controllers (all 26 controllers created)
- [x] Create all PyramidServices (18 services total)
- [x] Refactor controllers to use PyramidService (100% complete)
- [x] Create all missing Internal API controllers in Pyramid (13 controllers)
- [x] Update internal-api.php routes (all endpoints registered)
- [x] Create menu/product category controllers
- [x] Create discount, coupon, promotion controllers
- [x] Create service-specific order controllers (Kirim/Makan/Market-Aja)
- [x] Create drivers, roles, users, audit logs controllers
- [x] Setup authentication flow
- [x] Copy views (~88 Blade templates)
- [x] Copy frontend assets (CSS, JS, images, fonts, source files)
- [x] Configure environment (stateless BFF, cookie sessions, API keys)
- [x] Test login/logout (setup complete: automated tests, testing guide, checklist)
- [x] Fix authentication tests (all 9 tests passing)
- [ ] Test merchant CRUD
- [ ] Test menu management
- [ ] Test order management
- [ ] Test store management
- [ ] Deploy to staging
- [ ] QA testing
- [ ] Deploy to production

### Phase 4: Hapi BFF ✓
- [ ] Install shared package
- [ ] Configure pyramid.php config
- [ ] Copy API routes (/v1)
- [ ] Copy Hapi controllers
- [ ] Create PyramidService
- [ ] Refactor controllers to use PyramidService
- [ ] Implement token validation middleware
- [ ] Setup Redis for caching
- [ ] Test authentication flow
- [ ] Test customer endpoints
- [ ] Test order endpoints
- [ ] Test merchant endpoints
- [ ] Test Market-Aja endpoints
- [ ] Test Kirim-Aja endpoints
- [ ] Update mobile app config
- [ ] Deploy to staging
- [ ] Mobile app testing
- [ ] Deploy to production

### Phase 5: Cleanup ✓
- [ ] Remove web routes from Pyramid
- [ ] Remove /v1 routes from Pyramid
- [ ] Remove migrated controllers
- [ ] Remove views from Pyramid
- [ ] Remove frontend assets from Pyramid
- [ ] Update Pyramid README
- [ ] Update architecture documentation
- [ ] Setup monitoring
- [ ] Setup distributed tracing
- [ ] Performance testing
- [ ] Load testing

### Phase 6: Driver BFF (Future) ⏳
- [ ] Wait for spec review
- [ ] Create horus-bff service
- [ ] Follow same pattern as Hapi BFF
- [ ] Migrate /v1/horus routes
- [ ] Remove from Pyramid

---

COMMIT RULES:
Dont add more docs, the repo is now bloated by .md
the commit message should not mention AI assisted.

## 8. Testing Strategy

### Unit Tests
- Test PyramidService methods in isolation
- Mock HTTP responses
- Test DTOs and transformations

### Integration Tests
- Test BFF → Pyramid communication
- Test authentication flows end-to-end
- Test error handling

### End-to-End Tests
- Test full user journeys
- Merchant portal workflows
- Mobile app flows

---

## 9. Rollback Plan

### Gradual Traffic Migration
1. Deploy BFFs alongside Pyramid
2. Use feature flags to route traffic
3. Start with 10% traffic → BFF
4. Monitor errors and latency
5. Gradually increase to 100%

### Rollback Procedure
1. Feature flag to route 100% back to Pyramid
2. No data loss (Pyramid still owns data)
3. Analyze issues
4. Fix and redeploy BFF

---

## 10. Timeline Estimation

| Phase | Duration | Dependencies |
|-------|----------|-------------|
| 1. Foundation Setup | 3-5 days | - |
| 2. Pyramid Data Service | 5-7 days | Phase 1 |
| 3. Merchant BFF | 10-14 days | Phase 2 |
| 4. Hapi BFF | 10-14 days | Phase 3 |
| 5. Cleanup & Optimization | 3-5 days | Phase 4 |
| **Total** | **31-45 days** | |

---

## 11. Key Design Decisions

### ✅ Decisions Made

1. **Authentication:** Token-based gateway pattern (Pyramid as auth authority)
2. **Order:** Merchant BFF first, then Hapi BFF, Driver stays temporarily
3. **Shared Code:** Package approach with Enums, DTOs, Utils
4. **Communication:** REST APIs with API key protection
5. **Caching:** Redis for token validation and data caching
6. **Migration:** Gradual with feature flags

### ⚠️ Open Questions

1. **Database Access:** Should BFFs read from replicas directly for performance?
2. **Event Streaming:** Should we use Kafka/RabbitMQ for async operations?
3. **GraphQL:** Should BFFs expose GraphQL instead of REST?
4. **Service Mesh:** Do we need Istio/Linkerd for inter-service communication?

---

## 12. Success Metrics

- [ ] Zero downtime during migration
- [ ] < 100ms added latency from BFF layer (after caching)
- [ ] 99.9% uptime for BFF services
- [ ] < 5% error rate during migration
- [ ] All existing features work identically
- [ ] Mobile apps require no changes (transparent migration)

---

## Contact & Questions

For questions about this plan, contact the platform team.

**Last Updated:** October 11, 2025

