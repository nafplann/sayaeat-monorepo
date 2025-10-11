# BFF Migration Status

## Overview

This document tracks the progress of migrating the Sayaeat monolithic application (pyramid) to a BFF (Backend for Frontend) architecture.

## Architecture

```
┌─────────────────┐
│  Merchant BFF   │ (Merchant Portal Web App)
│  (Port: 8001)   │
└────────┬────────┘
         │
         │ HTTP API Calls
         │
┌────────▼────────┐
│    Pyramid      │ (Data Service API)
│  (Port: 8000)   │
└────────┬────────┘
         │
         │ Database Operations
         │
┌────────▼────────┐
│    Database     │
└─────────────────┘

┌─────────────────┐
│    Hapi BFF     │ (User Mobile App)
│  (Port: 8002)   │
└────────┬────────┘
         │
         │ HTTP API Calls
         │
┌────────▼────────┐
│    Pyramid      │ (Data Service API)
│  (Port: 8000)   │
└─────────────────┘
```

## Completed Phases

### ✅ Phase 1: Create Shared Library Package
- Created `services/sayaeat-shared` package
- Migrated 34 Models from `app/Models/`
- Migrated all Enums from `app/Enums/`
- Migrated all Utils from `app/Utils/`
- Migrated all Traits from `app/Traits/`
- Updated namespaces from `App\` to `Sayaeat\Shared\`
- Configured as Composer path repository in pyramid, merchant-bff, and hapi-bff
- **Status:** Committed ✅

### ✅ Phase 2: Convert Pyramid to Data Service
- Created base `BaseDataController` for common CRUD operations
- Created 16 Data API controllers:
  - MerchantsDataController
  - MenusDataController
  - MenuCategoriesDataController
  - OrdersDataController
  - StoresDataController
  - StoreOrdersDataController
  - ProductsDataController
  - ProductCategoriesDataController
  - CustomersDataController
  - CustomerAddressesDataController
  - DriversDataController
  - UsersDataController
  - CouponsDataController
  - PromotionsDataController
  - ShipmentOrdersDataController
- Added RESTful routes under `/data/v1` prefix
- **Status:** Committed ✅

### ✅ Phase 3: Setup BFFs with PyramidDataService
- Created `PyramidDataService` class in both merchant-bff and hapi-bff
- Implemented HTTP client wrapper methods for all entities:
  - Merchants
  - Menus
  - Menu Categories
  - Orders
  - Stores
  - Store Orders
  - Products
  - Product Categories
  - Customers
  - Customer Addresses
  - Drivers
  - Users
  - Coupons
  - Promotions
  - Shipment Orders
- Added Pyramid service configuration to `config/services.php`
- **Status:** Committed ✅

### ✅ Phase 3.2: Migrate Controllers, Routes, and Views
- Migrated 26+ controllers from pyramid to merchant-bff:
  - AuditLogsController
  - AuthController
  - CouponsController
  - CustomersController
  - DashboardController
  - DriverDailyReportController
  - DriversController
  - KirimAjaOrdersController
  - MakanAjaOrdersController
  - MarketAjaOrdersController
  - MenuAddonCategoriesController
  - MenuCategoriesController
  - MenusController
  - MerchantsController
  - OngoingOrdersController
  - OrdersController
  - ProductCategoriesController
  - ProductDiscountsController
  - ProductsController
  - PromotionsController
  - RolesController
  - SettingsController
  - ShoppingOrdersController
  - StoreOrdersController
  - StoresController
  - UsersController
- Migrated all routes from `routes/web.php`
- Migrated all views from `resources/views/`
- Migrated assets from `resources/assets/`
- Migrated supporting classes:
  - Imports
  - Observers
  - Policies
  - Notifications
  - Core
  - helpers.php
- Migrated config files:
  - audit.php
  - permission.php
  - menu.php
  - product.php
  - cors.php
  - excel.php
- **Status:** Committed ✅

## Pending Phases

### 🔄 Phase 4: Refactor Merchant-BFF Controllers
**Status:** PENDING - Requires manual refactoring

All controllers in merchant-bff need to be refactored to use `PyramidDataService` instead of direct Eloquent model access.

#### Refactoring Pattern

**Before (Direct DB Access):**
```php
use App\Models\Merchant;

class MerchantsController extends Controller
{
    public function index()
    {
        $merchants = Merchant::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('merchants.browse', compact('merchants'));
    }
}
```

**After (API Call via PyramidDataService):**
```php
use App\Services\PyramidDataService;

class MerchantsController extends Controller
{
    protected PyramidDataService $pyramidService;
    
