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
    public function list(?string $languageCode = null): array
    {
        $records = $this->countries->listActive();
        return array_map(
            fn (array $country): array => $this->formatCountry($country, $languageCode),
            $records
        );
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, has_next: bool}
     */
    public function paginate(int $page, int $perPage = 8, ?string $languageCode = null): array
    {
        $all = $this->list($languageCode);
        $offset = max(0, $page * $perPage);
        $items = array_slice($all, $offset, $perPage);
        $hasNext = $offset + $perPage < count($all);

        return [
            'items' => $items,
            'has_next' => $hasNext,
        ];
    }

    public function find(string $code, ?string $languageCode = null): ?array
    {
        $country = $this->countries->find($code);
        return $country ? $this->formatCountry($country, $languageCode) : null;
    }

    /**
     * @param array<string, mixed> $country
     * @return array<string, mixed>
     */
    private function formatCountry(array $country, ?string $languageCode = null): array
    {
        $margin = (float)($country['margin_percent'] ?? 0);
        $basePrice = (float)$country['price_usd'];
        $finalPrice = $basePrice + ($basePrice * $margin / 100);
        $translations = $this->extractTranslations($country['name_translations'] ?? null);
        $displayName = $this->resolveDisplayName((string)$country['name'], $translations, $languageCode);

        return [
            'code' => $country['code'],
            'name' => $country['name'],
            'display_name' => $displayName,
            'translations' => $translations,
            'price_usd' => round($finalPrice, 2),
            'provider_id' => $country['provider_id'],
        ];
    }

    /**
     * @param mixed $raw
     * @return array<string, string>
     */
    private function extractTranslations($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_map('strval', $decoded);
            }
        }

        return [];
    }

    /**
     * @param array<string, string> $translations
     */
    private function resolveDisplayName(string $default, array $translations, ?string $languageCode): string
    {
        if ($languageCode === null || $translations === []) {
            return $default;
        }

        $candidates = [$languageCode];
        $lower = strtolower($languageCode);
        if ($lower !== $languageCode) {
            $candidates[] = $lower;
        }
        if (str_contains($lower, '-')) {
            $candidates[] = strtok($lower, '-');
        } elseif (strlen($lower) > 2) {
            $candidates[] = substr($lower, 0, 2);
        }

        foreach ($candidates as $candidate) {
            if ($candidate && isset($translations[$candidate]) && $translations[$candidate] !== '') {
                return (string)$translations[$candidate];
            }
        }

        return $default;
    }
}
