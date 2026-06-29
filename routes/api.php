<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\Api\Noritsu\StoreController;
use App\Http\Controllers\Api\Noritsu\TemplateController;
use App\Http\Controllers\Api\Noritsu\OrderController as NoritsuOrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Product price calculation
    Route::post('/products/{product}/calculate-price', [ProductController::class, 'calculatePrice']);

    // File uploads
    Route::post('/upload', [UploadController::class, 'store']);
    Route::delete('/upload/{upload}', [UploadController::class, 'destroy']);

    // Cart (AJAX)
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::patch('/cart/update/{itemId}', [CartController::class, 'update']);
    Route::delete('/cart/remove/{itemId}', [CartController::class, 'remove']);
    Route::post('/cart/coupon', [CartController::class, 'applyCoupon']);
    Route::delete('/cart/coupon', [CartController::class, 'removeCoupon']);
});

// Noritsu Print App Routes
Route::prefix('noritsu/v1')->group(function () {
    // Public (No Auth Required for MVP)
    Route::get('config', [NoritsuOrderController::class, 'session']); // Reusing session for config for now
    
    // Stores
    Route::get('stores', [StoreController::class, 'index']);
    Route::get('stores/{id}', [StoreController::class, 'show']);

    // Templates
    Route::get('templates', [TemplateController::class, 'index']);
    Route::get('templates/{id}', [TemplateController::class, 'show']);

    // Orders
    Route::post('orders/session', [NoritsuOrderController::class, 'session']);
    Route::post('orders', [NoritsuOrderController::class, 'store']);
    Route::get('orders/{id}', [NoritsuOrderController::class, 'show']);
});

// Qrinto Print Agent API
Route::get('print-jobs/pending', [App\Http\Controllers\Api\AgentController::class, 'getPendingJobs']);
Route::post('agent/messages', [App\Http\Controllers\Api\AgentController::class, 'handleMessage']);