    public function __construct(PyramidDataService $pyramidService)
    {
        $this->pyramidService = $pyramidService;
    }
    
    public function index()
    {
        $merchants = $this->pyramidService->getMerchants([
            'status' => 'active',
            'sort' => 'created_at',
            'order' => 'desc'
        ]);
        
        return view('merchants.browse', compact('merchants'));
    }
}
```

#### Controllers Requiring Refactoring
- [ ] MerchantsController
- [ ] MenusController
- [ ] MenuCategoriesController
- [ ] MenuAddonCategoriesController
- [ ] OrdersController
- [ ] StoresController
- [ ] StoreOrdersController
- [ ] ProductsController
- [ ] ProductCategoriesController
- [ ] ProductDiscountsController
- [ ] CustomersController
- [ ] DriversController
- [ ] UsersController
- [ ] CouponsController
- [ ] PromotionsController
- [ ] KirimAjaOrdersController
- [ ] MakanAjaOrdersController
- [ ] MarketAjaOrdersController
- [ ] ShoppingOrdersController
- [ ] OngoingOrdersController
- [ ] DashboardController
- [ ] DriverDailyReportController
- [ ] AuditLogsController
- [ ] RolesController
- [ ] SettingsController
- [ ] AuthController

### 🔄 Phase 5: Migrate Hapi-BFF
**Status:** PENDING

1. Migrate API controllers from `pyramid/app/Http/Controllers/Api/Hapi/` to `hapi-bff/app/Http/Controllers/`
2. Migrate routes from `pyramid/routes/api.php` (/v1 routes) to `hapi-bff/routes/api.php`
3. Refactor controllers to use PyramidDataService

#### Controllers to Migrate
- [ ] CouponsApiController
- [ ] CustomerAddressesApiController
- [ ] CustomerAuthApiController
- [ ] KirimAja/ShippingOrdersApiController
- [ ] MarketAja/ProductsApiController
- [ ] MarketAja/StoreOrdersApiController
- [ ] MarketAja/StoresApiController
- [ ] MenusApiController
- [ ] MerchantsApiController
- [ ] OrdersApiController
- [ ] PaymentMethodsApiController
- [ ] ProfileApiController
- [ ] PromotionsApiController

### 🔄 Phase 6: Testing
**Status:** PENDING

1. Test merchant portal functionality end-to-end
2. Test user app API endpoints end-to-end
3. Ensure all database operations work through Pyramid data service

### 🔄 Phase 7: Cleanup and Documentation
**Status:** PENDING

1. Remove migrated controllers from pyramid
2. Remove migrated routes from pyramid (keep /data/v1 and /v1/horus)
3. Update pyramid README to document it as data service
4. Update each BFF README with architecture docs
5. Update docker-compose configuration for multi-service setup
6. Configure service discovery/networking
7. Add environment variable documentation

## Environment Configuration

### Pyramid (Data Service)
```env
APP_NAME="Pyramid Data Service"
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sayaeat
DB_USERNAME=root
DB_PASSWORD=secret
```

### Merchant-BFF
```env
APP_NAME="Merchant BFF"
APP_URL=http://localhost:8001
PYRAMID_API_URL=http://localhost:8000/api
```

### Hapi-BFF
```env
APP_NAME="Hapi BFF"
APP_URL=http://localhost:8002
PYRAMID_API_URL=http://localhost:8000/api
```

## Important Notes

- **Horus routes** (`/v1/horus`) remain in pyramid temporarily
- **Database**: Pyramid maintains the single source of truth
- **Authentication**: Each BFF handles its own auth but may need to verify tokens with pyramid
- **Notifications/Jobs**: Currently in pyramid, may move later if needed
- **Observers/Listeners**: Remain in pyramid (data layer)
- **Shared Package**: Install with `composer update` in each service

## Running the Services

### Install Shared Package
```bash
cd services/pyramid
composer update

cd ../merchant-bff
composer update

cd ../hapi-bff
composer update
```

### Start Services
```bash
# Terminal 1 - Pyramid Data Service
cd services/pyramid
php artisan serve --port=8000

# Terminal 2 - Merchant BFF
cd services/merchant-bff
php artisan serve --port=8001

# Terminal 3 - Hapi BFF
cd services/hapi-bff
php artisan serve --port=8002
```

## Next Steps

1. **Complete Phase 4**: Refactor all merchant-bff controllers to use PyramidDataService
2. **Complete Phase 5**: Migrate and refactor hapi-bff controllers
3. **Testing**: Thoroughly test all functionality
4. **Documentation**: Update README files for each service
5. **Docker Setup**: Configure docker-compose for all services

