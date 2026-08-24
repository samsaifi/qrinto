<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$templates = \App\Models\Template::all();
echo "Templates count: " . $templates->count() . "\n";
foreach ($templates as $t) {
    echo "ID: {$t->id} | Name: {$t->name} | Slug: {$t->slug} | CatID: {$t->category_id}\n";
}

$products = \App\Models\Product::all();
echo "\nProducts count: " . $products->count() . "\n";
foreach ($products as $p) {
    echo "ID: {$p->id} | Name: {$p->name} | Slug: {$p->slug} | CatID: {$p->category_id}\n";
}
