<?php

use App\Http\Controllers\AuditLogsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CouponsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverDailyReportController;
use App\Http\Controllers\DriversController;
use App\Http\Controllers\KirimAjaOrdersController;
use App\Http\Controllers\MakanAjaOrdersController;
use App\Http\Controllers\MarketAjaOrdersController;
use App\Http\Controllers\MenuAddonCategoriesController;
use App\Http\Controllers\MenuCategoriesController;
use App\Http\Controllers\MenusController;
use App\Http\Controllers\MerchantsController;
use App\Http\Controllers\OngoingOrdersController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductCategoriesController;
use App\Http\Controllers\ProductDiscountsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\PromotionsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShoppingOrdersController;
use App\Http\Controllers\StoreOrdersController;
use App\Http\Controllers\StoresController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return \App\Models\Product::where('id', '01j3g9r0b66m57nz4axa4dvv1e')
        ->get();
});

Route::group(['middleware' => 'throttle:global'], function () {

    Route::get('/', function () {
        return redirect('manage/dashboard');
    });

    Route::group(['prefix' => 'driver-daily-report', 'controller' => DriverDailyReportController::class], function () {
        Route::get('/', 'index');
        Route::get('driver-income', 'income');
        Route::get('driver-rank', 'driverRank');
    });

    // Auth Routes
    Route::group(['prefix' => 'auth', 'controller' => AuthController::class], function () {
        Route::redirect('/', 'auth/login');
        Route::get('login', 'login')->name('auth.login');
        Route::get('logout', 'logout')->name('auth.logout');
        Route::post('login', 'loginRequest')->name('auth.login-request');
        // Route::get('reset/{token}', 'ResetPasswordController@create')->name('password.reset');
        // Route::post('forgot', 'ForgotPasswordController@store')->name('password.email');
        // Route::post('reset', 'ResetPasswordController@store');
    });

    // Backend Routes
    Route::group(['prefix' => 'manage', 'middleware' => ['auth']], function () {
        Route::get('/', function () {
            return redirect('manage/dashboard')->name('home');
        });

        Route::group(['prefix' => 'dashboard', 'controller' => DashboardController::class], function () {
            Route::get('/', 'index');
            Route::get('get-overview', 'getData');
            Route::get('get-users-location', 'getUsersLocation');
            Route::get('daily-revenue', 'dailyRevenue');
            Route::get('/belanja-aja', 'belanjaAja');
        });

        // Manage
        Route::get('customers/datatable', [CustomersController::class, 'datatable']);
        Route::resource('customers', CustomersController::class);

        Route::get('drivers/datatable', [DriversController::class, 'datatable']);
        Route::resource('drivers', DriversController::class);

        Route::get('coupons/datatable', [CouponsController::class, 'datatable']);
        Route::resource('coupons', CouponsController::class);

        Route::get('merchants/datatable', [MerchantsController::class, 'datatable']);
        Route::get('merchants/toggle-status/{id}', [MerchantsController::class, 'toggleStatus']);
        Route::resource('merchants', MerchantsController::class);

        Route::get('menus/datatable', [MenusController::class, 'datatable']);
        Route::get('menus/by-merchant/{id}', [MenusController::class, 'getByMerchant']);
        Route::get('menus/toggle-status/{id}', [MenusController::class, 'toggleStatus']);
        Route::get('menus/toggle-all-status/{merchantId}', [MenusController::class, 'toggleAllStatus']);
        Route::post('menus/import', [MenusController::class, 'import']);
        Route::resource('menus', MenusController::class);

        Route::get('menu-categories/datatable', [MenuCategoriesController::class, 'datatable']);
        Route::get('menu-categories/by-merchant/{id}', [MenuCategoriesController::class, 'getByMerchant']);
        Route::resource('menu-categories', MenuCategoriesController::class);

        Route::get('menu-addon-categories/datatable', [MenuAddonCategoriesController::class, 'datatable']);
        Route::delete('menu-addon-categories/addon-delete/{id}', [MenuAddonCategoriesController::class, 'addonDelete']);
        Route::resource('menu-addon-categories', MenuAddonCategoriesController::class);

        Route::get('orders/datatable', [OrdersController::class, 'datatable']);
        Route::get('orders/list', [OrdersController::class, 'list']);
        Route::post('orders/process/{orderId}', [OrdersController::class, 'process']);
        Route::post('orders/reject/{orderId}', [OrdersController::class, 'reject']);
        Route::resource('orders', OrdersController::class);

        Route::get('stores/datatable', [StoresController::class, 'datatable']);
        Route::get('stores/toggle-status/{id}', [StoresController::class, 'toggleStatus']);
        Route::resource('stores', StoresController::class);

        Route::get('product-categories/datatable', [ProductCategoriesController::class, 'datatable']);
        Route::get('product-categories/by-merchant/{id}', [ProductCategoriesController::class, 'getByMerchant']);
        Route::resource('product-categories', ProductCategoriesController::class);

        Route::get('products/datatable', [ProductsController::class, 'datatable']);
        Route::get('products/by-store/{id}', [ProductsController::class, 'getByStore']);
        Route::get('products/toggle-status/{id}', [ProductsController::class, 'toggleStatus']);
        Route::get('products/toggle-all-status/{merchantId}', [ProductsController::class, 'toggleAllStatus']);
        Route::post('products/import', [ProductsController::class, 'import']);
        Route::resource('products', ProductsController::class);

        Route::get('product-discounts/datatable', [ProductDiscountsController::class, 'datatable']);
        Route::resource('product-discounts', ProductDiscountsController::class);

        Route::get('promotions/datatable', [PromotionsController::class, 'datatable']);
        Route::resource('promotions', PromotionsController::class);

        Route::group(['prefix' => 'store-orders', 'controller' => StoreOrdersController::class], function () {
            Route::get('/', [StoreOrdersController::class, 'index']);
            Route::get('datatable', [StoreOrdersController::class, 'datatable']);
            Route::get('list', [StoreOrdersController::class, 'list']);
            Route::post('process/{orderId}', [StoreOrdersController::class, 'process']);
            Route::post('reject/{orderId}', [StoreOrdersController::class, 'reject']);
        });

        Route::get('ongoing-orders', [OngoingOrdersController::class, 'index']);

        // Services
        Route::group(['prefix' => 'kirim-aja', 'controller' => KirimAjaOrdersController::class], function () {
            Route::get('/', 'index');
            Route::get('datatable', 'datatable');
            Route::get('settings', 'settings');
            Route::get('details/{orderId}', 'details');
            Route::get('process/{orderId}', 'process');
            Route::post('update/{orderId}', 'update');
            Route::post('cancel/{orderId}', 'cancel');
            Route::post('calculate-fees', 'calculateFees');
        });

        Route::group(['prefix' => 'makan-aja', 'controller' => MakanAjaOrdersController::class], function () {
            Route::get('/', 'index');
            Route::get('datatable', 'datatable');
            Route::get('settings', 'settings');
            Route::get('details/{orderId}', 'details');
            Route::get('process/{orderId}', 'process');
            Route::post('update/{orderId}', 'update');
            Route::post('cancel/{orderId}', 'cancel');
            Route::post('calculate-fees', 'calculateFees');
        });

        Route::group(['prefix' => 'market-aja', 'controller' => MarketAjaOrdersController::class], function () {
            Route::get('/', 'index');
            Route::get('datatable', 'datatable');
            Route::get('settings', 'settings');
            Route::get('details/{orderId}', 'details');
            Route::get('process/{orderId}', 'process');
            Route::post('update/{orderId}', 'update');
            Route::post('cancel/{orderId}', 'cancel');
            Route::post('calculate-fees', 'calculateFees');
        });

        Route::get('shopping-orders/fees', [ShoppingOrdersController::class, 'fees']);
        Route::get('shopping-orders/datatable', [ShoppingOrdersController::class, 'datatable']);
        Route::get('shopping-orders/whatsapp-template', [ShoppingOrdersController::class, 'whatsappTemplate']);
        Route::resource('shopping-orders', ShoppingOrdersController::class);

        // Administration
        Route::get('roles/datatable', [RolesController::class, 'datatable']);
        Route::resource('roles', RolesController::class);

        Route::get('users/datatable', [UsersController::class, 'datatable']);
        Route::resource('users', UsersController::class);

        Route::get('audit-logs/datatable', [AuditLogsController::class, 'datatable']);
        Route::resource('audit-logs', AuditLogsController::class);

        Route::get('settings', [SettingsController::class, 'index']);
        Route::post('settings', [SettingsController::class, 'update']);
    });
});


