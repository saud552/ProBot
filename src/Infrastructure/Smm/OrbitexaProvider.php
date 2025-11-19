<?php

declare(strict_types=1);

namespace App\Infrastructure\Smm;

use App\Domain\Smm\SmmProviderInterface;
use RuntimeException;

class OrbitexaProvider implements SmmProviderInterface
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct(array $config)
    {
        $this->baseUrl = $config['base_url'] ?? 'https://orbitexa.com/api/v2';
        $this->apiKey = $config['api_key'] ?? '';
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function placeOrder(array $payload): array
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('Orbitexa provider API key is not configured.');
        }
        $post = array_merge($payload, [
            'key' => $this->apiKey,
            'action' => 'add',
        ]);

        $response = $this->request($post);
        if (!isset($response['order'])) {
            throw new RuntimeException('Orbitexa order failed.');
        }

        return [
            'provider_order_id' => (string)$response['order'],
        ];
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function request(array $post): array
    {
        $ch = curl_init($this->baseUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($post),
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'UnifiedBot/1.0',
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("Orbitexa request failed: {$error}");
        }
        curl_close($ch);

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Orbitexa returned invalid response.');
        }

        if (isset($decoded['error'])) {
            throw new RuntimeException('Orbitexa error: ' . $decoded['error']);
        }

        return $decoded;
    }
}
