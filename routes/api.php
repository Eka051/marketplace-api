<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    
    // Authentication routes
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('register', 'register')->name('register');
        Route::post('login', 'login')->name('login');
        Route::post('logout', 'logout')->middleware('auth:sanctum')->name('logout');
        Route::post('create-token', 'createToken')->middleware('auth:sanctum')->name('create-token');
    });

    // Protected routes (requires authentication) -> Seller
    Route::middleware(['auth:sanctum', 'role:seller'])->group(function () {
        Route::apiResource('products', ProductController::class);
    });

});

