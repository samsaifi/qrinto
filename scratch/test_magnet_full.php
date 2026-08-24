<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$type = \App\Models\ProductType::where('slug', 'magnets')->first();

echo "--- MAGNET CATEGORY PAGE ---\n";
$controller = new \App\Http\Controllers\QuickFlowPcController();
$viewCategory = $controller->category($type);
$subTypes = $viewCategory->getData()['subTypes'];
foreach ($subTypes as $st) {
    echo "Size: {$st->name} | Price: \${$st->price} | W: {$st->width} | H: {$st->height}\n";
}

echo "\n--- MAGNET 4x6 DESIGN ---\n";
$view4x6 = $controller->customize('magnets', null, '4x6');
$d4x6 = $view4x6->getData();
echo "UnitPrice: \${$d4x6['unitPrice']} | Ratio: {$d4x6['flowData']['size_width']}x{$d4x6['flowData']['size_height']} | Name: {$d4x6['flowData']['size_name']}\n";

echo "\n--- MAGNET 5x7 DESIGN ---\n";
$view5x7 = $controller->customize('magnets', null, '5x7');
$d5x7 = $view5x7->getData();
echo "UnitPrice: \${$d5x7['unitPrice']} | Ratio: {$d5x7['flowData']['size_width']}x{$d5x7['flowData']['size_height']} | Name: {$d5x7['flowData']['size_name']}\n";
