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
use App\Http\Controllers\Admin\KioskController as AdminKioskController;
use App\Http\Controllers\Admin\AIProductController;
use App\Http\Controllers\NoritsuController;
use App\Http\Controllers\QuickFlowController;
use App\Http\Controllers\QuickFlowPcController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartPcController;
use App\Http\Controllers\AIController;
use App\Http\Middleware\DetectDevice;
use App\Http\Controllers\Store\StorePanelController;
use Illuminate\Http\Request;
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
/*
| Standalone Store Panel (separate layout from the NAC admin panel).
| Defined BEFORE the /store/{storeCode} scan wildcard so the static panel
| paths win over it.
*/
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/store', [StorePanelController::class, 'orders'])->name('storepanel.home');
    Route::get('/store/orders', [StorePanelController::class, 'orders'])->name('storepanel.orders');
    Route::get('/store/qr', [StorePanelController::class, 'qr'])->name('storepanel.qr');
    Route::get('/store/trays', [StorePanelController::class, 'trays'])->name('storepanel.trays');
    Route::post('/store/trays', [StorePanelController::class, 'saveTrays'])->name('storepanel.trays.save');
    // QZ Tray print bridge: hand the browser the payload (tray + printer + PDF).
    Route::get('/store/orders/{order}/prepare-print', [StorePanelController::class, 'preparePrint'])
        ->name('storepanel.orders.preparePrint');
    // Print outcome log — success + failure rows land here after QZ Tray runs.
    Route::post('/store/orders/{order}/log-print', [StorePanelController::class, 'logPrint'])
        ->name('storepanel.orders.logPrint');
    // Store-scoped print-log listing (read-only, paginated).
    Route::get('/store/print-logs', [StorePanelController::class, 'printLogs'])
        ->name('storepanel.printLogs');
    // Store-scoped kiosk logs listing (read-only, paginated).
    Route::get('/store/kiosks', [StorePanelController::class, 'kioskLogs'])
        ->name('storepanel.kioskLogs');
    // Store panel catalog routes: /store/products, /store/categories, etc.
    Route::resource('/store/products', AdminProductController::class, ['as' => 'storepanel_cat']);
    Route::post('/store/products/bulk-delete', [AdminProductController::class, 'bulkDelete'])->name('storepanel_cat.products.bulkDelete');
    Route::post('/store/products/{product}/options', [AdminProductController::class, 'storeOptionGroup'])->name('storepanel_cat.products.options.store');
    Route::post('/store/option-groups/{optionGroup}/values', [AdminProductController::class, 'storeOptionValue'])->name('storepanel_cat.options.values.store');
    Route::delete('/store/option-groups/{optionGroup}', [AdminProductController::class, 'deleteOptionGroup'])->name('storepanel_cat.options.destroy');
    Route::delete('/store/option-values/{optionValue}', [AdminProductController::class, 'deleteOptionValue'])->name('storepanel_cat.optionValues.destroy');
    Route::delete('/store/product-images/{imageId}', [AdminProductController::class, 'deleteImage'])->name('storepanel_cat.productImages.destroy');
    Route::get('/store/products/{product}/mask', [AdminProductController::class, 'maskEditor'])->name('storepanel_cat.products.mask');
    Route::post('/store/products/{product}/mask', [AdminProductController::class, 'saveMask'])->name('storepanel_cat.products.mask.save');

    Route::resource('/store/categories', AdminCategoryController::class, ['as' => 'storepanel_cat']);
    Route::resource('/store/product-types', \App\Http\Controllers\Admin\ProductTypeController::class, ['as' => 'storepanel_cat']);
    Route::resource('/store/templates', AdminTemplateController::class, ['as' => 'storepanel_cat']);
    Route::resource('/store/coupons', AdminCouponController::class, ['as' => 'storepanel_cat']);
    Route::resource('/store/events', AdminEventController::class, ['as' => 'storepanel_cat']);

    // Persist "I already downloaded QZ Tray" for the current user, so the
    // onboarding modal on /store/orders is only shown once per user.
    Route::post('/store/qz-tray/confirm', [StorePanelController::class, 'confirmQzTray'])
        ->name('storepanel.qz.confirm');
});

Route::get('/store/{storeCode}', function ($storeCode) {
    return app(\App\Http\Controllers\StoreQrController::class)->scan($storeCode);
})->name('store.scan');

