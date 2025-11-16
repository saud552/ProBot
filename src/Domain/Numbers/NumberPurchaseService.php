<?php

declare(strict_types=1);

namespace App\Domain\Numbers;

use App\Domain\Notifications\NotificationService;
use App\Domain\Wallet\TransactionService;
use App\Domain\Wallet\WalletService;
use App\Infrastructure\Repository\NumberOrderRepository;
use RuntimeException;
use Throwable;

class NumberPurchaseService
{
    private NumberCatalogService $catalog;
    private WalletService $wallets;
    private NumberProviderInterface $provider;
    private NumberOrderRepository $orders;
    private NotificationService $notifications;
    private TransactionService $transactions;

    public function __construct(
        NumberCatalogService $catalog,
        WalletService $wallets,
        NumberProviderInterface $provider,
        NumberOrderRepository $orders,
        NotificationService $notifications,
        TransactionService $transactions
    ) {
        $this->catalog = $catalog;
        $this->wallets = $wallets;
        $this->provider = $provider;
        $this->orders = $orders;
        $this->notifications = $notifications;
        $this->transactions = $transactions;
    }

    /**
     * @return array<string, mixed>
     */
    public function purchaseWithUsd(int $userId, int $telegramUserId, string $countryCode): array
    {
        $country = $this->catalog->find($countryCode);
        if (!$country) {
            throw new RuntimeException('Country not available.');
        }

        $price = (float)$country['price_usd'];
        if ($price <= 0) {
            throw new RuntimeException('Invalid price.');
        }

        $this->wallets->debit($userId, $price, 'USD');

        try {
            $numberData = $this->provider->requestNumber($countryCode);

            $order = $this->orders->create([
                'user_id' => $userId,
                'country_code' => $country['code'],
                'provider_id' => $country['provider_id'],
                'number' => $numberData['number'],
                'hash_code' => $numberData['hash_code'],
                'price_usd' => $price,
                'currency' => 'USD',
                'status' => 'purchased',
                'metadata' => [],
            ]);

            $this->transactions->log(
                $userId,
                'debit',
                'purchase',
                $price,
                'USD',
                (string)$order['id'],
                [
                    'action' => 'number_purchase',
                    'country' => $country['code'],
                ]
            );

            $this->notifications->notifyPurchase($order, $country, $telegramUserId);

            return $order;
        } catch (Throwable $e) {
            $this->wallets->credit($userId, $price, 'USD');
            throw $e;
        }
    }

    public function purchaseWithStars(
        int $userId,
        int $telegramUserId,
        string $countryCode,
        int $starsAmount,
        float $priceUsd
    ): array {
        $country = $this->catalog->find($countryCode);
        if (!$country) {
            throw new RuntimeException('Country not available.');
        }

        $numberData = $this->provider->requestNumber($countryCode);

        $order = $this->orders->create([
            'user_id' => $userId,
            'country_code' => $country['code'],
            'provider_id' => $country['provider_id'],
            'number' => $numberData['number'],
            'hash_code' => $numberData['hash_code'],
            'price_usd' => $priceUsd,
            'currency' => 'USD',
            'status' => 'purchased',
            'metadata' => [],
        ]);

        $this->transactions->log(
            $userId,
            'debit',
            'stars',
            (float)$starsAmount,
            'XTR',
            (string)$order['id'],
            [
                'action' => 'number_purchase',
                'country' => $country['code'],
                'usd_equivalent' => $priceUsd,
            ]
        );

        $this->notifications->notifyPurchase($order, $country, $telegramUserId);

        return $order;
    }
}
