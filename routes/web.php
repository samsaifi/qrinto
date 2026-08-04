<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\Admin\OrderPrintController;
use App\Http\Controllers\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\PaperTypeController as AdminPaperTypeController;
use App\Http\Controllers\Admin\AIProductController;
use App\Http\Controllers\NoritsuController;
use App\Http\Controllers\QuickFlowController;
use App\Http\Controllers\QuickFlowPcController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartPcController;
use App\Http\Controllers\AIController;
Route::get('/storage-link', function () {
    $link = public_path('storage');
    $target = storage_path('app/public');

    // Remove broken file/link if it exists
    if (file_exists($link) || is_link($link)) {
        if (is_dir($link)) {
            rmdir($link);
        } else {
            unlink($link);
        }
    }

    // On Windows, use junction (no admin needed). On Linux/Mac, use symlink.
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        exec('mklink /J "' . $link . '" "' . $target . '"', $output, $code);
        if ($code !== 0) {
            return 'Failed to create junction: ' . implode("\n", $output);
        }
    } else {
        symlink($target, $link);
    }

    return 'Storage link created successfully!';
});
Route::get('/store/{storeCode}', function ($storeCode) { 
    return app(\App\Http\Controllers\StoreQrController::class)->scan($storeCode);
})->name('store.scan');


Route::post('/chat', [AIController::class, 'chat']);
/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
// artisan route to clear cache in laravel
Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
    return 'Cache cleared successfully!';
});
/*
|--------------------------------------------------------------------------
| Public & Customer Flow Routes
|--------------------------------------------------------------------------
*/

$registerFlowRoutes = function (string $controller, string $namePrefix, string $cartController = CartController::class) {
    Route::name("$namePrefix.")->group(function () use ($controller, $namePrefix, $cartController) {
        Route::get('/type/{type:slug}', [$controller, 'category'])->name('category');
        Route::get('/product/{product:slug}', [$controller, 'product'])->name('product');
        Route::get('/customize/{product:slug}', [$controller, 'customize'])->name('customize');
        Route::post('/upload', [$controller, 'upload'])->name('upload');
        Route::post('/upload-composite', [$controller, 'uploadComposite'])->name('upload_composite');
        Route::post('/checkout', [$controller, 'checkout'])->name('checkout');
        Route::post('/paypal/create', [$controller, 'createPaypalOrder'])->name('paypal.create');
        Route::post('/paypal/capture', [$controller, 'capturePaypalOrder'])->name('paypal.capture');
        Route::post('/checkout/cash', [$controller, 'checkoutCash'])->name('checkout.cash');

        Route::get('/custom-print', [$controller, 'qrinto'])->name('qrinto');
        Route::post('/custom-print/checkout/cash', [$controller, 'qrintoCheckoutCash'])->name('qrinto.checkout.cash');
        Route::post('/custom-print/paypal/create', [$controller, 'qrintoPaypalCreate'])->name('qrinto.paypal.create');
        Route::post('/custom-print/paypal/capture', [$controller, 'qrintoPaypalCapture'])->name('qrinto.paypal.capture');

        Route::get('/confirmation/{order}', [$controller, 'confirmation'])->name('confirmation');
        Route::get('/print/{order}', [$controller, 'printDesign'])->name('print');
        Route::get('/print/{order}/pdf', [$controller, 'viewPdf'])->name('print.pdf');

        Route::get('/find-store', [$controller, 'findStore'])->name('find-store');
        Route::get('/set-store', fn() => redirect()->route("$namePrefix.find-store"));
        Route::post('/set-store', [$controller, 'setStore'])->name('set-store');
        Route::post('/apply-coupon', [$controller, 'applyCoupon'])->name('apply-coupon');

        Route::get('/cart-checkout', [$controller, 'cartCheckout'])->name('cart-checkout');
        Route::post('/cart-checkout/cash', [$controller, 'cartCheckoutCash'])->name('cart-checkout.cash');
        Route::post('/cart-checkout/paypal/create', [$controller, 'cartPaypalCreate'])->name('cart-checkout.paypal.create');
        Route::post('/cart-checkout/paypal/capture', [$controller, 'cartPaypalCapture'])->name('cart-checkout.paypal.capture');

        Route::get('/cart', [$cartController, 'index'])->name('cart.index');
        Route::post('/cart/add', [$cartController, 'add'])->name('cart.add');
        Route::post('/cart/update/{itemId}', [$cartController, 'update'])->name('cart.update');
        Route::delete('/cart/remove/{itemId}', [$cartController, 'remove'])->name('cart.remove');
        Route::post('/cart/apply-coupon', [$cartController, 'applyCoupon'])->name('cart.apply-coupon');
        Route::post('/cart/remove-coupon', [$cartController, 'removeCoupon'])->name('cart.remove-coupon');
        Route::get('/cart/count', [$cartController, 'count'])->name('cart.count');

        Route::get('/track', [$controller, 'trackForm'])->name('track.form');
        Route::post('/track', [$controller, 'track'])->name('track');
        Route::get('/track/{orderNumber}', [$controller, 'trackOrder'])->name('track.order');

        Route::get('/thumbnails/generate', [\App\Http\Controllers\UtilityController::class, 'generateThumbnails'])->name('utility.thumbnails');
    });
};

Route::get('/', [QuickFlowController::class, 'index'])->name('flow.index');
$registerFlowRoutes(QuickFlowController::class, 'flow');

Route::prefix('pc')->group(function () use ($registerFlowRoutes) {
    Route::get('/', [QuickFlowPcController::class, 'index'])->name('flow-pc.index');
    $registerFlowRoutes(QuickFlowPcController::class, 'flow-pc', CartPcController::class);
});

