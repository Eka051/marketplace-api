<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('register', 'register')->name('register');
    Route::post('login', 'login')->name('login');
    Route::post('logout', 'logout')->middleware('auth:sanctum')->name('logout');
    Route::post('create-token', 'createToken')->middleware('auth:sanctum')->name('create-token');
});

// Protected routes (requires authentication) -> Seller
Route::middleware(['auth:sanctum', 'role:seller', 'throttle:10,1'])->group(function () {
    Route::post('product', [ProductController::class, 'store']);
});

// Unprotected routes (without authentication)
Route::middleware('throttle|10,1')->group(function () {
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/search', [ProductController::class, 'search']);
});
