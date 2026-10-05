<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Ramen Bro API is running',
        'time' => now()->toISOString(),
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index']);

Route::apiResource('products', ProductController::class);
Route::apiResource('materials', MaterialController::class);
Route::apiResource('orders', OrderController::class)->only([
    'index', 'store', 'show', 'update'
]);
