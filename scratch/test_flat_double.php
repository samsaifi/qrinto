<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$type = \App\Models\ProductType::where('slug', 'cards')->first();
$controller = new \App\Http\Controllers\QuickFlowPcController();

echo "--- FLAT (1 page) ---\n";
$viewFlat = $controller->category($type, 'flat', '5x7');
$tplsFlat = $viewFlat->getData()['templates'];
echo "Count: " . $tplsFlat->count() . "\n";
foreach ($tplsFlat->take(3) as $t) {
    echo "ID: {$t->id} | Name: {$t->name} | Pages: {$t->no_of_pages}\n";
}

echo "\n--- FOLDED (4 pages) ---\n";
$viewFolded = $controller->category($type, 'folded', '5x7');
$tplsFolded = $viewFolded->getData()['templates'];
echo "Count: " . $tplsFolded->count() . "\n";
foreach ($tplsFolded->take(3) as $t) {
    echo "ID: {$t->id} | Name: {$t->name} | Pages: {$t->no_of_pages}\n";
}