// Alias for homepage
Route::get('/home', fn() => redirect()->route('flow.index'))->name('home');

/*
|--------------------------------------------------------------------------
| Upload API
|--------------------------------------------------------------------------
*/
// Keep this for direct editor uploads if still used by client
Route::post('/api/upload', [\App\Http\Controllers\QrintoController::class, 'upload'])->name('qrinto.upload');
Route::post('/api/user-designs/save', [\App\Http\Controllers\Api\UserDesignController::class, 'save'])->name('api.user-designs.save');

/*
|--------------------------------------------------------------------------
| Noritsu Editor
|--------------------------------------------------------------------------
*/
Route::prefix('noritsu')->name('noritsu.')->group(function () {
    Route::get('/', [NoritsuController::class, 'index'])->name('index');
    Route::get('/editor/{product:slug}', [NoritsuController::class, 'editor'])->name('editor');
});

/*
|--------------------------------------------------------------------------
| Store QR Code Routes
|--------------------------------------------------------------------------
*/

Route::get('/store/{storeCode}/qr', [\App\Http\Controllers\StoreQrController::class, 'show'])->name('store.qr');


/*
|--------------------------------------------------------------------------
| Auth & Account Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('my-account')->name('customer.')->group(function () {
        Route::get('/', [CustomerController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [CustomerController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [CustomerController::class, 'orderDetail'])->name('orders.show');
    });
});

Route::get('/dashboard', function () {
    if (auth()->user()->canAccessAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('customer.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Order Printing (accessible from frontend confirmation page)
Route::post('/orders/{order}/print', [OrderPrintController::class, 'sendPrint'])->name('orders.print');
Route::get('/stores-search', [AdminStoreController::class, 'searchStores'])->name('stores.search');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/stores-summary', [AdminDashboardController::class, 'storesSummary'])->name('stores.summary');
    Route::get('/stores-summary/export', [AdminDashboardController::class, 'exportStoresSummary'])->name('stores.summary.export');
    Route::get('/store-statistics', [AdminDashboardController::class, 'storeStatistics'])->name('stores.statistics');
    Route::get('/store-statistics/export', [AdminDashboardController::class, 'exportStoreStatistics'])->name('stores.statistics.export');

    // Products
    Route::resource('products', AdminProductController::class);
    Route::post('products/bulk-delete', [AdminProductController::class, 'bulkDelete'])->name('products.bulkDelete');
    Route::post('products/{product}/options', [AdminProductController::class, 'storeOptionGroup'])->name('products.options.store');
    Route::post('option-groups/{optionGroup}/values', [AdminProductController::class, 'storeOptionValue'])->name('options.values.store');
    Route::delete('option-groups/{optionGroup}', [AdminProductController::class, 'deleteOptionGroup'])->name('options.destroy');
    Route::delete('option-values/{optionValue}', [AdminProductController::class, 'deleteOptionValue'])->name('optionValues.destroy');
    Route::delete('product-images/{imageId}', [AdminProductController::class, 'deleteImage'])->name('productImages.destroy');
    Route::get('products/{product}/mask', [AdminProductController::class, 'maskEditor'])->name('products.mask');
    Route::post('products/{product}/mask', [AdminProductController::class, 'saveMask'])->name('products.mask.save');

    // Categories
    Route::resource('categories', AdminCategoryController::class);

    // Orders
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/pdf', [AdminOrderController::class, 'show_pdf'])->name('orders.show.pdf');
    Route::get('orders/{order}/realtime_pdf', [AdminOrderController::class, 'realtimePdf'])->name('orders.realtime_pdf');
    Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('orders/{order}/duplicate', [AdminOrderController::class, 'duplicate'])->name('orders.duplicate');

    // Order Printing
    Route::post('orders/{order}/print', [OrderPrintController::class, 'sendPrint'])->name('orders.print');
    Route::get('orders/{order}/print-page', [OrderPrintController::class, 'printPage'])->name('orders.print.page');

    // Users
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::patch('users/{user}/toggle', [AdminUserController::class, 'toggleStatus'])->name('users.toggle');

    // Coupons
    Route::resource('coupons', AdminCouponController::class);

    // Store Management
    Route::resource('stores', AdminStoreController::class);
    Route::post('stores/{id}/toggle-status', [AdminStoreController::class, 'toggleStatus'])->name('stores.toggle');
    Route::get('stores-search', [AdminStoreController::class, 'searchStores'])->name('stores.search');
    Route::post('stores/{id}/users', [AdminStoreController::class, 'storeUser'])->name('stores.users.store');
    Route::delete('stores/{storeId}/users/{userId}', [AdminStoreController::class, 'deleteUser'])->name('stores.users.destroy');
    Route::post('stores/{id}/ftp-test', [AdminStoreController::class, 'testFtp'])->name('stores.ftp.test');

    // Qrinto Sizes
    Route::resource('qrinto-sizes', \App\Http\Controllers\Admin\QrintoSizeController::class);

    // Product Types & Sizes
    Route::resource('product-types', \App\Http\Controllers\Admin\ProductTypeController::class);

    // Events
    Route::resource('events', AdminEventController::class);

    // Paper Types
    Route::resource('paper-types', AdminPaperTypeController::class)->except(['show']);

    // AI Product Generation
    Route::post('products/ai-generate', [AIProductController::class, 'generate'])->name('products.ai-generate');

    // Design Templates
    Route::resource('templates', AdminTemplateController::class);
    Route::post('templates/{template}/toggle', [AdminTemplateController::class, 'toggle'])->name('templates.toggle');
    Route::post('templates/upload-asset', [AdminTemplateController::class, 'uploadAsset'])->name('templates.upload-asset');
});

require __DIR__ . '/auth.php';
