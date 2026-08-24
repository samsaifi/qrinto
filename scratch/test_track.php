<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = \App\Models\Order::latest()->first();

// Test 1: Phone lookup
$phone = $order->guest_phone ?? '7532976564';
$requestPhone = \Illuminate\Http\Request::create('/track', 'POST', [
    'order_number' => $order->order_number,
    'contact' => $phone
]);
$controller = new \App\Http\Controllers\QuickFlowPcController();
$responsePhone = $controller->track($requestPhone);
echo "Phone lookup status: " . $responsePhone->getStatusCode() . " -> " . $responsePhone->getTargetUrl() . "\n";

// Test 2: Invalid contact lookup
$requestInvalid = \Illuminate\Http\Request::create('/track', 'POST', [
    'order_number' => $order->order_number,
    'contact' => 'invalid@wrongemail.com'
]);
$responseInvalid = $controller->track($requestInvalid);
echo "Invalid contact lookup status: " . $responseInvalid->getStatusCode() . " -> " . $responseInvalid->getTargetUrl() . "\n";
