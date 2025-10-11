<?php

use App\Http\Controllers\Api\Hapi\CouponsApiController;
use App\Http\Controllers\Api\Hapi\CustomerAddressesApiController;
use App\Http\Controllers\Api\Hapi\CustomerAuthApiController;
use App\Http\Controllers\Api\Hapi\KirimAja\ShippingOrdersApiController;
use App\Http\Controllers\Api\Hapi\MarketAja\ProductsApiController;
use App\Http\Controllers\Api\Hapi\MarketAja\StoreOrdersApiController;
use App\Http\Controllers\Api\Hapi\MarketAja\StoresApiController;
use App\Http\Controllers\Api\Hapi\MenusApiController;
use App\Http\Controllers\Api\Hapi\MerchantsApiController;
use App\Http\Controllers\Api\Hapi\OrdersApiController;
use App\Http\Controllers\Api\Hapi\PaymentMethodsApiController;
use App\Http\Controllers\Api\Hapi\ProfileApiController;
use App\Http\Controllers\Api\Hapi\PromotionsApiController;
use App\Http\Controllers\Api\Horus\DriverAuthApiController;
use App\Http\Controllers\Api\Horus\UsersApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'middleware' => ['throttle:global']], function () {
    Route::group(['middleware' => 'guest'], function () {
        Route::group(['prefix' => 'auth', 'controller' => CustomerAuthApiController::class], function () {
            Route::post('login', 'login');
            Route::post('verify-otp', 'verifyOtp');
//          Route::post('register', 'loginRequest');
        });
    });

    /** Public Routes */
    // Promotions
    Route::group(['prefix' => 'promotions', 'controller' => PromotionsApiController::class], function () {
        Route::get('/', 'index');
    });

    // Merchants
    Route::group(['prefix' => 'merchants', 'controller' => MerchantsApiController::class], function () {
        Route::get('{slug}', 'show');
        Route::get('nearby', 'nearby');
        Route::get('get-distance/{slug}', 'getDistance');
    });

    // Menus
    Route::group(['prefix' => 'menus', 'controller' => MenusApiController::class], function () {
        Route::get('search', 'searchByTerm');
        Route::get('randomize', 'randomize');
    });

    // Stores
    Route::group(['prefix' => 'stores', 'controller' => StoresApiController::class], function () {
        Route::get('{storeId}', 'show');
        Route::get('sale/{storeId}', 'sale');
        Route::get('nearby', 'nearby');
        Route::get('get-distance/{storeId}', 'getDistance');
    });

    // Products
    Route::group(['prefix' => 'products', 'controller' => ProductsApiController::class], function () {
        Route::get('search', 'searchByTerm');
        Route::get('{storeSlug}/search', 'searchStoreProducts');
        Route::get('randomize', 'randomize');
    });
    /** End of Public Routes */

    /** Auth Routes */
    // Auth protected routes
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::group(['prefix' => 'auth', 'controller' => CustomerAuthApiController::class], function () {
            Route::post('logout', 'logout');
        });

        // Coupon
        Route::group(['prefix' => 'coupons', 'controller' => CouponsApiController::class], function () {
            Route::get('/', 'getAll');
            Route::get('{code}', 'show');
        });

        // Orders
        Route::group(['prefix' => 'orders', 'controller' => OrdersApiController::class], function () {
            Route::get('/', 'getOrders');
            Route::get('{orderId}', 'getOrder');
            Route::post('{orderId}/upload-payment-proof', 'uploadPaymentProof');
            Route::post('{orderId}/rate', 'rateOrder');
            Route::post('submit', 'submit');
            Route::delete('{orderId}', 'cancelOrder');
        });

        // Customers
        Route::group(['prefix' => 'customers'], function () {
            Route::group(['prefix' => 'addresses', 'controller' => CustomerAddressesApiController::class], function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::put('{id}', 'update');
                Route::delete('{id}', 'destroy');
                Route::post('/set-default/{id}', 'default');
            });
        });

        Route::post('profile', [ProfileApiController::class, 'update']);

        Route::get('user', function (Request $request) {
            return $request->user();
        });

        Route::get('payment-methods', [PaymentMethodsApiController::class, 'index']);

        /** Start of Kirim-Aja Routes */
        Route::group(['prefix' => 'kirim-aja'], function () {
            Route::group(['prefix' => 'orders', 'controller' => ShippingOrdersApiController::class], function () {
                Route::get('/', 'getOrders');
                Route::get('{orderId}', 'getOrder');
                Route::post('calculate-fees', 'calculateFees');
                Route::post('{orderId}/upload-payment-proof', 'uploadPaymentProof');
                Route::post('{orderId}/rate', 'rateOrder');
                Route::post('submit', 'submit');
                Route::delete('{orderId}', 'cancelOrder');
            });
        });
        /** Start of Kirim-Aja Routes */

        /** Start of Market-Aja Routes */
        // Orders
        Route::group(['prefix' => 'market-aja/orders', 'controller' => StoreOrdersApiController::class], function () {
            Route::get('/', 'getOrders');
            Route::get('{orderId}', 'getOrder');
            Route::post('{orderId}/upload-payment-proof', 'uploadPaymentProof');
            Route::post('submit', 'submit');
            Route::delete('{orderId}', 'cancelOrder');
        });
        /** End of Market-Aja Routes */
    });
    /** End of Auth Routes */
});

// Start of Horus routes
Route::group(['prefix' => 'v1/horus', 'middleware' => ['throttle:global']], function () {
    Route::group(['middleware' => 'guest'], function () {
        Route::group(['prefix' => 'auth', 'controller' => DriverAuthApiController::class], function () {
            Route::post('login', 'login');
            Route::post('verify-otp', 'verifyOtp');
        });
    });

    // Auth protected routes
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::group(['prefix' => 'auth', 'controller' => DriverAuthApiController::class], function () {
            Route::post('logout', 'logout');
        });

        Route::group(['prefix' => 'user', 'controller' => UsersApiController::class], function () {
            Route::post('fcm-token', 'storeFcmToken');
        });
    });
});
// End of Horus routes
