<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$magnetParent = \App\Models\ProductType::whereIn('slug', ['magnets', 'photo-magnets', 'fridge-magnets'])
    ->orWhere('name', 'like', '%magnet%')
    ->parents()
    ->first();

if (!$magnetParent) {
    $magnetParent = \App\Models\ProductType::create([
        'name' => 'Magnets',
        'title' => 'Photo Magnets',
        'slug' => 'magnets',
        'unit' => 'in',
        'is_active' => 1
    ]);
}

// Deactivate any existing children not matching 4x6 or 5x7
\App\Models\ProductType::where('parent_id', $magnetParent->id)->update(['is_active' => 0]);

// Update/Create 4x6 ($3.00)
$size4x6 = \App\Models\ProductType::where('parent_id', $magnetParent->id)->where('width', 4)->first();
if ($size4x6) {
    $size4x6->update(['name' => '4 x 6', 'title' => '4 x 6 in', 'slug' => '4x6', 'width' => 4, 'height' => 6, 'price' => 3.00, 'is_active' => 1]);
} else {
    $size4x6 = \App\Models\ProductType::create(['parent_id' => $magnetParent->id, 'name' => '4 x 6', 'title' => '4 x 6 in', 'slug' => '4x6', 'width' => 4, 'height' => 6, 'price' => 3.00, 'is_active' => 1]);
}

// Update/Create 5x7 ($4.00)
$size5x7 = \App\Models\ProductType::where('parent_id', $magnetParent->id)->where('width', 5)->first();
if ($size5x7) {
    $size5x7->update(['name' => '5 x 7', 'title' => '5 x 7 in', 'slug' => '5x7', 'width' => 5, 'height' => 7, 'price' => 4.00, 'is_active' => 1]);
} else {
    $size5x7 = \App\Models\ProductType::create(['parent_id' => $magnetParent->id, 'name' => '5 x 7', 'title' => '5 x 7 in', 'slug' => '5x7', 'width' => 5, 'height' => 7, 'price' => 4.00, 'is_active' => 1]);
}

echo "Magnet sizes updated:\n";
foreach (\App\Models\ProductType::where('parent_id', $magnetParent->id)->where('is_active', 1)->get() as $child) {
    echo "ID: {$child->id} | Name: {$child->name} | Slug: {$child->slug} | W: {$child->width} | H: {$child->height} | Price: {$child->price}\n";
}
