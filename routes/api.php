<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/products', [ProductController::class, 'store']);

    Route::put('/products/{product}', [
        ProductController::class,
        'update',
    ]);

    Route::delete('/products/{product}', [
        ProductController::class,
        'destroy',
    ]);

    Route::post('/orders', [OrderController::class, 'store']);

    Route::get('/orders', [OrderController::class, 'index']);

    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post(
        '/orders/{order}/payment',
        [PaymentController::class, 'createPaymentIntent']
    );

});

Route::post(
    '/stripe/webhook',
    [StripeWebhookController::class, 'handle']
);