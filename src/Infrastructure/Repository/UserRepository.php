<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use PDOException;
use RuntimeException;

class UserRepository extends Repository
{
    public function findByTelegramId(int $telegramId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE telegram_id = :telegram_id LIMIT 1');
        $stmt->execute(['telegram_id' => $telegramId]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function create(int $telegramId, string $languageCode): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (telegram_id, language_code) VALUES (:telegram_id, :language_code)'
        );
        $stmt->execute([
            'telegram_id' => $telegramId,
            'language_code' => $languageCode,
        ]);

        return $this->findByTelegramId($telegramId)
            ?? throw new RuntimeException('Failed to create user record.');
    }

    public function updateLanguage(int $userId, string $languageCode): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET language_code = :language, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $stmt->execute([
            'language' => $languageCode,
            'id' => $userId,
        ]);
    }

    /**
     * @param array<string, mixed> $profile
     */
    public function upsertProfile(int $userId, array $profile): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO profiles (user_id, first_name, username, referrer_id, last_seen_at)
             VALUES (:user_id, :first_name, :username, :referrer_id, CURRENT_TIMESTAMP)
             ON CONFLICT(user_id) DO UPDATE SET
                 first_name = excluded.first_name,
                 username = excluded.username,
                 referrer_id = excluded.referrer_id,
                 last_seen_at = excluded.last_seen_at'
        );

        $stmt->execute([
            'user_id' => $userId,
            'first_name' => $profile['first_name'] ?? null,
            'username' => $profile['username'] ?? null,
            'referrer_id' => $profile['referrer_id'] ?? null,
        ]);
    }

    public function markLastSeen(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE profiles SET last_seen_at = CURRENT_TIMESTAMP WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
    }

    public function findById(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function assignReferrerIfEmpty(int $userId, int $referrerId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE profiles
             SET referrer_id = :referrer
             WHERE user_id = :user
               AND (referrer_id IS NULL OR referrer_id = 0)'
        );
        $stmt->execute([
            'referrer' => $referrerId,
            'user' => $userId,
        ]);
    }

    public function setBanStatus(int $userId, bool $banned): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET is_banned = :banned, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $stmt->execute([
            'banned' => $banned ? 1 : 0,
            'id' => $userId,
        ]);
    }

    /**
     * @return array<int, array{id: int, telegram_id: int}>
     */
    public function listAllTelegramIds(): array
    {
        $stmt = $this->pdo->query('SELECT id, telegram_id FROM users ORDER BY id ASC');
        return $stmt->fetchAll() ?: [];
    }
}
