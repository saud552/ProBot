<?php

declare(strict_types=1);

namespace App\Domain\Wallet;

use App\Infrastructure\Repository\WalletRepository;
use RuntimeException;

class WalletService
{
    private WalletRepository $wallets;

    public function __construct(WalletRepository $wallets)
    {
        $this->wallets = $wallets;
    }

    public function ensure(int $userId, string $currency = 'USD'): array
    {
        return $this->wallets->find($userId, $currency)
            ?? $this->wallets->create($userId, $currency);
    }

    public function balance(int $userId, string $currency = 'USD'): float
    {
        $wallet = $this->ensure($userId, $currency);
        return (float)$wallet['balance'];
    }

    public function credit(int $userId, float $amount, string $currency = 'USD'): array
    {
        if ($amount <= 0) {
            throw new RuntimeException('Credit amount must be positive.');
        }
        return $this->wallets->adjustBalance($userId, $currency, $amount);
    }

    public function debit(int $userId, float $amount, string $currency = 'USD'): array
    {
        if ($amount <= 0) {
            throw new RuntimeException('Debit amount must be positive.');
        }
        return $this->wallets->adjustBalance($userId, $currency, -$amount);
    }
}
