<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MilesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;


// Públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::get('/categories', [CategoryController::class, 'index']);

// Catálogo público (sin auth)
Route::get('/products',      [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/tiers',         [MilesController::class, 'tiers']);

// Protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
        // Checkout
    Route::post('/checkout',              [OrderController::class, 'checkout']);
    Route::get ('/checkout/preview',      [OrderController::class, 'preview']);
    Route::post('/miles/redeem-product',  [OrderController::class, 'redeemProduct']);
    Route::get ('/orders',                [OrderController::class, 'index']);
    Route::get ('/orders/{order}',        [OrderController::class, 'show']);

    // Millas
    Route::get('/miles',              [MilesController::class, 'balance']);
    Route::get('/miles/transactions', [MilesController::class, 'transactions']);
    Route::post('/miles/redeem',      [MilesController::class, 'redeem']);

    // Carrito y órdenes (client)
    Route::get   ('/cart',        [CartController::class,  'index']);
    Route::post  ('/cart',        [CartController::class,  'store']);
    Route::delete('/cart',        [CartController::class,  'clear']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);
    Route::post  ('/checkout',    [OrderController::class, 'checkout']);
    Route::get   ('/orders',      [OrderController::class, 'index']);
    Route::get   ('/orders/{order}',  [OrderController::class, 'show']);

    // Seller y admin — acreditar millas
    Route::middleware('seller')->group(function () {
        Route::post('/miles/earn', [MilesController::class, 'earn']);
        Route::get ('/admin/clients',   [UserController::class,  'findByEmail']);
        Route::get ('/admin/clients/all', [UserController::class, 'clients']);
    });

    // Solo admin
    Route::middleware('admin')->group(function () {
        Route::get   ('/admin/products',           [ProductController::class, 'adminIndex']);
        Route::post  ('/products',                 [ProductController::class, 'store']);
        Route::put   ('/products/{product}',       [ProductController::class, 'update']);
        Route::delete('/products/{product}',       [ProductController::class, 'destroy']);
        Route::post  ('/categories',          [CategoryController::class, 'store']);
        Route::put   ('/categories/{category}',[CategoryController::class, 'update']);
        Route::delete('/categories/{category}',[CategoryController::class, 'destroy']);
    });

});
