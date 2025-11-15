<?php

declare(strict_types=1);

namespace App\Domain\Payments;

use App\Domain\Settings\SettingsService;
use App\Infrastructure\Repository\StarPaymentRepository;
use App\Infrastructure\Telegram\TelegramClient;
use RuntimeException;

class StarPaymentService
{
    private StarPaymentRepository $payments;
    private SettingsService $settings;
    private TelegramClient $telegram;

    public function __construct(
        StarPaymentRepository $payments,
        SettingsService $settings,
        TelegramClient $telegram
    ) {
        $this->payments = $payments;
        $this->settings = $settings;
        $this->telegram = $telegram;
    }

    /**
     * @param array<string, mixed> $country
     * @return array{link: string, stars: int}
     */
    public function createNumberInvoice(
        int $userId,
        int $telegramUserId,
        array $country,
        float $priceUsd,
        array $strings
    ): array {
        $stars = $this->calculateStars($priceUsd);
        $payload = $this->generatePayload();
        $meta = [
            'country_code' => $country['code'],
            'country_name' => $country['name'],
            'price_usd' => $priceUsd,
            'type' => 'number',
        ];

        $record = $this->payments->create([
            'user_id' => $userId,
            'telegram_user_id' => $telegramUserId,
            'type' => 'number',
            'reference' => $country['code'],
            'payload' => $payload,
            'price_usd' => $priceUsd,
            'stars_amount' => $stars,
            'currency' => 'XTR',
            'meta' => $meta,
        ]);

        $title = $strings['stars_invoice_title'] ?? 'Buy Telegram Account';
        $description = str_replace(
            ['__c__', '__p__', '__s__'],
            [$country['name'], number_format($priceUsd, 2), (string)$stars],
            $strings['stars_invoice_description'] ?? 'Purchase __c__ number for __p__$ (~__s__⭐️)'
        );

        $link = $this->createInvoiceLink($title, $description, $payload, $stars);

        return [
            'link' => $link,
            'stars' => $stars,
            'payment' => $record,
        ];
    }

    /**
     * @param array<string, mixed> $service
     * @return array{link: string, stars: int}
     */
    public function createSmmInvoice(
        int $userId,
        int $telegramUserId,
        array $service,
        string $link,
        int $quantity,
        float $priceUsd,
        array $strings
    ): array {
        $stars = $this->calculateStars($priceUsd);
        $payload = $this->generatePayload();
        $meta = [
            'service_id' => $service['id'],
            'service_name' => $service['name'],
            'link' => $link,
            'quantity' => $quantity,
            'price_usd' => $priceUsd,
            'type' => 'smm',
        ];

        $record = $this->payments->create([
            'user_id' => $userId,
            'telegram_user_id' => $telegramUserId,
            'type' => 'smm',
            'reference' => (string)$service['id'],
            'payload' => $payload,
            'price_usd' => $priceUsd,
            'stars_amount' => $stars,
            'currency' => 'XTR',
            'meta' => $meta,
        ]);

        $title = $strings['smm_service_details'] ?? 'Boost Service';
        $description = str_replace(
            ['__service__', '__quantity__', '__price__'],
            [$service['name'], (string)$quantity, number_format($priceUsd, 2)],
            $strings['smm_order_summary'] ?? 'Service: __service__ Qty: __quantity__ Price: __price__$'
        );

        $link = $this->createInvoiceLink($title, $description, $payload, $stars);

        return [
            'link' => $link,
            'stars' => $stars,
            'payment' => $record,
        ];
    }

    public function findPending(string $payload): ?array
    {
        return $this->payments->findByPayload($payload);
    }

    /**
     * @param array<string, mixed> $paymentData
     */
    public function markCompleted(array $record, array $paymentData): void
    {
        $meta = $record['meta'] ? json_decode((string)$record['meta'], true) : [];
        $meta['telegram_payment'] = $paymentData;

        $this->payments->markCompleted((int)$record['id'], [
            'status' => 'completed',
            'provider_payment_charge_id' => $paymentData['telegram_payment_charge_id'] ?? null,
            'meta' => $meta,
        ]);
    }

    public function usdPerStar(): float
    {
        $starsSettings = $this->settings->stars();
        return max(0.0001, (float)($starsSettings['usd_per_star'] ?? 0.011));
    }

    private function calculateStars(float $priceUsd): int
    {
        return (int)max(1, ceil($priceUsd / $this->usdPerStar()));
    }

    private function generatePayload(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function createInvoiceLink(string $title, string $description, string $payload, int $stars): string
    {
        $request = [
            'title' => $title,
            'description' => $description,
            'payload' => $payload,
            'currency' => 'XTR',
            'prices' => json_encode([
                ['label' => $title, 'amount' => $stars],
            ]),
        ];

        $response = $this->telegram->call('createInvoiceLink', $request);

        if (!$response || ($response['ok'] ?? false) !== true) {
            throw new RuntimeException('Unable to generate invoice link.');
        }

        return (string)($response['result'] ?? '');
    }
}
