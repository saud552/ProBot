<?php

declare(strict_types=1);

namespace App\Domain\Users;

use App\Infrastructure\Repository\UserRepository;

class UserManager
{
    private UserRepository $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function sync(array $payload): array
    {
        $telegramId = (int)$payload['telegram_id'];
        $languageCode = (string)($payload['language_code'] ?? 'ar');

        $user = $this->users->findByTelegramId($telegramId);
        if (!$user) {
            $user = $this->users->create($telegramId, $languageCode);
        }

        $languageCode = $languageCode !== '' ? $languageCode : ($user['language_code'] ?? 'ar');
        if ($languageCode !== $user['language_code']) {
            $this->users->updateLanguage((int)$user['id'], $languageCode);
            $user['language_code'] = $languageCode;
        }

        $profile = [
            'first_name' => $payload['first_name'] ?? null,
            'username' => $payload['username'] ?? null,
            'referrer_id' => $payload['referrer_id'] ?? null,
        ];
        $this->users->upsertProfile((int)$user['id'], $profile);

        return $user;
    }

    public function findByTelegramId(int $telegramId): ?array
    {
        return $this->users->findByTelegramId($telegramId);
    }

    public function findById(int $userId): ?array
    {
        return $this->users->findById($userId);
    }

    public function assignReferrerIfEmpty(int $userId, int $referrerId): void
    {
        $this->users->assignReferrerIfEmpty($userId, $referrerId);
    }

    public function setBanStatus(int $userId, bool $banned): void
    {
        $this->users->setBanStatus($userId, $banned);
    }

    public function setBanStatusByTelegramId(int $telegramId, bool $banned): ?array
    {
        $user = $this->findByTelegramId($telegramId);
        if (!$user) {
            return null;
        }

        $this->users->setBanStatus((int)$user['id'], $banned);
        return $this->users->findById((int)$user['id']);
    }
}
