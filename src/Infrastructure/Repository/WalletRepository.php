<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use RuntimeException;

class WalletRepository extends Repository
{
    public function find(int $userId, string $currency): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM wallets WHERE user_id = :user_id AND currency = :currency LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'currency' => strtoupper($currency),
        ]);

        $wallet = $stmt->fetch();
        return $wallet ?: null;
    }

    public function create(int $userId, string $currency): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO wallets (user_id, currency, balance) VALUES (:user_id, :currency, 0)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'currency' => strtoupper($currency),
        ]);

        return $this->find($userId, $currency)
            ?? throw new RuntimeException('Failed to create wallet.');
    }

    public function updateBalance(int $userId, string $currency, float $balance): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE wallets SET balance = :balance WHERE user_id = :user_id AND currency = :currency'
        );
        $stmt->execute([
            'balance' => $balance,
            'user_id' => $userId,
            'currency' => strtoupper($currency),
        ]);
    }

    public function adjustBalance(int $userId, string $currency, float $delta): array
    {
        $currency = strtoupper($currency);
        $wallet = $this->find($userId, $currency) ?? $this->create($userId, $currency);

        $newBalance = (float)$wallet['balance'] + $delta;
        if ($newBalance < 0) {
            throw new RuntimeException('Insufficient balance.');
        }

        $this->updateBalance($userId, $currency, $newBalance);
        $wallet['balance'] = $newBalance;

        return $wallet;
    }
}
