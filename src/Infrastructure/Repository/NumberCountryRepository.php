<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

class NumberCountryRepository extends Repository
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listActive(): array
    {
        $stmt = $this->pdo->query(
            'SELECT code, name, name_translations, price_usd, margin_percent, provider_id
             FROM number_countries
             WHERE is_active = 1
             ORDER BY name ASC'
        );

        return $stmt->fetchAll() ?: [];
    }

    public function find(string $code): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT code, name, name_translations, price_usd, margin_percent, provider_id
             FROM number_countries
             WHERE code = :code AND is_active = 1
             LIMIT 1'
        );
        $stmt->execute(['code' => strtoupper($code)]);
        $country = $stmt->fetch();

        return $country ?: null;
    }
}
