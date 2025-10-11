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

        // More routes will be added as we migrate more controllers
    });
});
