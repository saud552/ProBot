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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM service_categories ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll() ?: [];
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM service_categories WHERE code = :code LIMIT 1');
        $stmt->execute(['code' => $code]);
        $category = $stmt->fetch();

        return $category ?: null;
    }

    public function create(string $code, string $name, ?string $caption = null, int $sortOrder = 0): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO service_categories (code, name, caption, sort_order, is_active)
             VALUES (:code, :name, :caption, :sort_order, 1)
             ON DUPLICATE KEY UPDATE
                 name = VALUES(name),
                 caption = VALUES(caption),
                 sort_order = VALUES(sort_order),
                 is_active = 1'
        );
        $stmt->execute([
            'code' => $code,
            'name' => $name,
            'caption' => $caption,
            'sort_order' => $sortOrder,
        ]);

        return $this->findByCode($code) ?? [];
    }

    public function deleteByCode(string $code): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM service_categories WHERE code = :code');
        $stmt->execute(['code' => $code]);
    }

    public function setActive(string $code, bool $active): void
    {
        $stmt = $this->pdo->prepare('UPDATE service_categories SET is_active = :active WHERE code = :code');
        $stmt->execute([
            'active' => $active ? 1 : 0,
            'code' => $code,
        ]);
    }
}
