<?php

declare(strict_types=1);

namespace Numbers\Storage;

use PDO;

class DatabaseStorage
{
    private PDO $pdo;

    /**
     * @var array<string, string>
     */
    private array $fallbackPaths;

    public function __construct(PDO $pdo, array $fallbackPaths = [])
    {
        $this->pdo = $pdo;
        $this->fallbackPaths = $fallbackPaths;
        $this->createTable();
    }

    public function load(string $key, $default = [])
    {
        $stmt = $this->pdo->prepare('SELECT value FROM kv_store WHERE key = :key LIMIT 1');
        $stmt->execute([':key' => $key]);
        $row = $stmt->fetchColumn();

        if ($row !== false) {
            $decoded = json_decode((string)$row, true);
            return $decoded === null && json_last_error() !== JSON_ERROR_NONE ? $default : $decoded;
        }

        if (isset($this->fallbackPaths[$key]) && is_file($this->fallbackPaths[$key])) {
            $content = file_get_contents($this->fallbackPaths[$key]);
            $decoded = $content ? json_decode($content, true) : $default;
            $this->persist($key, $decoded ?? $default);
            return $decoded ?? $default;
        }

        return $default;
    }

    public function persist(string $key, $value): void
    {
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stmt = $this->pdo->prepare(
            'INSERT INTO kv_store(key, value, updated_at) VALUES(:key, :value, :updated_at)
             ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = excluded.updated_at'
        );
        $stmt->execute([
            ':key' => $key,
            ':value' => $encoded ?: 'null',
            ':updated_at' => time(),
        ]);
    }

    private function createTable(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS kv_store (
                key TEXT PRIMARY KEY,
                value TEXT NOT NULL,
                updated_at INTEGER NOT NULL
            )'
        );
    }
}
