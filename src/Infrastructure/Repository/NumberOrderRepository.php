<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

class NumberOrderRepository extends Repository
{
    public function create(array $attributes): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO orders_numbers
            (user_id, country_code, provider_id, number, hash_code, price_usd, currency, status, metadata)
            VALUES (:user_id, :country_code, :provider_id, :number, :hash_code, :price_usd, :currency, :status, :metadata)'
        );

        $stmt->execute([
            'user_id' => $attributes['user_id'],
            'country_code' => $attributes['country_code'],
            'provider_id' => $attributes['provider_id'],
            'number' => $attributes['number'],
            'hash_code' => $attributes['hash_code'],
            'price_usd' => $attributes['price_usd'],
            'currency' => $attributes['currency'] ?? 'USD',
            'status' => $attributes['status'] ?? 'purchased',
            'metadata' => json_encode($attributes['metadata'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $id = (int)$this->pdo->lastInsertId();
        return $this->find($id);
    }

    public function updateStatus(int $orderId, string $status, array $metadata = []): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE orders_numbers SET status = :status, metadata = :metadata, updated_at = NOW() WHERE id = :id'
        );

        $stmt->execute([
            'status' => $status,
            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'id' => $orderId,
        ]);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM orders_numbers WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch();

        return $order ?: null;
    }

    public function countByCountry(string $countryCode, ?string $status = null): int
    {
        if ($status) {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM orders_numbers WHERE country_code = :code AND status = :status'
            );
            $stmt->execute([
                'code' => strtoupper($countryCode),
                'status' => $status,
            ]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM orders_numbers WHERE country_code = :code'
            );
            $stmt->execute(['code' => strtoupper($countryCode)]);
        }

        return (int)$stmt->fetchColumn();
    }
}
