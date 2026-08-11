<?php
$src = __DIR__ . '/resources/views/quick-flow-pc';
$dst = __DIR__ . '/resources/views/quick-flow';

function recursiveCopy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst, 0777, true);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recursiveCopy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

if (!is_dir($dst)) {
    recursiveCopy($src, $dst);
    echo "Successfully created resources/views/quick-flow directory and copied all templates!\n";
} else {
    echo "resources/views/quick-flow already exists.\n";
}
