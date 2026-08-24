<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$type21 = \App\Models\ProductType::find(21);
echo "ProductType 21: " . json_encode($type21) . "\n";

$cat21 = \App\Models\Category::find(21);
echo "Category 21: " . json_encode($cat21) . "\n";
