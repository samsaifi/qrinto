<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Stores in DB ---\n";
$stores = \App\Models\Store::all();
foreach ($stores as $s) {
    echo "ID: {$s->id} | Name: {$s->store_name} | Active: {$s->is_active} | Test: {$s->is_test} | Lat: '{$s->lat}' | Lon: '{$s->lon}' | City: {$s->city}\n";
}
