<?php

declare(strict_types=1);

namespace App\Domain\Smm;

use App\Infrastructure\Repository\ServiceCategoryRepository;
use App\Infrastructure\Repository\ServiceRepository;

class SmmCatalogService
{
    private ServiceCategoryRepository $categories;
    private ServiceRepository $services;

    public function __construct(
        ServiceCategoryRepository $categories,
        ServiceRepository $services
    ) {
        $this->categories = $categories;
        $this->services = $services;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function categories(): array
    {
        return $this->categories->listActive();
    }

    public function category(int $categoryId): ?array
    {
        return $this->categories->find($categoryId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function servicesByCategory(int $categoryId): array
    {
        $records = $this->services->listByCategory($categoryId);
        return array_map([$this, 'formatService'], $records);
    }

    public function service(int $serviceId): ?array
    {
        $service = $this->services->find($serviceId);
        return $service ? $this->formatService($service) : null;
    }

    /**
     * @param array<string, mixed> $service
     * @return array<string, mixed>
     */
    private function formatService(array $service): array
    {
        $rate = (float)$service['rate_per_1k'];
        $min = (int)$service['min_quantity'];
        $max = (int)$service['max_quantity'];
        $currency = $service['currency'] ?? 'USD';

        return [
            'id' => (int)$service['id'],
            'category_id' => (int)$service['category_id'],
            'provider_code' => (string)$service['provider_code'],
            'name' => $service['name'],
            'description' => $service['description'] ?? '',
            'rate_per_1k' => $rate,
            'min_quantity' => $min,
            'max_quantity' => $max,
            'currency' => $currency,
            'metadata' => $service['metadata'] ? json_decode((string)$service['metadata'], true) : [],
        ];
    }

    public function calculatePrice(float $ratePer1k, int $quantity): float
    {
        return ($ratePer1k / 1000) * $quantity;
    }
}
