<?php

declare(strict_types=1);

namespace Numbers\Service;

use PDO;

class ActionLocker
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS action_locks (
                user_id TEXT NOT NULL,
                action TEXT NOT NULL,
                expires_at INTEGER NOT NULL,
                PRIMARY KEY (user_id, action)
            )'
        );
    }

    public function acquire($userId, string $action, int $ttlSeconds = 20): bool
    {
        $this->cleanup();

        $stmt = $this->pdo->prepare(
            'INSERT OR IGNORE INTO action_locks(user_id, action, expires_at)
             VALUES (:user_id, :action, :expires)'
        );

        $expires = time() + $ttlSeconds;
        $stmt->execute([
            ':user_id' => (string)$userId,
            ':action' => $action,
            ':expires' => $expires,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function release($userId, string $action): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM action_locks WHERE user_id = :user_id AND action = :action'
        );
        $stmt->execute([
            ':user_id' => (string)$userId,
            ':action' => $action,
        ]);
    }

    private function cleanup(): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM action_locks WHERE expires_at < :now');
        $stmt->execute([':now' => time()]);
    }
}
