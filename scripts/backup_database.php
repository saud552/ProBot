#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Infrastructure\Database\Connection;
use PDO;
use RuntimeException;

$config = require APP_BASE_PATH . '/config/database.php';
$connection = new Connection($config);
$pdo = $connection->pdo();

$timestamp = date('Ymd_His');
$backupDir = APP_BASE_PATH . '/storage/backups';
$backupFile = sprintf('%s/unified-bot-%s.sql', $backupDir, $timestamp);

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
if (!$tables) {
    throw new RuntimeException('No tables found to back up.');
}

$buffer = [];
$buffer[] = '-- Unified bot backup';
$buffer[] = '-- Generated at ' . date('c');
$buffer[] = 'SET FOREIGN_KEY_CHECKS = 0;';

foreach ($tables as $table) {
    $stmt = $pdo->query('SHOW CREATE TABLE `' . $table . '`');
    $createRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$createRow) {
        throw new RuntimeException(sprintf('Unable to read definition for table %s', $table));
    }

    $createSql = $createRow['Create Table'] ?? $createRow[array_key_first($createRow)];
    $buffer[] = sprintf('DROP TABLE IF EXISTS `%s`;', $table);
    $buffer[] = $createSql . ';';

    $rows = $pdo->query('SELECT * FROM `' . $table . '`')->fetchAll(PDO::FETCH_ASSOC);
    if ($rows === []) {
        continue;
    }

    $columns = array_map(
        fn (string $column): string => sprintf('`%s`', str_replace('`', '``', $column)),
        array_keys($rows[0])
    );

    $chunks = array_chunk($rows, 250);
    foreach ($chunks as $chunk) {
        $values = [];
        foreach ($chunk as $row) {
            $valueSet = [];
            foreach ($row as $value) {
                if ($value === null) {
                    $valueSet[] = 'NULL';
                    continue;
                }

                if (is_int($value) || is_float($value)) {
                    $valueSet[] = (string)$value;
                    continue;
                }

                $valueSet[] = $pdo->quote((string)$value);
            }
            $values[] = '(' . implode(', ', $valueSet) . ')';
        }

        $buffer[] = sprintf(
            'INSERT INTO `%s` (%s) VALUES %s;',
            $table,
            implode(', ', $columns),
            implode(', ', $values)
        );
    }
}

$buffer[] = 'SET FOREIGN_KEY_CHECKS = 1;';

$payload = implode(PHP_EOL . PHP_EOL, $buffer) . PHP_EOL;
file_put_contents($backupFile, $payload);

$gzip = getenv('APP_BACKUP_GZIP') !== '0' && function_exists('gzencode');
if ($gzip) {
    $compressed = gzencode($payload, 9);
    if ($compressed !== false) {
        $gzFile = $backupFile . '.gz';
        file_put_contents($gzFile, $compressed);
        unlink($backupFile);
        $backupFile = $gzFile;
    }
}

echo sprintf("Backup stored at %s\n", $backupFile);
