<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

class SettingsRepository extends Repository
{
    public function find(string $key): ?array
    {
        $stmt = $this->pdo->prepare('SELECT value FROM settings WHERE `key` = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();

        if ($value === false) {
            return null;
        }

        $decoded = json_decode((string)$value, true);
        return is_array($decoded) ? $decoded : null;
    }

    public function upsert(string $key, array $value): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO settings (`key`, `value`)
             VALUES (:key, :value)
             ON CONFLICT(`key`) DO UPDATE SET
                 `value` = excluded.`value`,
                 updated_at = CURRENT_TIMESTAMP'
        );

        $stmt->execute([
            'key' => $key,
            'value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
