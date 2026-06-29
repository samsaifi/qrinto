<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$categories = \App\Models\Category::all();
foreach($categories as $c) {
    echo $c->id . ' - ' . $c->name . "\n";
    $products = $c->products ?? []; // if relation exists
    foreach($products as $p) {
        $sizes = $p->sizes ?? $p->options ?: []; 
        // Or check product options
    }
}
$options = \App\Models\ProductOption::all() ?? [];
echo "Options:\n";
foreach($options as $o) {
    echo $o->id . ' - ' . $o->name . ' - ' . $o->type . ' - ' . $o->value . "\n";
}
