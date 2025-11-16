<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use PDO;
use RuntimeException;

final class SchemaManager
{
    public static function ensure(PDO $pdo, string $schemaPath, ?string $seedPath = null): void
    {
        if (!self::needsMigration($pdo)) {
            return;
        }

        self::runFile($pdo, $schemaPath);

        if ($seedPath && is_file($seedPath)) {
            self::runFile($pdo, $seedPath);
        }
    }

    private static function needsMigration(PDO $pdo): bool
    {
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'users' LIMIT 1");
        return $stmt === false || $stmt->fetchColumn() === false;
    }

    private static function runFile(PDO $pdo, string $path): void
    {
        if (!is_file($path)) {
            throw new RuntimeException(sprintf('Database bootstrap file not found at %s', $path));
        }

        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException(sprintf('Unable to read SQL file %s', $path));
        }

        $pdo->exec($sql);
    }
}
