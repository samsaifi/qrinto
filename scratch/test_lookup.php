<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$templateSlug = 'my-love-for-you-only-grows';

$product = \App\Models\Product::where('slug', $templateSlug)->first();
echo "Found product: " . ($product ? $product->name . " (ID: {$product->id})" : "None") . "\n";

$template = \App\Models\Template::where('slug', $templateSlug)->first();
echo "Found template: " . ($template ? $template->name . " (ID: {$template->id})" : "None") . "\n";
