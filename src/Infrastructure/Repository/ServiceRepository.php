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
}