Route::get('/store-admin/{storeCode}/scan', function ($storeCode) {
    return app(\App\Http\Controllers\StoreQrController::class)->scan($storeCode);
})->name('store.qr');


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
| Canonical Specification Routes (Qrinto Dev Instructions Section 3)
|--------------------------------------------------------------------------
*/
// Device-aware controller resolver: small/mobile screens get the mobile flow
// (resources/views/quick-flow), everything else gets the PC flow
// (resources/views/quick-flow-pc). Both controllers share the same method
// signatures, so the canonical routes simply dispatch to whichever one matches
// the current device.
// QuickFlowController is device-aware (it selects the quick-flow vs
// quick-flow-pc view folder from the request), so a single instance renders
// the correct experience for the canonical URLs.
$flowController = fn() => new QuickFlowController;

// Store Locator
Route::get('/find-store', fn(Request $request) => $flowController()->findStore($request))->name('canonical.find-store');

// Products & Categories
Route::get('/products', fn() => $flowController()->index())->name('canonical.products');
Route::get('/cards', fn(Request $request) => $flowController()->cards($request))->name('canonical.cards');
Route::get('/cards/{title}/{size}/templates', fn($title, $size) => $flowController()->category('cards', $title, $size))->name('canonical.cards.templates');
Route::get('/cards/{title}/{size}/design/{template?}', fn($title, $size, $template = null) => $flowController()->customize('cards', $title, $size, $template))->name('canonical.cards.design');
Route::get('/magnets/{size}/design/{template?}', fn($size, $template = null) => $flowController()->customize('magnets', null, $size, $template))->name('canonical.magnets.design');

// Order & Confirmation
Route::get('/order', fn() => $flowController()->cartCheckout())->name('canonical.order');
Route::get('/order/{order}', fn(\App\Models\Order $order) => $flowController()->confirmation($order))->name('canonical.order.confirmation');

// Tracking
Route::get('/track', fn() => $flowController()->trackForm())->name('canonical.track.form');
Route::post('/track', fn(Request $request) => $flowController()->track($request))->name('canonical.track');
Route::get('/track/{orderNumber}', fn($orderNumber) => $flowController()->trackOrder($orderNumber))->name('canonical.track.order');

// Direct Print on 931BL
// Direct print on the customer's own 931BL (spec §6). Windows/desktop flow.
Route::get('/print', [\App\Http\Controllers\LocalPrintController::class, 'start'])->name('localprint.start');
Route::get('/print/design', [\App\Http\Controllers\LocalPrintController::class, 'design'])->name('localprint.design');
Route::get('/print/pdf', [\App\Http\Controllers\LocalPrintController::class, 'pdf'])->name('localprint.pdf');
Route::post('/print/pdf', [\App\Http\Controllers\LocalPrintController::class, 'upload'])->name('localprint.upload');
Route::get('/print/pdf/check', [\App\Http\Controllers\LocalPrintController::class, 'check'])->name('localprint.check');
Route::post('/print/pdf/print', [\App\Http\Controllers\LocalPrintController::class, 'doPrint'])->name('localprint.print');
Route::get('/print/sent', [\App\Http\Controllers\LocalPrintController::class, 'sent'])->name('localprint.sent');
Route::get('/print/setup', [\App\Http\Controllers\LocalPrintController::class, 'setup'])->name('localprint.setup');
Route::get('/print/trays', [\App\Http\Controllers\LocalPrintController::class, 'trayScreen'])->name('localprint.trays');
Route::post('/print/trays', [\App\Http\Controllers\LocalPrintController::class, 'saveTrays'])->name('localprint.trays.save');

// Store Panel Standalone & Staff Queue
// Store panel lives at /store/* — registered near the top of this file,
// ahead of the /store/{storeCode} scan wildcard.

/*
|--------------------------------------------------------------------------
| Public & Customer Flow Routes
|--------------------------------------------------------------------------
*/

