<?php
// Lightweight live-reload endpoint
header('Content-Type: application/json; charset=utf-8');

$paths = [
    __DIR__ . '/../../views',
    __DIR__ . '/../../app',
    __DIR__ . '/../../src',
    __DIR__ . '/..',
];

function max_mtime(array $paths){
    $max = 0;
    foreach ($paths as $p){
        if (!file_exists($p)) continue;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($p, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f){
            $fn = $f->getPathname();
            if (strpos($fn, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false) continue;
            $mt = $f->getMTime();
            if ($mt > $max) $max = $mt;
        }
    }
    return $max;
}

$mtime = max_mtime($paths);
echo json_encode(['mtime' => $mtime]);
