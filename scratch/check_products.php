<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Products in DB ---\n";
$products = \App\Models\Product::where('is_active', 1)->get();
foreach ($products as $p) {
    echo "ID: {$p->id} | Name: {$p->name} | Slug: {$p->slug} | TypeID: {$p->product_type_id}\n";
}
