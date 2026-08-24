<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = \App\Models\Order::with(['items.product', 'store'])->latest()->first();

if ($order) {
    echo "Testing Order ID: {$order->id} | Order Number: {$order->order_number}\n";
    $controller = new \App\Http\Controllers\QuickFlowPcController();
    $view = $controller->confirmation($order);
    echo "Rendered View: " . $view->name() . "\n";
} else {
    echo "No order found in DB.\n";
}
