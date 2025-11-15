<?php

declare(strict_types=1);

namespace App\Domain\Numbers;

use App\Infrastructure\Repository\NumberCountryRepository;

class NumberCatalogService
{
    private NumberCountryRepository $countries;

    public function __construct(NumberCountryRepository $countries)
    {
        $this->countries = $countries;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(): array
    {
        $records = $this->countries->listActive();
        return array_map([$this, 'formatCountry'], $records);
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, has_next: bool}
     */
    public function paginate(int $page, int $perPage = 8): array
    {
        $all = $this->list();
        $offset = max(0, $page * $perPage);
        $items = array_slice($all, $offset, $perPage);
        $hasNext = $offset + $perPage < count($all);

        return [
            'items' => $items,
            'has_next' => $hasNext,
        ];
    }

    public function find(string $code): ?array
    {
        $country = $this->countries->find($code);
        return $country ? $this->formatCountry($country) : null;
    }

    /**
     * @param array<string, mixed> $country
     * @return array<string, mixed>
     */
    private function formatCountry(array $country): array
    {
        $margin = (float)($country['margin_percent'] ?? 0);
        $basePrice = (float)$country['price_usd'];
        $finalPrice = $basePrice + ($basePrice * $margin / 100);

        return [
            'code' => $country['code'],
            'name' => $country['name'],
            'price_usd' => round($finalPrice, 2),
            'provider_id' => $country['provider_id'],
        ];
    }
}
