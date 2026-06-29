<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Category;

echo "PRODUCTS BY CATEGORY:\n";
foreach (Category::all() as $c) {
    $count = Product::where('category_id', $c->id)->count();
    $activeCount = Product::where('category_id', $c->id)->active()->count();
    echo "- Category [{$c->id}] {$c->name}: TOTAL={$count}, ACTIVE={$activeCount}\n";
}
