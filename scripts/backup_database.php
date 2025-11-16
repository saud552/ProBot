#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use RuntimeException;

$config = require APP_BASE_PATH . '/config/database.php';
$databasePath = $config['path'] ?? APP_BASE_PATH . '/storage/database.sqlite';

if ($databasePath === ':memory:') {
    throw new RuntimeException('Cannot back up an in-memory SQLite database.');
}

if (!is_file($databasePath)) {
    throw new RuntimeException(sprintf('Database file not found at %s', $databasePath));
}

$timestamp = date('Ymd_His');
$backupDir = APP_BASE_PATH . '/storage/backups';
if (!is_dir($backupDir) && !mkdir($backupDir, 0775, true) && !is_dir($backupDir)) {
    throw new RuntimeException(sprintf('Unable to create backup directory: %s', $backupDir));
}

$backupFile = sprintf('%s/unified-bot-%s.sqlite', $backupDir, $timestamp);

if (!copy($databasePath, $backupFile)) {
    throw new RuntimeException('Failed to copy SQLite database.');
}

$gzip = getenv('APP_BACKUP_GZIP') !== '0' && function_exists('gzencode');
if ($gzip) {
    $payload = file_get_contents($backupFile);
    if ($payload === false) {
        throw new RuntimeException('Failed to read temporary backup for compression.');
    }

    $compressed = gzencode($payload, 9);
    if ($compressed !== false) {
        $gzFile = $backupFile . '.gz';
        file_put_contents($gzFile, $compressed);
        unlink($backupFile);
        $backupFile = $gzFile;
    }
}

echo sprintf("Backup stored at %s\n", $backupFile);
