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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM number_countries ORDER BY name ASC');
        return $stmt->fetchAll() ?: [];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function upsert(array $payload): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO number_countries
                (code, name, name_translations, price_usd, margin_percent, provider_id, is_active)
             VALUES
                (:code, :name, :name_translations, :price_usd, :margin_percent, :provider_id, :is_active)
             ON CONFLICT(code) DO UPDATE SET
                 name = excluded.name,
                 name_translations = excluded.name_translations,
                 price_usd = excluded.price_usd,
                 margin_percent = excluded.margin_percent,
                 provider_id = excluded.provider_id,
                 is_active = excluded.is_active,
                 updated_at = CURRENT_TIMESTAMP'
        );

        $stmt->execute([
            'code' => strtoupper((string)$payload['code']),
            'name' => $payload['name'],
            'name_translations' => isset($payload['name_translations'])
                ? json_encode($payload['name_translations'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null,
            'price_usd' => $payload['price_usd'],
            'margin_percent' => $payload['margin_percent'] ?? 0,
            'provider_id' => $payload['provider_id'],
            'is_active' => $payload['is_active'] ?? 1,
        ]);
    }

    public function remove(string $code): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM number_countries WHERE code = :code');
        $stmt->execute(['code' => strtoupper($code)]);
    }

    public function setActive(string $code, bool $active): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE number_countries
             SET is_active = :active,
                 updated_at = CURRENT_TIMESTAMP
             WHERE code = :code'
        );
        $stmt->execute([
            'active' => $active ? 1 : 0,
            'code' => strtoupper($code),
        ]);
    }
}
