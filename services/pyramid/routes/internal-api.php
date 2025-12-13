<?php

use App\Http\Controllers\Internal\AuditLogsController;
use App\Http\Controllers\Internal\AuthController;
use App\Http\Controllers\Internal\CouponsController;
use App\Http\Controllers\Internal\CustomersController;
use App\Http\Controllers\Internal\DriversController;
use App\Http\Controllers\Internal\MenuAddonCategoriesController;
use App\Http\Controllers\Internal\MenuCategoriesController;
use App\Http\Controllers\Internal\MerchantsController;
use App\Http\Controllers\Internal\MenusController;
use App\Http\Controllers\Internal\OrdersController;
use App\Http\Controllers\Internal\ProductCategoriesController;
use App\Http\Controllers\Internal\ProductDiscountsController;
use App\Http\Controllers\Internal\ProductsController;
use App\Http\Controllers\Internal\PromotionsController;
use App\Http\Controllers\Internal\RolesController;
use App\Http\Controllers\Internal\SettingsController;
use App\Http\Controllers\Internal\StoresController;
use App\Http\Controllers\Internal\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Internal API Routes
|--------------------------------------------------------------------------
|
| These routes are for BFF services to access Pyramid's data layer.
| All routes are protected by API key authentication.
|
*/

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
    Route::get('merchants/{id}/menu-categories', [MerchantsController::class, 'menuCategories']);
    Route::post('merchants/{id}/toggle-status', [MerchantsController::class, 'toggleStatus']);

    // Menus
    Route::apiResource('menus', MenusController::class);
    Route::get('menus/by-merchant/{merchantId}', [MenusController::class, 'getByMerchant']);
    Route::post('menus/{id}/toggle-status', [MenusController::class, 'toggleStatus']);

    // Menu Categories
    Route::apiResource('menu-categories', MenuCategoriesController::class);
    Route::get('menu-categories/by-merchant/{merchantId}', [MenuCategoriesController::class, 'getByMerchant']);

    // Menu Addon Categories
    Route::apiResource('menu-addon-categories', MenuAddonCategoriesController::class);
    Route::delete('menu-addon-categories/addon-delete/{id}', [MenuAddonCategoriesController::class, 'addonDelete']);

    // Orders
    Route::apiResource('orders', OrdersController::class);
    Route::post('orders/{id}/process', [OrdersController::class, 'process']);
    Route::post('orders/{id}/cancel', [OrdersController::class, 'cancel']);
    Route::post('orders/{id}/reject', [OrdersController::class, 'reject']);

    // Stores
    Route::apiResource('stores', StoresController::class);
    Route::get('stores/{id}/products', [StoresController::class, 'products']);
    Route::post('stores/{id}/toggle-status', [StoresController::class, 'toggleStatus']);

    // Products
    Route::apiResource('products', ProductsController::class);
    Route::get('products/by-store/{storeId}', [ProductsController::class, 'getByStore']);
    Route::post('products/{id}/toggle-status', [ProductsController::class, 'toggleStatus']);

    // Product Categories
    Route::apiResource('product-categories', ProductCategoriesController::class);
    Route::get('product-categories/by-merchant/{merchantId}', [ProductCategoriesController::class, 'getByMerchant']);

    // Product Discounts
    Route::apiResource('product-discounts', ProductDiscountsController::class);

    // Customers
    Route::apiResource('customers', CustomersController::class);
    Route::get('customers/{id}/addresses', [CustomersController::class, 'addresses']);
    Route::get('customers/{id}/orders', [CustomersController::class, 'orders']);

    // Coupons
    Route::apiResource('coupons', CouponsController::class);

    // Promotions
    Route::apiResource('promotions', PromotionsController::class);

    // Drivers
    Route::apiResource('drivers', DriversController::class);

    // Roles & Permissions
    Route::apiResource('roles', RolesController::class);
    Route::get('roles/permissions', [RolesController::class, 'permissions']);

    // Users
    Route::apiResource('users', UsersController::class);

    // Audit Logs
    Route::get('audit-logs', [AuditLogsController::class, 'index']);
    Route::get('audit-logs/{id}', [AuditLogsController::class, 'show']);

    // Settings
    Route::get('settings', [SettingsController::class, 'index']);
    Route::post('settings', [SettingsController::class, 'update']);
    Route::get('settings/{key}', [SettingsController::class, 'get']);

    // Driver Reports (placeholder endpoints)
    Route::prefix('driver-reports')->group(function () {
        Route::get('income', function () {
            return response()->json(['income' => 0]); // Placeholder
        });
        Route::get('rank', function () {
            return response()->json(['ranks' => []]); // Placeholder
        });
    });
});