$registerFlowRoutes = function (string $controller, string $namePrefix, string $cartController = CartController::class) {
    Route::name("$namePrefix.")->group(function () use ($controller, $namePrefix, $cartController) {
        // Static Routes (Defined FIRST so wildcards do not intercept them)
        Route::get('/find-store', [$controller, 'findStore'])->name('find-store');
        Route::get('/nearest-store', [$controller, 'getNearestStore'])->name('nearest-store');
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

        Route::get('/local-print', [\App\Http\Controllers\LocalPrintController::class, 'start'])->name('qrinto');
        Route::get('/custom-print', fn() => redirect()->route("$namePrefix.qrinto"));
        Route::post('/local-print/checkout/cash', [$controller, 'qrintoCheckoutCash'])->name('qrinto.checkout.cash');
        Route::post('/local-print/paypal/create', [$controller, 'qrintoPaypalCreate'])->name('qrinto.paypal.create');
        Route::post('/local-print/paypal/capture', [$controller, 'qrintoPaypalCapture'])->name('qrinto.paypal.capture');

        Route::get('/confirmation/{order}', [$controller, 'confirmation'])->name('confirmation');
        Route::get('/print/{order}', [$controller, 'printDesign'])->name('print');
        Route::get('/print/{order}/pdf', [$controller, 'viewPdf'])->name('print.pdf');

        Route::get('/track', [$controller, 'trackForm'])->name('track.form');
        Route::post('/track', [$controller, 'track'])->name('track');
        Route::get('/track/{orderNumber}', [$controller, 'trackOrder'])->name('track.order');
        Route::post('/subscribe', [$controller, 'subscribe'])->name('subscribe');

        Route::post('/upload', [$controller, 'upload'])->name('upload');
        Route::post('/upload-composite', [$controller, 'uploadComposite'])->name('upload_composite');
        Route::post('/checkout', [$controller, 'checkout'])->name('checkout');
        Route::post('/paypal/create', [$controller, 'createPaypalOrder'])->name('paypal.create');
        Route::post('/paypal/capture', [$controller, 'capturePaypalOrder'])->name('paypal.capture');
        Route::post('/checkout/cash', [$controller, 'checkoutCash'])->name('checkout.cash');

        Route::get('/product/{product:slug}', [$controller, 'product'])->name('product');
        Route::get('/customize/{product:slug}', [$controller, 'customize'])->name('customize');
        Route::get('/thumbnails/generate', [\App\Http\Controllers\UtilityController::class, 'generateThumbnails'])->name('utility.thumbnails');

        // Dynamic Wildcard Patterns (Defined LAST)
        Route::get('/{type}/{title}/{size}/templates', [$controller, 'category'])->name('templates');
        Route::get('/{type}/{title}/{size}/design/{template?}', [$controller, 'customize'])->name('customize.design');
        Route::get('/{type}', [$controller, 'category'])->name('category');
    });
};

Route::get('/', [QuickFlowController::class, 'index'])
    ->middleware(DetectDevice::class)
    ->name('flow.index');

Route::get('/home', fn() => redirect()->route('flow.index'))->name('home');

require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Admin / Store / Staff Routes (Defined BEFORE wildcard flow routes)
|--------------------------------------------------------------------------
*/
$registerAdminRoutes = function ($prefix, $namePrefix) {
    Route::prefix($prefix)->name($namePrefix)->middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('roleDashboard');
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
        Route::post('orders/{order}/undo-status', [AdminOrderController::class, 'undoStatus'])->name('orders.undoStatus');
        Route::post('orders/{order}/advance', [AdminOrderController::class, 'advanceStatus'])->name('orders.advance');
        Route::get('orders/{order}/download-pdf', [AdminOrderController::class, 'downloadPdf'])->name('orders.downloadPdf');
        Route::post('orders/{order}/duplicate', [AdminOrderController::class, 'duplicate'])->name('orders.duplicate');

        // Order Printing
        Route::post('orders/{order}/print', [OrderPrintController::class, 'sendPrint'])->name('orders.print');
        Route::post('orders/{order}/print-direct', [OrderPrintController::class, 'sendDirectPrint'])->name('orders.print.direct');
        Route::get('orders/{order}/print-page/{item?}', [OrderPrintController::class, 'printPage'])->name('orders.print.page');

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

        // Kiosk Management
        Route::resource('kiosks', AdminKioskController::class);

        // AI Product Generation
        Route::post('products/ai-generate', [AIProductController::class, 'generate'])->name('products.ai-generate');

        // Design Templates
        Route::resource('templates', AdminTemplateController::class);
        Route::post('templates/{template}/toggle', [AdminTemplateController::class, 'toggle'])->name('templates.toggle');
        Route::post('templates/upload-asset', [AdminTemplateController::class, 'uploadAsset'])->name('templates.upload-asset');

        // Documentations
        Route::get('docs', [\App\Http\Controllers\Admin\DocumentationController::class, 'index'])->name('docs.index');
        Route::get('docs/manage', [\App\Http\Controllers\Admin\DocumentationController::class, 'manage'])->name('docs.manage');
        Route::get('docs/create', [\App\Http\Controllers\Admin\DocumentationController::class, 'create'])->name('docs.create');
        Route::post('docs', [\App\Http\Controllers\Admin\DocumentationController::class, 'store'])->name('docs.store');
        Route::get('docs/{slug}', [\App\Http\Controllers\Admin\DocumentationController::class, 'show'])->name('docs.show');
        Route::get('docs/{slug}/edit', [\App\Http\Controllers\Admin\DocumentationController::class, 'edit'])->name('docs.edit');
        Route::put('docs/{slug}', [\App\Http\Controllers\Admin\DocumentationController::class, 'update'])->name('docs.update');
        Route::delete('docs/{slug}', [\App\Http\Controllers\Admin\DocumentationController::class, 'destroy'])->name('docs.destroy');
        Route::post('docs/{slug}/toggle', [\App\Http\Controllers\Admin\DocumentationController::class, 'toggle'])->name('docs.toggle');
    });
};

