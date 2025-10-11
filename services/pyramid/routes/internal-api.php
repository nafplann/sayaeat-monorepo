<?php

use App\Http\Controllers\Internal\AuthController;
use App\Http\Controllers\Internal\CustomersController;
use App\Http\Controllers\Internal\MerchantsController;
use App\Http\Controllers\Internal\MenusController;
use App\Http\Controllers\Internal\OrdersController;
use App\Http\Controllers\Internal\ProductsController;
use App\Http\Controllers\Internal\StoresController;
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

    // Customers
    Route::apiResource('customers', CustomersController::class);
    Route::get('customers/{id}/addresses', [CustomersController::class, 'addresses']);
    Route::get('customers/{id}/orders', [CustomersController::class, 'orders']);
});

