<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use RuntimeException;

class StarPaymentRepository extends Repository
{
    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    public function create(array $attributes): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO star_payments
             (user_id, telegram_user_id, type, reference, payload, price_usd, stars_amount, currency, status, meta)
             VALUES (:user_id, :telegram_user_id, :type, :reference, :payload, :price_usd, :stars_amount, :currency, :status, :meta)'
        );
        $stmt->execute([
            'user_id' => $attributes['user_id'],
            'telegram_user_id' => $attributes['telegram_user_id'],
            'type' => $attributes['type'],
            'reference' => $attributes['reference'],
            'payload' => $attributes['payload'],
            'price_usd' => $attributes['price_usd'],
            'stars_amount' => $attributes['stars_amount'],
            'currency' => $attributes['currency'] ?? 'XTR',
            'status' => $attributes['status'] ?? 'pending',
            'meta' => json_encode($attributes['meta'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $id = (int)$this->pdo->lastInsertId();
        return $this->findById($id) ?? throw new RuntimeException('Failed to create star payment.');
    }

    public function findByPayload(string $payload): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM star_payments WHERE payload = :payload AND status = "pending" LIMIT 1');
        $stmt->execute(['payload' => $payload]);
        $record = $stmt->fetch();

        return $record ?: null;
    }

    public function markCompleted(int $id, array $extra = []): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE star_payments
             SET status = :status,
                 provider_payment_charge_id = :charge,
                 meta = :meta,
                 fulfilled_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'status' => $extra['status'] ?? 'completed',
            'charge' => $extra['provider_payment_charge_id'] ?? null,
            'meta' => json_encode($extra['meta'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'id' => $id,
        ]);
    }

    private function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM star_payments WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();

        return $record ?: null;
    }
}
