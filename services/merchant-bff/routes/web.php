<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('manage/dashboard');
});

Route::group(['middleware' => 'throttle:global'], function () {
    
    // Auth Routes
    Route::group(['prefix' => 'auth', 'controller' => AuthController::class], function () {
        Route::redirect('/', 'auth/login');
        Route::get('login', 'login')->name('auth.login');
        Route::get('logout', 'logout')->name('auth.logout');
        Route::post('login', 'loginRequest')->name('auth.login-request');
    });

    // Backend Routes
    Route::group(['prefix' => 'manage', 'middleware' => ['auth']], function () {
        Route::get('/', function () {
            return redirect('manage/dashboard')->name('home');
        });

        // Dashboard
        Route::group(['prefix' => 'dashboard', 'controller' => App\Http\Controllers\DashboardController::class], function () {
            Route::get('/', 'index');
            Route::get('get-overview', 'getData');
            Route::get('get-users-location', 'getUsersLocation');
            Route::get('daily-revenue', 'dailyRevenue');
            Route::get('/belanja-aja', 'belanjaAja');
        });

        // Merchants
        Route::get('merchants/datatable', [App\Http\Controllers\MerchantsController::class, 'datatable']);
        Route::get('merchants/toggle-status/{id}', [App\Http\Controllers\MerchantsController::class, 'toggleStatus']);
        Route::resource('merchants', App\Http\Controllers\MerchantsController::class);

        // Menus
        Route::get('menus/datatable', [App\Http\Controllers\MenusController::class, 'datatable']);
        Route::get('menus/by-merchant/{id}', [App\Http\Controllers\MenusController::class, 'getByMerchant']);
        Route::get('menus/toggle-status/{id}', [App\Http\Controllers\MenusController::class, 'toggleStatus']);
        Route::resource('menus', App\Http\Controllers\MenusController::class);

        // Orders
        Route::get('orders/datatable', [App\Http\Controllers\OrdersController::class, 'datatable']);
        Route::get('orders/list', [App\Http\Controllers\OrdersController::class, 'list']);
        Route::post('orders/process/{orderId}', [App\Http\Controllers\OrdersController::class, 'process']);
        Route::post('orders/reject/{orderId}', [App\Http\Controllers\OrdersController::class, 'reject']);
        Route::resource('orders', App\Http\Controllers\OrdersController::class);

        // Stores
        Route::get('stores/datatable', [App\Http\Controllers\StoresController::class, 'datatable']);
        Route::get('stores/toggle-status/{id}', [App\Http\Controllers\StoresController::class, 'toggleStatus']);
        Route::resource('stores', App\Http\Controllers\StoresController::class);

        // Products
        Route::get('products/datatable', [App\Http\Controllers\ProductsController::class, 'datatable']);
        Route::get('products/by-store/{id}', [App\Http\Controllers\ProductsController::class, 'getByStore']);
        Route::get('products/toggle-status/{id}', [App\Http\Controllers\ProductsController::class, 'toggleStatus']);
        Route::resource('products', App\Http\Controllers\ProductsController::class);

        // Customers
        Route::get('customers/datatable', [App\Http\Controllers\CustomersController::class, 'datatable']);
        Route::resource('customers', App\Http\Controllers\CustomersController::class);

        // Menu Categories
        Route::get('menu-categories/datatable', [App\Http\Controllers\MenuCategoriesController::class, 'datatable']);
        Route::get('menu-categories/by-merchant/{id}', [App\Http\Controllers\MenuCategoriesController::class, 'getByMerchant']);
        Route::resource('menu-categories', App\Http\Controllers\MenuCategoriesController::class);

        // Menu Addon Categories
        Route::get('menu-addon-categories/datatable', [App\Http\Controllers\MenuAddonCategoriesController::class, 'datatable']);
        Route::delete('menu-addon-categories/addon-delete/{id}', [App\Http\Controllers\MenuAddonCategoriesController::class, 'addonDelete']);
        Route::resource('menu-addon-categories', App\Http\Controllers\MenuAddonCategoriesController::class);

        // Product Categories
        Route::get('product-categories/datatable', [App\Http\Controllers\ProductCategoriesController::class, 'datatable']);
        Route::get('product-categories/by-merchant/{id}', [App\Http\Controllers\ProductCategoriesController::class, 'getByMerchant']);
        Route::resource('product-categories', App\Http\Controllers\ProductCategoriesController::class);

        // Product Discounts
        Route::get('product-discounts/datatable', [App\Http\Controllers\ProductDiscountsController::class, 'datatable']);
        Route::resource('product-discounts', App\Http\Controllers\ProductDiscountsController::class);

        // Coupons
        Route::get('coupons/datatable', [App\Http\Controllers\CouponsController::class, 'datatable']);
        Route::resource('coupons', App\Http\Controllers\CouponsController::class);

        // Promotions
        Route::get('promotions/datatable', [App\Http\Controllers\PromotionsController::class, 'datatable']);
        Route::resource('promotions', App\Http\Controllers\PromotionsController::class);

        // Store Orders
        Route::group(['prefix' => 'store-orders', 'controller' => App\Http\Controllers\StoreOrdersController::class], function () {
            Route::get('/', 'index');
            Route::get('datatable', 'datatable');
            Route::get('list', 'list');
            Route::post('process/{orderId}', 'process');
            Route::post('reject/{orderId}', 'reject');
        });

        // Ongoing Orders
        Route::get('ongoing-orders', [App\Http\Controllers\OngoingOrdersController::class, 'index']);

        // Kirim-Aja Orders
        Route::group(['prefix' => 'kirim-aja', 'controller' => App\Http\Controllers\KirimAjaOrdersController::class], function () {
            Route::get('/', 'index');
            Route::get('datatable', 'datatable');
            Route::get('settings', 'settings');
            Route::get('details/{orderId}', 'details');
            Route::get('process/{orderId}', 'process');
            Route::post('update/{orderId}', 'update');
            Route::post('cancel/{orderId}', 'cancel');
            Route::post('calculate-fees', 'calculateFees');
        });

        // Makan-Aja Orders
        Route::group(['prefix' => 'makan-aja', 'controller' => App\Http\Controllers\MakanAjaOrdersController::class], function () {
            Route::get('/', 'index');
            Route::get('datatable', 'datatable');
            Route::get('settings', 'settings');
            Route::get('details/{orderId}', 'details');
            Route::get('process/{orderId}', 'process');
            Route::post('update/{orderId}', 'update');
            Route::post('cancel/{orderId}', 'cancel');
            Route::post('calculate-fees', 'calculateFees');
        });

        // Market-Aja Orders
        Route::group(['prefix' => 'market-aja', 'controller' => App\Http\Controllers\MarketAjaOrdersController::class], function () {
            Route::get('/', 'index');
            Route::get('datatable', 'datatable');
            Route::get('settings', 'settings');
            Route::get('details/{orderId}', 'details');
            Route::get('process/{orderId}', 'process');
            Route::post('update/{orderId}', 'update');
            Route::post('cancel/{orderId}', 'cancel');
            Route::post('calculate-fees', 'calculateFees');
        });

        // Shopping Orders
        Route::get('shopping-orders/fees', [App\Http\Controllers\ShoppingOrdersController::class, 'fees']);
        Route::get('shopping-orders/datatable', [App\Http\Controllers\ShoppingOrdersController::class, 'datatable']);
        Route::get('shopping-orders/whatsapp-template', [App\Http\Controllers\ShoppingOrdersController::class, 'whatsappTemplate']);
        Route::resource('shopping-orders', App\Http\Controllers\ShoppingOrdersController::class);

        // Drivers
        Route::get('drivers/datatable', [App\Http\Controllers\DriversController::class, 'datatable']);
        Route::resource('drivers', App\Http\Controllers\DriversController::class);

        // Roles
        Route::get('roles/datatable', [App\Http\Controllers\RolesController::class, 'datatable']);
        Route::resource('roles', App\Http\Controllers\RolesController::class);

        // Users
        Route::get('users/datatable', [App\Http\Controllers\UsersController::class, 'datatable']);
        Route::resource('users', App\Http\Controllers\UsersController::class);

        // Audit Logs
        Route::get('audit-logs/datatable', [App\Http\Controllers\AuditLogsController::class, 'datatable']);
        Route::resource('audit-logs', App\Http\Controllers\AuditLogsController::class);

        // Settings
    Route::get('settings', [App\Http\Controllers\SettingsController::class, 'index']);
    Route::post('settings', [App\Http\Controllers\SettingsController::class, 'update']);
});

    // Driver Daily Report Routes (Outside manage prefix to match original)
    Route::group(['prefix' => 'driver-daily-report', 'controller' => App\Http\Controllers\DriverDailyReportController::class], function () {
        Route::get('/', 'index');
        Route::get('driver-income', 'income');
        Route::get('driver-rank', 'driverRank');
    });
});
