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

        // Dashboard routes will be added here
        // More routes will be added as we migrate controllers
    });
});
