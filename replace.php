<?php
$dir = new RecursiveDirectoryIterator(__DIR__ . '/resources/views');
$iterator = new RecursiveIteratorIterator($dir);
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $modified = false;
        
        if (strpos($content, '₹') !== false) {
            $content = str_replace('₹', '$', $content);
            $modified = true;
        }
        
        if (strpos($content, 'en-IN') !== false) {
            $content = str_replace('en-IN', 'en-US', $content);
            $modified = true;
        }

        if ($modified) {
            file_put_contents($file->getPathname(), $content);
            echo "Updated " . $file->getPathname() . "\n";
        }
    }
}
echo "Done.\n";
