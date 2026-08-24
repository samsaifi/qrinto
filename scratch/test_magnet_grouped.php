<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$type = \App\Models\ProductType::where('slug', 'magnets')->first();
$controller = new \App\Http\Controllers\QuickFlowPcController();
$viewCategory = $controller->category($type);
$subTypes = $viewCategory->getData()['subTypes'];

$grouped = $subTypes->groupBy(fn($item) => trim($item->title ?? $item->name));
echo "Group count for Magnets: " . $grouped->count() . "\n";
foreach ($grouped as $title => $items) {
    echo "Group Title: '{$title}' | Items count: " . $items->count() . "\n";
    foreach ($items as $item) {
        echo "  - Item: {$item->name} | Price: \${$item->price} | Size: {$item->width}x{$item->height}\n";
    }
}
