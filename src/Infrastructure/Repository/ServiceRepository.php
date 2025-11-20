<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

class ServiceRepository extends Repository
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listByCategory(int $categoryId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, category_id, provider_code, name, description,
                    rate_per_1k, min_quantity, max_quantity, currency, metadata
             FROM services
             WHERE category_id = :category_id AND is_active = 1
             ORDER BY id ASC'
        );
        $stmt->execute(['category_id' => $categoryId]);

        return $stmt->fetchAll() ?: [];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, category_id, provider_code, name, description,
                    rate_per_1k, min_quantity, max_quantity, currency, metadata
             FROM services
             WHERE id = :id AND is_active = 1
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $service = $stmt->fetch();
        return $service ?: null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM services ORDER BY category_id ASC, id ASC'
        );

        return $stmt->fetchAll() ?: [];
    }

    public function create(array $attributes): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO services
            (category_id, provider_code, name, description, rate_per_1k, min_quantity, max_quantity, currency, metadata, is_active)
            VALUES
            (:category_id, :provider_code, :name, :description, :rate_per_1k, :min_quantity, :max_quantity, :currency, :metadata, :is_active)'
        );

        $stmt->execute([
            'category_id' => $attributes['category_id'],
            'provider_code' => $attributes['provider_code'],
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'rate_per_1k' => $attributes['rate_per_1k'],
            'min_quantity' => $attributes['min_quantity'],
            'max_quantity' => $attributes['max_quantity'],
            'currency' => $attributes['currency'] ?? 'USD',
            'metadata' => json_encode($attributes['metadata'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'is_active' => $attributes['is_active'] ?? 1,
        ]);

        $id = (int)$this->pdo->lastInsertId();
        return $this->find($id) ?? [];
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM services WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function setActive(int $id, bool $active): void
    {
        $stmt = $this->pdo->prepare('UPDATE services SET is_active = :active WHERE id = :id');
        $stmt->execute([
            'active' => $active ? 1 : 0,
            'id' => $id,
        ]);
    }
}
