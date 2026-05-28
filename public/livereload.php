<?php
// Lightweight live-reload endpoint — returns latest modification time
header('Content-Type: application/json; charset=utf-8');

$paths = [
    __DIR__ . '/../server',
    __DIR__ . '/../server/views',
    __DIR__ . '/../server/app',
    __DIR__ . '/../views',
    __DIR__ . '/../public',
];

function max_mtime(array $paths){
    $max = 0;
    foreach ($paths as $p){
        if (!file_exists($p)) continue;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($p, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f){
            $fn = $f->getPathname();
            // skip vendor to reduce noise
            if (strpos($fn, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false) continue;
            $mt = $f->getMTime();
            if ($mt > $max) $max = $mt;
        }
    }
    return $max;
}

$mtime = max_mtime($paths);
echo json_encode(['mtime' => $mtime]);
