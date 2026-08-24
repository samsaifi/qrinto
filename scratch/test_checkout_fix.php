<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find a session_id that has cart items
$cart = \App\Models\Cart::whereHas('items')->latest()->first();

if ($cart) {
    session()->setId($cart->session_id);
    \Illuminate\Support\Facades\Session::setId($cart->session_id);
    
    echo "Found cart with items! Cart ID: {$cart->id} | SessionID: {$cart->session_id} | Items count: " . $cart->items->count() . "\n";
    
    $cartService = app(\App\Services\CartService::class);
    $fetchedCart = $cartService->getCart();
    echo "Fetched cart items count via CartService: " . $fetchedCart->items->count() . "\n";
} else {
    echo "No cart with items found.\n";
}
