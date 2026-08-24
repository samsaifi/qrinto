<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$magnets = \App\Models\ProductType::whereIn('slug', ['magnets', 'photo-magnets', 'fridge-magnets'])
    ->orWhere('name', 'like', '%magnet%')
    ->get();

echo "Magnet ProductTypes:\n";
foreach ($magnets as $m) {
    echo "ID: {$m->id} | Parent: {$m->parent_id} | Name: {$m->name} | Slug: {$m->slug} | W: {$m->width} | H: {$m->height} | Price: {$m->price} | ActivePrice: {$m->active_price}\n";
    $children = \App\Models\ProductType::where('parent_id', $m->id)->get();
    foreach ($children as $c) {
        echo "  - Child ID: {$c->id} | Name: {$c->name} | Title: {$c->title} | Slug: {$c->slug} | W: {$c->width} | H: {$c->height} | Price: {$c->price} | ActivePrice: {$c->active_price}\n";
    }
}
