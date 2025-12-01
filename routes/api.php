<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductsController;


// created by kariem ibrahiem

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
});


// Protected routes
Route::group(['middleware' => 'auth:client-api'], function () {
    // Product routes
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductsController::class, 'getProducts']);
        Route::get('/{id}', [ProductsController::class, 'getProductById']);
    });
    // Order routes
    Route::prefix("orders")->group(function () {
        Route::get('/', [OrderController::class, 'getOrders']);
        Route::post("/", [OrderController::class, 'createOrder']);
        Route::post("/hold", [OrderController::class, 'holdOrder']);
    });
    // Payment routes
    Route::prefix('payments')->group(function () {
        Route::post('/checkout', [PaymentController::class, 'checkout']);
    });

    // logout route
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('logout');
});
// Payment webhook route "must be outside auth middleware"
Route::post('payments/webhook', [PaymentController::class, 'webhook']);
