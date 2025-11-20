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
        // بناء URL بنفس طريقة الملف المرفق: base_url?apiKay=KEY&action=...&country=...
        $url = "{$this->baseUrl}?apiKay={$this->apiKey}&action=getNumber&country=" . urlencode($countryCode);

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
        // بناء URL بنفس طريقة الملف المرفق: base_url?apiKay=KEY&action=...&hash_code=...
        $url = "{$this->baseUrl}?apiKay={$this->apiKey}&action=getCode&hash_code=" . urlencode($hashCode);

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
     * @return array<string, float> Returns array of country codes => base prices
     */
    public function getCountries(): array
    {
        // بناء URL بنفس طريقة الملف المرفق: base_url?apiKay=KEY&action=getCountrys
        $url = "{$this->baseUrl}?apiKay={$this->apiKey}&action=getCountrys";

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
            $errorMsg = $decoded['msg'] ?? $decoded['error'] ?? 'Unknown error';
            throw new RuntimeException('Failed to fetch countries from provider: ' . $errorMsg);
        }

        // API يرجع countries ككائن مع مفاتيح "1" و "2"، نحتاج "1"
        $countriesData = $decoded['result']['countries'] ?? [];
        $countries = $countriesData['1'] ?? $countriesData[1] ?? [];
        
        if (!is_array($countries) || empty($countries)) {
            return [];
        }

        $result = [];
        foreach ($countries as $code => $price) {
            if (is_numeric($price)) {
                $result[strtoupper((string)$code)] = (float)$price;
            }
        }

        return $result;
    }
}
