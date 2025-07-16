<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::apiResource('products', ProductController::class);
Route::get('/test', function () {
    return response()->json(['message' => 'Welcome to the API']);
});