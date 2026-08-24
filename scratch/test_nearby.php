<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test coordinates (e.g. user in New York: 40.7128, -74.0060)
$lat = 40.7128;
$lon = -74.0060;

$stores = \App\Models\Store::where('is_active', true)
    ->where(function($q) {
        $q->where('is_test', false)->orWhereNull('is_test');
    })
    ->whereNotNull('lat')
    ->whereNotNull('lon')
    ->selectRaw("*,
    ( 3959 * acos( LEAST(1.0, GREATEST(-1.0, cos( radians(?) ) *
    cos( radians( lat ) ) *
    cos( radians( lon ) - radians(?) ) +
    sin( radians(?) ) *
    sin( radians( lat ) ) )) )
    ) AS distance", [$lat, $lon, $lat])
    ->orderBy('distance')
    ->get();

echo "Found stores count: " . $stores->count() . "\n";
foreach ($stores as $s) {
    echo "Store: {$s->store_name} | City: {$s->city} | Dist: {$s->distance} mi\n";
}
