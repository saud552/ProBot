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
        $incomingLanguage = trim((string)($payload['language_code'] ?? ''));

        $user = $this->users->findByTelegramId($telegramId);
        if (!$user) {
            $languageForUser = $incomingLanguage !== '' ? $incomingLanguage : 'ar';
            $user = $this->users->create($telegramId, $languageForUser);
        } else {
            $storedLanguage = (string)($user['language_code'] ?? '');
            if ($storedLanguage === '') {
                $languageForUser = $incomingLanguage !== '' ? $incomingLanguage : 'ar';
                $this->users->updateLanguage((int)$user['id'], $languageForUser);
                $user['language_code'] = $languageForUser;
            } else {
                $user['language_code'] = $storedLanguage;
            }
        }

        if (!isset($user['language_code']) || $user['language_code'] === '') {
            $user['language_code'] = 'ar';
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

    public function updateLanguagePreference(int $userId, string $languageCode): void
    {
        $languageCode = trim($languageCode);
        if ($languageCode === '') {
            return;
        }

        $this->users->updateLanguage($userId, $languageCode);
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
