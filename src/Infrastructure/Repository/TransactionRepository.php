<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

class TransactionRepository extends Repository
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO transactions (user_id, type, method, currency, amount, reference, meta)
             VALUES (:user_id, :type, :method, :currency, :amount, :reference, :meta)'
        );

        $stmt->execute([
            'user_id' => $attributes['user_id'],
            'type' => $attributes['type'],
            'method' => $attributes['method'],
            'currency' => strtoupper($attributes['currency'] ?? 'USD'),
            'amount' => $attributes['amount'],
            'reference' => $attributes['reference'] ?? null,
            'meta' => json_encode($attributes['meta'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
