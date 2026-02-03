<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentRedirectController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VoucherController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::middleware('throttle:10,1')->controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('register', 'register')->name('register');
    Route::post('login', 'login')->name('login');
    Route::post('logout', 'logout')->middleware('auth:sanctum')->name('logout');
    Route::post('create-token', 'createToken')->middleware('auth:sanctum')->name('create-token');
});

// Protected routes (requires authentication) -> Seller
Route::middleware(['auth:sanctum', 'role:seller', 'throttle:10,1'])->group(function () {
    Route::prefix('products')->controller(ProductController::class)->group(function () {
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::post('/bulk', 'bulkStore');
    });
    
    Route::post('shop', [ShopController::class, 'store']);
    
    Route::prefix('orders')->controller(OrderController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{orderId}', 'show');
    });
});

// Protected routes (requires authentication) -> Admin
Route::middleware(['auth:sanctum', 'role:admin', 'throttle:10,1'])->group(function () {
    Route::prefix('users')->controller(UserController::class)->group(function () {
        Route::get('/', 'index');
        Route::delete('/{id}', 'destroy');
    });
    
    Route::prefix('categories')->controller(CategoryController::class)->group(function () {
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::post('/bulk', 'bulkStore');
    });
    
    Route::prefix('brands')->controller(BrandController::class)->group(function () {
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::post('/bulk', 'bulkStore');
    });

    Route::prefix('vouchers')->controller(VoucherController::class)->group(function () {
        Route::get('/admin', 'adminIndex');
    });

    Route::apiResource('vouchers', VoucherController::class)->only(['store', 'update', 'destroy']);
});

// Protected routes (requires authentication) -> User
Route::middleware(['auth:sanctum', 'role:user', 'throttle:10,1'])->group(function () {
    Route::apiResource('carts', CartController::class)->only(['index', 'store', 'destroy']);
    
    Route::prefix('orders')->controller(OrderController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{orderId}', 'show');
        Route::post('/checkout', 'checkout');
    });
});

// Protected routes (requires authentication) -> All Role
Route::middleware(['auth:sanctum', 'throttle:10,1'])->group(function () {
    Route::apiResource('vouchers', VoucherController::class)->only(['index', 'show']);
    Route::prefix('vouchers')->controller(VoucherController::class)->group(function (){
        Route::post('/validate', 'validate');
        Route::post('/apply', 'apply');
        Route::delete('/{orderId}/refund', 'refund');
    });
});

// Unprotected routes (without authentication)
Route::middleware('throttle:10,1')->group(function () {
    Route::prefix('products')->controller(ProductController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/search', 'search');
        Route::get('/{id}', 'show');
    });
    
    Route::prefix('categories')->controller(CategoryController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
    });
    
    Route::prefix('brands')->controller(BrandController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
    });

    Route::post('payments/midtrans-callback', [PaymentController::class, 'midtransCallback']);
});

// Payment redirect routes (for webview)
Route::prefix('payment')->controller(PaymentRedirectController::class)->group(function () {
    Route::get('/finish', 'finish');
    Route::get('/unfinish', 'unfinish');
    Route::get('/error', 'error');
});