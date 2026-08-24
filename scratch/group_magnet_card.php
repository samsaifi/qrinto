<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$magnetParent = \App\Models\ProductType::whereIn('slug', ['magnets', 'photo-magnets', 'fridge-magnets'])
    ->orWhere('name', 'like', '%magnet%')
    ->parents()
    ->first();

if ($magnetParent) {
    \App\Models\ProductType::where('parent_id', $magnetParent->id)->update([
        'title' => 'Standard size'
    ]);
}

echo "Updated Magnet children titles to 'Standard size':\n";
foreach (\App\Models\ProductType::where('parent_id', $magnetParent->id)->where('is_active', 1)->get() as $child) {
    echo "ID: {$child->id} | Name: {$child->name} | Title: {$child->title} | W: {$child->width} | H: {$child->height} | Price: {$child->price}\n";
}
