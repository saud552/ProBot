<?php

declare(strict_types=1);

namespace App\Domain\Numbers;

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

    public function __construct(
        NumberCatalogService $catalog,
        WalletService $wallets,
        NumberProviderInterface $provider,
        NumberOrderRepository $orders
    ) {
        $this->catalog = $catalog;
        $this->wallets = $wallets;
        $this->provider = $provider;
        $this->orders = $orders;
    }

    /**
     * @return array<string, mixed>
     */
    public function purchaseWithUsd(int $userId, string $countryCode): array
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

            return $this->orders->create([
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
        } catch (Throwable $e) {
            $this->wallets->credit($userId, $price, 'USD');
            throw $e;
        }
    }
}
