<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Templates in DB ---\n";
$templates = \App\Models\Template::where('is_active', 1)->get();
foreach ($templates as $t) {
    echo "ID: {$t->id} | Name: {$t->name} | Slug: {$t->slug} | Category: {$t->category_id} | Store: {$t->product_store}\n";
}

echo "\n--- Product Types ---\n";
$pts = \App\Models\ProductType::all();
foreach ($pts as $pt) {
    echo "ID: {$pt->id} | Name: {$pt->name} | Title: {$pt->title} | W: {$pt->width} | H: {$pt->height} | Slug: {$pt->slug}\n";
}
