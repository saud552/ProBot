<?php

declare(strict_types=1);

namespace App\Infrastructure\Numbers;

use App\Domain\Numbers\NumberProviderInterface;
use RuntimeException;

class SpiderNumberProvider implements NumberProviderInterface
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct(array $config)
    {
        $this->baseUrl = rtrim($config['base_url'] ?? '', '/');
        $this->apiKey = (string)($config['api_key'] ?? '');
        $this->timeout = (int)($config['timeout'] ?? 15);

        if ($this->baseUrl === '' || $this->apiKey === '') {
            throw new RuntimeException('Spider provider configuration is invalid.');
        }
    }

    /**
     * @return array{number: string, hash_code: string}
     */
    public function requestNumber(string $countryCode): array
    {
        $query = http_build_query([
            'apiKey' => $this->apiKey,
            'action' => 'getNumber',
            'country' => $countryCode,
        ]);
        $url = "{$this->baseUrl}?{$query}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("Spider request failed: {$error}");
        }
        curl_close($ch);

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || ($decoded['error'] ?? '') !== 'INFORMATION_SUCCESS') {
            throw new RuntimeException('Spider provider returned an error.');
        }

        $result = $decoded['result'] ?? [];
        return [
            'number' => $result['phone'] ?? '',
            'hash_code' => $result['hash_code'] ?? '',
        ];
    }

    /**
     * @return array{code: string, password: string}
     */
    public function requestCode(string $hashCode): array
    {
        $query = http_build_query([
            'apiKey' => $this->apiKey,
            'action' => 'getCode',
            'hash_code' => $hashCode,
        ]);
        $url = "{$this->baseUrl}?{$query}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("Spider request failed: {$error}");
        }
        curl_close($ch);

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || ($decoded['error'] ?? '') !== 'INFORMATION_SUCCESS') {
            throw new RuntimeException('Code not ready yet.');
        }

        $result = $decoded['result'] ?? [];
        return [
            'code' => $result['code'] ?? '',
            'password' => $result['password'] ?? '',
        ];
    }

    /**
     * @return array<string, float> Returns country code => base price mapping
     */
    public function getCountries(): array
    {
        $query = http_build_query([
            'apiKey' => $this->apiKey,
            'action' => 'getCountrys',
        ]);
        $url = "{$this->baseUrl}?{$query}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECT_TIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("Spider request failed: {$error}");
        }
        curl_close($ch);

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || ($decoded['error'] ?? '') !== 'INFORMATION_SUCCESS') {
            throw new RuntimeException('Spider provider returned an error while fetching countries.');
        }

        $countries = $decoded['result']['countries'][1] ?? [];
        if (!is_array($countries)) {
            return [];
        }

        $result = [];
        foreach ($countries as $code => $price) {
            if (is_string($code) && is_numeric($price)) {
                $result[strtoupper($code)] = (float)$price;
            }
        }

        return $result;
    }
}
