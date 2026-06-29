<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::transaction(function () {
    $order = App\Models\Order::create([
        'user_id' => 1,
        'order_number' => 'TEST-ORD',
        'status' => 'pending',
        'subtotal' => 10,
        'total' => 10,
        'shipping_address' => [],
    ]);
    
    $order->items()->create([
        'product_id' => null,
        'product_name' => 'Qrinto',
        'quantity' => 1,
        'unit_price' => 10,
        'total_price' => 10,
    ]);
    
    echo "Order items count: " . $order->items()->count() . "\n";
});
