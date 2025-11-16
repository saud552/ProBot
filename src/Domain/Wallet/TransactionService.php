<?php

declare(strict_types=1);

namespace App\Domain\Wallet;

use App\Infrastructure\Repository\TransactionRepository;

class TransactionService
{
    private TransactionRepository $transactions;

    public function __construct(TransactionRepository $transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function log(
        int $userId,
        string $type,
        string $method,
        float $amount,
        string $currency = 'USD',
        ?string $reference = null,
        array $meta = []
    ): void {
        $this->transactions->create([
            'user_id' => $userId,
            'type' => $type,
            'method' => $method,
            'currency' => $currency,
            'amount' => $amount,
            'reference' => $reference,
            'meta' => $meta,
        ]);
    }
}
