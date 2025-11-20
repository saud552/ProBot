<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

class SmmOrderRepository extends Repository
{
    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    public function create(array $attributes): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO orders_smm
            (user_id, service_id, link, quantity, price, currency, status, provider_order_id, meta)
            VALUES (:user_id, :service_id, :link, :quantity, :price, :currency, :status, :provider_order_id, :meta)'
        );
        $stmt->execute([
            'user_id' => $attributes['user_id'],
            'service_id' => $attributes['service_id'],
            'link' => $attributes['link'],
            'quantity' => $attributes['quantity'],
            'price' => $attributes['price'],
            'currency' => $attributes['currency'] ?? 'USD',
            'status' => $attributes['status'] ?? 'pending',
            'provider_order_id' => $attributes['provider_order_id'] ?? null,
            'meta' => json_encode($attributes['meta'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $id = (int)$this->pdo->lastInsertId();
        return $this->find($id);
    }

    public function updateStatus(int $id, string $status, array $meta = []): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE orders_smm
             SET status = :status,
                 meta = :meta,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            'status' => $status,
            'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'id' => $id,
        ]);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM orders_smm WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch();

        return $order ?: null;
    }

    public function countByService(int $serviceId, ?string $status = null): int
    {
        if ($status) {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM orders_smm WHERE service_id = :service AND status = :status'
            );
            $stmt->execute([
                'service' => $serviceId,
                'status' => $status,
            ]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM orders_smm WHERE service_id = :service'
            );
            $stmt->execute(['service' => $serviceId]);
        }

        return (int)$stmt->fetchColumn();
    }
}
