<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$testTitles = ['Flat', 'flat', 'Flat - double', 'Flat, double-sided', 'flat-double', 'flat double', 'Folded', 'folded'];

foreach ($testTitles as $t) {
    $targetTitle = strtolower($t);
    $pages = null;
    if (str_contains($targetTitle, 'double')) {
        $pages = 2;
    } elseif (str_contains($targetTitle, 'folded')) {
        $pages = 4;
    } elseif (str_contains($targetTitle, 'flat')) {
        $pages = 1;
    }
    echo "Title: '{$t}' => no_of_pages: {$pages}\n";
}
