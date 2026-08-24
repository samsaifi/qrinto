<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

session(['active_store_id' => 31]);

$controller = new \App\Http\Controllers\QuickFlowPcController();
$view = $controller->qrinto();

echo "Rendered View Name: " . $view->name() . "\n";
