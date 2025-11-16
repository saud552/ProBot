<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use PDO;
use PDOException;
use RuntimeException;

class Connection
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $path = $config['path'] ?? APP_BASE_PATH . '/storage/database.sqlite';
        if ($path !== ':memory:') {
            $directory = dirname($path);
            if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new RuntimeException(sprintf('Unable to create database directory: %s', $directory));
            }

            if (!file_exists($path)) {
                if (false === touch($path)) {
                    throw new RuntimeException(sprintf('Unable to create SQLite database file at %s', $path));
                }
            }
        }

        $dsn = 'sqlite:' . $path;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $timeoutMs = (int)($config['busy_timeout'] ?? 0);
        if ($timeoutMs > 0) {
            $options[PDO::ATTR_TIMEOUT] = max(1, (int)ceil($timeoutMs / 1000));
        }

        try {
            $this->pdo = new PDO($dsn, null, null, $options);
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }

        $this->initializePragmas($config);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function initializePragmas(array $config): void
    {
        if (($config['foreign_keys'] ?? true) === true) {
            $this->pdo->exec('PRAGMA foreign_keys = ON');
        }

        $timeoutMs = (int)($config['busy_timeout'] ?? 0);
        if ($timeoutMs > 0) {
            $this->pdo->exec(sprintf('PRAGMA busy_timeout = %d', $timeoutMs));
        }

        $journalMode = strtoupper((string)($config['journal_mode'] ?? ''));
        if ($journalMode !== '') {
            $allowed = ['DELETE', 'TRUNCATE', 'PERSIST', 'MEMORY', 'WAL', 'OFF'];
            if (!in_array($journalMode, $allowed, true)) {
                $journalMode = 'WAL';
            }
            $this->pdo->exec(sprintf('PRAGMA journal_mode = %s', $journalMode));
        }

        $this->pdo->exec('PRAGMA synchronous = NORMAL');
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
