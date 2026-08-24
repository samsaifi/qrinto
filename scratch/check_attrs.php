<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pt = \App\Models\ProductType::first();
echo "ProductType attributes: " . json_encode($pt->toArray()) . "\n";

$p = \App\Models\Product::first();
echo "Product attributes: " . json_encode($p->toArray()) . "\n";
