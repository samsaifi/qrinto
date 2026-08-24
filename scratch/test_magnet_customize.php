<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new \App\Http\Controllers\QuickFlowPcController();

echo "--- Testing Magnet 4x6 ---\n";
$view4x6 = $controller->customize('magnets', null, '4x6');
$data4x6 = $view4x6->getData();
echo "UnitPrice: " . $data4x6['unitPrice'] . "\n";
echo "FlowData size_name: " . ($data4x6['flowData']['size_name'] ?? 'N/A') . "\n";
echo "FlowData size_price: " . ($data4x6['flowData']['size_price'] ?? 'N/A') . "\n";
echo "FlowData size_width: " . ($data4x6['flowData']['size_width'] ?? 'N/A') . "\n";
echo "FlowData size_height: " . ($data4x6['flowData']['size_height'] ?? 'N/A') . "\n";

echo "\n--- Testing Magnet 5x7 ---\n";
$view5x7 = $controller->customize('magnets', null, '5x7');
$data5x7 = $view5x7->getData();
echo "UnitPrice: " . $data5x7['unitPrice'] . "\n";
echo "FlowData size_name: " . ($data5x7['flowData']['size_name'] ?? 'N/A') . "\n";
echo "FlowData size_price: " . ($data5x7['flowData']['size_price'] ?? 'N/A') . "\n";
echo "FlowData size_width: " . ($data5x7['flowData']['size_width'] ?? 'N/A') . "\n";
echo "FlowData size_height: " . ($data5x7['flowData']['size_height'] ?? 'N/A') . "\n";
