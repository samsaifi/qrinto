<?php
echo "VIEWS DIRECTORY:\n";
print_r(scandir(__DIR__ . '/resources/views'));

echo "\nSEARCH FOR QUICK-FLOW IN PROJECT:\n";
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/resources/views'));
foreach ($iterator as $file) {
    if ($file->isDir() && strpos($file->getFilename(), 'quick-flow') !== false) {
        echo "Found dir: " . $file->getPathname() . "\n";
    }
}
