<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

class ServiceCategoryRepository extends Repository
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listActive(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, code, name, caption
             FROM service_categories
             WHERE is_active = 1
             ORDER BY sort_order ASC, id ASC'
        );

        return $stmt->fetchAll() ?: [];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, code, name, caption
             FROM service_categories
             WHERE id = :id AND is_active = 1
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $category = $stmt->fetch();
        return $category ?: null;
    }
}
