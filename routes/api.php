<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\UserController;
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
    Route::apiResource('product', ProductController::class)->only(['store', 'update', 'destroy']);
    Route::post('products/bulk', [ProductController::class, 'bulkStore']);

    Route::post('shop', [ShopController::class, 'store']);
});

// Protected routes (requires authentication) -> Admin
Route::middleware(['auth:sanctum', 'role:admin', 'throttle:10,1'])->group(function () {
    Route::apiResource('users', UserController::class)->only(['index', 'destroy']);
    Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);
    Route::post('categories/bulk', [CategoryController::class, 'bulkStore']);
    Route::apiResource('brands', BrandController::class)->only(['store', 'update', 'destroy']);
    Route::post('brands/bulk', [BrandController::class, 'bulkStore']);
});

// Unprotected routes (without authentication)
Route::middleware('throttle:10,1')->group(function () {
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);
    Route::get('products/search', [ProductController::class, 'search']);
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::apiResource('brands', BrandController::class)->only(['index', 'show']);
});