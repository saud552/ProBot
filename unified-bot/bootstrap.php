<?php

declare(strict_types=1);

define('APP_BASE_PATH', __DIR__);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $prefixLength = strlen($prefix);

    if (strncmp($class, $prefix, $prefixLength) !== 0) {
        return;
    }

    $relative = substr($class, $prefixLength);
    $file = APP_BASE_PATH . '/src/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

if (!is_dir(APP_BASE_PATH . '/logs')) {
    mkdir(APP_BASE_PATH . '/logs', 0775, true);
}
if (!is_dir(APP_BASE_PATH . '/storage/backups')) {
    mkdir(APP_BASE_PATH . '/storage/backups', 0775, true);
}
