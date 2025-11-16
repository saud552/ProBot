<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

spl_autoload_register(function (string $class): void {
    $prefix = 'Numbers\\';
    $prefixLength = strlen($prefix);

    if (strncmp($class, $prefix, $prefixLength) !== 0) {
        return;
    }

    $relativeClass = substr($class, $prefixLength);
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

if (!is_dir(BASE_PATH . '/logs')) {
    mkdir(BASE_PATH . '/logs', 0775, true);
}
if (!is_dir(BASE_PATH . '/storage')) {
    mkdir(BASE_PATH . '/storage', 0775, true);
}