$registerAdminRoutes('admin', 'admin.');
$registerAdminRoutes('store-admin', 'store.');
$registerAdminRoutes('staff', 'staff.');


// NAC admin only: view-only print-logs (order_print_logs). Index + detail.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('print-logs', [\App\Http\Controllers\Admin\PrintLogController::class, 'index'])
        ->name('print-logs.index');

    // Manual trigger for the weekly analytics report. Optional ?to= override,
    // ?from=YYYY-MM-DD & ?until=YYYY-MM-DD for a custom window.
    Route::get('analytics/weekly-report/test', function (\Illuminate\Http\Request $request) {
        $service = ($request->filled('from') && $request->filled('until'))
            ? \App\Services\WeeklyAnalyticsService::forRange($request->from, $request->until)
            : \App\Services\WeeklyAnalyticsService::previousWeek();

        $recipients = $request->filled('to')
            ? array_filter(array_map('trim', explode(',', $request->to)))
            : (array) config('analytics.recipients', []);

        if (empty($recipients)) {
            return response()->json(['ok' => false, 'error' => 'No recipients. Set ANALYTICS_RECIPIENTS in .env or pass ?to=you@example.com'], 422);
        }

        $cc  = $request->filled('cc')  ? array_filter(array_map('trim', explode(',', $request->cc)))  : (array) config('analytics.cc',  []);
        $bcc = $request->filled('bcc') ? array_filter(array_map('trim', explode(',', $request->bcc))) : (array) config('analytics.bcc', []);

        try {
            // Force synchronous send even if the mailable were still ShouldQueue.
            $pending = \Illuminate\Support\Facades\Mail::mailer(config('mail.default'))->to($recipients);
            if (!empty($cc))  $pending->cc($cc);
            if (!empty($bcc)) $pending->bcc($bcc);
            $pending->sendNow(new \App\Mail\WeeklyAnalyticsReport($service));
        } catch (\Throwable $e) {
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage(),
                'trace' => collect($e->getTrace())->take(6)->map(fn($f) => ($f['file'] ?? '?') . ':' . ($f['line'] ?? '?'))->all(),
                'mail'  => [
                    'mailer' => config('mail.default'),
                    'host'   => config('mail.mailers.smtp.host'),
                    'port'   => config('mail.mailers.smtp.port'),
                    'from'   => config('mail.from.address'),
                ],
            ], 500);
        }

        return response()->json([
            'ok'         => true,
            'period'     => $service->period()['label'],
            'recipients' => $recipients,
            'cc'         => $cc,
            'bcc'        => $bcc,
            'mailer'     => config('mail.default'),
            'from'       => config('mail.from.address'),
            'note'       => 'Sent synchronously via ' . config('mail.default') . '. Check inbox and spam.',
        ]);
    })->name('analytics.weekly-report.test');
    Route::get('print-logs/export', [\App\Http\Controllers\Admin\PrintLogController::class, 'export'])
        ->name('print-logs.export');
    Route::get('print-logs/{orderPrintLog}', [\App\Http\Controllers\Admin\PrintLogController::class, 'show'])
        ->name('print-logs.show');
});

Route::get('/store-admin/dashboard', [AdminDashboardController::class, 'index'])->middleware(['auth', 'admin'])->name('store.dashboard');
Route::get('/store-admin/trays', fn() => response('Trays feature coming soon', 200))->middleware(['auth', 'admin'])->name('store.trays');
Route::get('/staff/dashboard', [AdminDashboardController::class, 'index'])->middleware(['auth', 'admin'])->name('staff.dashboard');

// Single device-aware consumer flow. QuickFlowController now picks the view
// folder (quick-flow vs quick-flow-pc) from the request device, so one route
// set named 'flow.*' serves both mobile and desktop.
$registerFlowRoutes(QuickFlowController::class, 'flow');
