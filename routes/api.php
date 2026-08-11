<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\StoreApiController;
use App\Http\Controllers\Api\V1\CategoryApiController;
use App\Http\Controllers\Api\V1\ProductApiController;
use App\Http\Controllers\Api\V1\TemplateApiController;
use App\Http\Controllers\Api\V1\UploadApiController;
use App\Http\Controllers\Api\V1\CartApiController;
use App\Http\Controllers\Api\V1\CheckoutApiController;
use App\Http\Controllers\Api\V1\OrderApiController;
use App\Http\Controllers\Api\V1\CouponApiController;
use App\Http\Controllers\Api\V1\AiApiController;

use App\Http\Controllers\Api\Noritsu\StoreController as NoritsuStoreController;
use App\Http\Controllers\Api\Noritsu\TemplateController as NoritsuTemplateController;
use App\Http\Controllers\Api\Noritsu\OrderController as NoritsuOrderController;

/*
|--------------------------------------------------------------------------
| Qrinto 3rd Party Application REST API (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── 1. Authentication Endpoints ──
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // ── 2. Store & Location Endpoints ──
    Route::get('/stores', [StoreApiController::class, 'index']);
    Route::get('/stores/nearest', [StoreApiController::class, 'nearest']);
    Route::get('/stores/{idOrCode}', [StoreApiController::class, 'show']);

    // ── 3. Product Categories & Types ──
    Route::get('/categories', [CategoryApiController::class, 'index']);
    Route::get('/categories/{slugOrId}', [CategoryApiController::class, 'show']);

    // ── 4. Products & Customization Specs ──
    Route::get('/products', [ProductApiController::class, 'index']);
    Route::get('/products/{slugOrId}', [ProductApiController::class, 'show']);
    Route::post('/products/{id}/calculate-price', [ProductApiController::class, 'calculatePrice']);

    // ── 5. Templates ──
    Route::get('/templates', [TemplateApiController::class, 'index']);
    Route::get('/templates/{id}', [TemplateApiController::class, 'show']);

    // ── 6. File & Canvas Uploads ──
    Route::post('/upload', [UploadApiController::class, 'uploadPhoto']);
    Route::post('/upload-composite', [UploadApiController::class, 'uploadComposite']);
    Route::delete('/upload/{id}', [UploadApiController::class, 'destroy']);

    // ── 7. Shopping Cart ──
    Route::get('/cart', [CartApiController::class, 'index']);
    Route::post('/cart/add', [CartApiController::class, 'add']);
    Route::patch('/cart/items/{itemId}', [CartApiController::class, 'update']);
    Route::delete('/cart/items/{itemId}', [CartApiController::class, 'remove']);
    Route::delete('/cart/clear', [CartApiController::class, 'clear']);
    Route::post('/cart/coupon', [CartApiController::class, 'applyCoupon']);
    Route::delete('/cart/coupon', [CartApiController::class, 'removeCoupon']);

    // ── 8. Checkout & Payments ──
    Route::post('/checkout/cash', [CheckoutApiController::class, 'checkoutCash']);
    Route::post('/checkout/paypal/create', [CheckoutApiController::class, 'createPaypalTransaction']);

    // ── 9. Order Management & Tracking ──
    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::post('/orders/track', [OrderApiController::class, 'track']);
    Route::get('/orders/track/{orderNumber}', [OrderApiController::class, 'track']);
    Route::get('/orders/{idOrNumber}', [OrderApiController::class, 'show']);

    // ── 10. Coupon Validation ──
    Route::post('/coupons/validate', [CouponApiController::class, 'validateCoupon']);

    // ── 11. AI Assistant Chat ──
    Route::post('/ai/chat', [AiApiController::class, 'chat']);
});

/*
|--------------------------------------------------------------------------
| Noritsu Print App API Routes (v1)
|--------------------------------------------------------------------------
*/
Route::prefix('noritsu/v1')->group(function () {
    Route::get('config', [NoritsuOrderController::class, 'session']);
    Route::get('stores', [NoritsuStoreController::class, 'index']);
    Route::get('stores/{id}', [NoritsuStoreController::class, 'show']);
    Route::get('templates', [NoritsuTemplateController::class, 'index']);
    Route::get('templates/{id}', [NoritsuTemplateController::class, 'show']);
    Route::post('orders/session', [NoritsuOrderController::class, 'session']);
    Route::post('orders', [NoritsuOrderController::class, 'store']);
    Route::get('orders/{id}', [NoritsuOrderController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Qrinto Print Agent API
|--------------------------------------------------------------------------
*/
Route::get('print-jobs/pending', [App\Http\Controllers\Api\AgentController::class, 'getPendingJobs']);
Route::post('agent/messages', [App\Http\Controllers\Api\AgentController::class, 'handleMessage']);
