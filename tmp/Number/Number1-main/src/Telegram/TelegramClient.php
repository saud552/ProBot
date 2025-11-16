<?php

declare(strict_types=1);

namespace Numbers\Telegram;

use RuntimeException;

class TelegramClient
{
    private string $token;
    private string $baseUrl;
    private int $timeout;
    private int $connectTimeout;

    public function __construct(
        string $token,
        int $timeout = 15,
        int $connectTimeout = 5
    ) {
        $this->token = $token;
        $this->baseUrl = "https://api.telegram.org/bot{$token}";
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>|null
     */
    public function call(string $method, array $params = []): ?array
    {
        $url = "{$this->baseUrl}/{$method}";
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            $this->logError($method, $error);
            throw new RuntimeException("Telegram request failed: {$error}");
        }

        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            $this->logError($method, "Invalid JSON response ({$statusCode}): {$response}");
            return null;
        }

        if (($decoded['ok'] ?? false) !== true) {
            $description = $decoded['description'] ?? 'Unknown error';
            $this->logError($method, "API error ({$statusCode}): {$description}");
        }

        return $decoded;
    }

    private function logError(string $method, string $message): void
    {
        $logLine = sprintf(
            "[%s] %s: %s%s",
            date('c'),
            $method,
            $message,
            PHP_EOL
        );

        file_put_contents(BASE_PATH . '/logs/telegram.log', $logLine, FILE_APPEND);
    }
}
