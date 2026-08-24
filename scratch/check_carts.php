<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$carts = \App\Models\Cart::with('items')->get();
echo "Total Carts in DB: " . $carts->count() . "\n";

foreach ($carts as $c) {
    echo "Cart ID: {$c->id} | UserID: {$c->user_id} | SessionID: {$c->session_id} | Items Count: " . $c->items->count() . "\n";
    foreach ($c->items as $item) {
        echo "  - Item ID: {$item->id} | ProductID: {$item->product_id} | Qty: {$item->quantity} | Price: {$item->unit_price}\n";
    }
}
