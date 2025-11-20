<?php

declare(strict_types=1);

namespace App\Domain\Numbers;

use App\Infrastructure\Numbers\SpiderNumberProvider;
use App\Infrastructure\Repository\NumberCountryRepository;
use RuntimeException;

class NumberCountryImportService
{
    private SpiderNumberProvider $provider;
    private NumberCountryRepository $repository;
    private int $providerId;
    private array $countryNames;

    public function __construct(
        SpiderNumberProvider $provider,
        NumberCountryRepository $repository,
        int $providerId
    ) {
        $this->provider = $provider;
        $this->repository = $repository;
        $this->providerId = $providerId;
        $this->countryNames = $this->loadCountryNames();
    }

    /**
     * Import all countries from provider with optional global margin
     * If globalMargin is null, existing margin will be preserved for updated countries
     * @return array{imported: int, updated: int, failed: int}
     */
    public function importAll(?float $globalMargin = null): array
    {
        try {
            $countries = $this->provider->getCountries();
        } catch (RuntimeException $e) {
            throw new RuntimeException('Failed to fetch countries from provider: ' . $e->getMessage());
        }

        $imported = 0;
        $updated = 0;
        $failed = 0;

        foreach ($countries as $code => $basePrice) {
            if (!is_numeric($basePrice) || $basePrice <= 0) {
                $failed++;
                continue;
            }

            $existing = $this->repository->find($code);
            $name = $this->getCountryName($code);
            $margin = $globalMargin ?? ($existing['margin_percent'] ?? 0);

            try {
                $this->repository->upsert([
                    'code' => $code,
                    'name' => $name,
                    'name_translations' => null,
                    'price_usd' => (float)$basePrice,
                    'margin_percent' => $margin,
                    'provider_id' => $this->providerId,
                    'is_active' => 1,
                ]);

                if ($existing) {
                    $updated++;
                } else {
                    $imported++;
                }
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return [
            'imported' => $imported,
            'updated' => $updated,
            'failed' => $failed,
        ];
    }

    /**
     * Import a single country
     */
    public function importSingle(string $code, ?float $customPrice = null, ?float $margin = null): bool
    {
        try {
            $countries = $this->provider->getCountries();
        } catch (RuntimeException $e) {
            throw new RuntimeException('Failed to fetch countries from provider: ' . $e->getMessage());
        }

        $code = strtoupper($code);
        if (!isset($countries[$code])) {
            throw new RuntimeException("Country code {$code} not found in provider.");
        }

        $basePrice = $customPrice ?? $countries[$code];
        $existing = $this->repository->find($code);
        $name = $this->getCountryName($code);
        $marginPercent = $margin ?? ($existing['margin_percent'] ?? 0);

        $this->repository->upsert([
            'code' => $code,
            'name' => $name,
            'name_translations' => null,
            'price_usd' => (float)$basePrice,
            'margin_percent' => $marginPercent,
            'provider_id' => $this->providerId,
            'is_active' => 1,
        ]);

        return true;
    }

    /**
     * Delete a country
     */
    public function delete(string $code): void
    {
        $this->repository->remove(strtoupper($code));
    }

    /**
     * Update global margin for all countries
     * @return int Number of updated countries
     */
    public function updateGlobalMargin(float $margin): int
    {
        $all = $this->repository->listAll();
        $updated = 0;

        foreach ($all as $country) {
            $this->repository->upsert([
                'code' => $country['code'],
                'name' => $country['name'],
                'name_translations' => $country['name_translations'] ?? null,
                'price_usd' => $country['price_usd'],
                'margin_percent' => $margin,
                'provider_id' => $country['provider_id'],
                'is_active' => $country['is_active'] ?? 1,
            ]);
            $updated++;
        }

        return $updated;
    }

    /**
     * Update price for a specific country
     */
    public function updateCountryPrice(string $code, float $price, ?float $margin = null): void
    {
        $code = strtoupper($code);
        $existing = $this->repository->find($code);

        if (!$existing) {
            throw new RuntimeException("Country {$code} not found.");
        }

        $marginPercent = $margin ?? $existing['margin_percent'] ?? 0;

        $this->repository->upsert([
            'code' => $code,
            'name' => $existing['name'],
            'name_translations' => $existing['name_translations'] ?? null,
            'price_usd' => $price,
            'margin_percent' => $marginPercent,
            'provider_id' => $existing['provider_id'],
            'is_active' => $existing['is_active'] ?? 1,
        ]);
    }

    /**
     * Get country name from cache or return code
     */
    private function getCountryName(string $code): string
    {
        $code = strtoupper($code);
        return $this->countryNames[$code] ?? $code;
    }

    /**
     * Load country names from the old file structure
     * @return array<string, string>
     */
    private function loadCountryNames(): array
    {
        $names = [
            'AE' => 'United Arab Emirates', 'UA' => 'Ukraine', 'BH' => 'Bahrain',
            'DE' => 'Germany', 'AO' => 'Angola', 'GE' => 'Georgia',
            'GB' => 'United Kingdom', 'IR' => 'Iran', 'KG' => 'Kyrgyzstan',
            'KZ' => 'Kazakhstan', 'KW' => 'Kuwait', 'MZ' => 'Mozambique',
            'OM' => 'Oman', 'NE' => 'Niger', 'PS' => 'Palestine',
            'PG' => 'Papua New Guinea', 'QA' => 'Qatar', 'SB' => 'Solomon Islands',
            'TW' => 'Taiwan', 'TR' => 'Turkey', 'YE' => 'Yemen',
            'JO' => 'Jordan', 'US' => 'United States', 'RO' => 'Romania',
            'HN' => 'Honduras', 'AW' => 'Aruba', 'FR' => 'France',
            'IL' => 'Israel', 'TJ' => 'Tajikistan', 'CH' => 'Switzerland',
            'IQ' => 'Iraq', 'TN' => 'Tunisia', 'CF' => 'Central African Republic',
            'MM' => 'Myanmar', 'ZA' => 'South Africa', 'CM' => 'Cameroon',
            'DZ' => 'Algeria', 'NG' => 'Nigeria', 'BG' => 'Bulgaria',
            'ML' => 'Mali', 'MX' => 'Mexico', 'GH' => 'Ghana',
            'PT' => 'Portugal', 'AZ' => 'Azerbaijan', 'ES' => 'Spain',
            'NL' => 'Netherlands', 'CU' => 'Cuba', 'FK' => 'Falkland Islands',
            'AR' => 'Argentina', 'UZ' => 'Uzbekistan', 'LY' => 'Libya',
            'PE' => 'Peru', 'HU' => 'Hungary', 'VE' => 'Venezuela',
            'CO' => 'Colombia', 'BA' => 'Bosnia and Herzegovina', 'SE' => 'Sweden',
            'AM' => 'Armenia', 'SV' => 'El Salvador', 'JP' => 'Japan',
            'SL' => 'Sierra Leone', 'CZ' => 'Czech Republic', 'ID' => 'Indonesia',
            'PL' => 'Poland', 'BN' => 'Brunei', 'MG' => 'Madagascar',
            'NO' => 'Norway', 'KR' => 'South Korea', 'AU' => 'Australia',
            'TC' => 'Turks and Caicos Islands', 'EC' => 'Ecuador', 'MY' => 'Malaysia',
            'AG' => 'Antigua and Barbuda', 'CI' => 'Ivory Coast', 'KE' => 'Kenya',
            'BJ' => 'Benin', 'GD' => 'Grenada', 'NI' => 'Nicaragua',
            'WS' => 'Samoa', 'TO' => 'Tonga', 'CA' => 'Canada',
            'FI' => 'Finland', 'MN' => 'Mongolia', 'GY' => 'Guyana',
            'SZ' => 'Eswatini', 'PK' => 'Pakistan', 'CN' => 'China',
            'ZM' => 'Zambia', 'VC' => 'Saint Vincent and the Grenadines',
            'BD' => 'Bangladesh', 'ZW' => 'Zimbabwe', 'SC' => 'Seychelles',
            'TD' => 'Chad', 'EG' => 'Egypt', 'TH' => 'Thailand',
            'PR' => 'Puerto Rico', 'TM' => 'Turkmenistan', 'BR' => 'Brazil',
            'SG' => 'Singapore', 'LB' => 'Lebanon', 'FO' => 'Faroe Islands',
            'LR' => 'Liberia', 'VU' => 'Vanuatu', 'UY' => 'Uruguay',
            'PY' => 'Paraguay', 'HT' => 'Haiti', 'GL' => 'Greenland',
            'IN' => 'India', 'SY' => 'Syria', 'MT' => 'Malta',
            'SA' => 'Saudi Arabia', 'SS' => 'South Sudan', 'BS' => 'Bahamas',
            'HK' => 'Hong Kong', 'NC' => 'New Caledonia', 'IT' => 'Italy',
            'ME' => 'Montenegro', 'MR' => 'Mauritania', 'KM' => 'Comoros',
            'PA' => 'Panama', 'ST' => 'Sao Tome and Principe', 'TL' => 'Timor-Leste',
            'MV' => 'Maldives', 'AL' => 'Albania', 'RW' => 'Rwanda',
            'CY' => 'Cyprus', 'XK' => 'Kosovo', 'LC' => 'Saint Lucia',
            'TZ' => 'Tanzania', 'MW' => 'Malawi', 'BE' => 'Belgium',
            'BW' => 'Botswana', 'NZ' => 'New Zealand', 'CK' => 'Cook Islands',
            'LK' => 'Sri Lanka', 'SI' => 'Slovenia', 'RE' => 'Réunion',
            'GT' => 'Guatemala', 'BO' => 'Bolivia', 'YT' => 'Mayotte',
            'MO' => 'Macau', 'DJ' => 'Djibouti', 'MD' => 'Moldova',
            'CL' => 'Chile', 'GM' => 'Gambia', 'NA' => 'Namibia',
            'PH' => 'Philippines', 'SO' => 'Somalia', 'KH' => 'Cambodia',
            'EE' => 'Estonia', 'MU' => 'Mauritius', 'SN' => 'Senegal',
            'ET' => 'Ethiopia', 'CV' => 'Cape Verde', 'BF' => 'Burkina Faso',
            'GW' => 'Guinea-Bissau', 'LT' => 'Lithuania', 'FJ' => 'Fiji',
            'BI' => 'Burundi', 'GR' => 'Greece', 'AT' => 'Austria',
            'SD' => 'Sudan', 'LV' => 'Latvia', 'TT' => 'Trinidad and Tobago',
            'LU' => 'Luxembourg', 'MA' => 'Morocco', 'LA' => 'Laos',
            'CR' => 'Costa Rica', 'DM' => 'Dominica', 'BB' => 'Barbados',
            'BZ' => 'Belize', 'BY' => 'Belarus', 'BM' => 'Bermuda',
            'GN' => 'Guinea', 'CW' => 'Curaçao', 'KI' => 'Kiribati',
            'HR' => 'Croatia', 'GQ' => 'Equatorial Guinea', 'MK' => 'North Macedonia',
            'NR' => 'Nauru', 'SK' => 'Slovakia', 'SR' => 'Suriname',
            'UG' => 'Uganda', 'NP' => 'Nepal', 'AF' => 'Afghanistan',
            'RU' => 'Russia',
        ];

        return $names;
    }
}
