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

        // More routes will be added as we migrate more controllers
    });
});
