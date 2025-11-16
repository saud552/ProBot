<?php

declare(strict_types=1);

namespace App\Domain\Smm;

use App\Domain\Wallet\TransactionService;
use App\Domain\Wallet\WalletService;
use App\Infrastructure\Repository\SmmOrderRepository;
use RuntimeException;
use Throwable;

class SmmPurchaseService
{
    private SmmCatalogService $catalog;
    private WalletService $wallets;
    private TransactionService $transactions;
    private SmmProviderInterface $provider;
    private SmmOrderRepository $orders;

    public function __construct(
        SmmCatalogService $catalog,
        WalletService $wallets,
        TransactionService $transactions,
        SmmProviderInterface $provider,
        SmmOrderRepository $orders
    ) {
        $this->catalog = $catalog;
        $this->wallets = $wallets;
        $this->transactions = $transactions;
        $this->provider = $provider;
        $this->orders = $orders;
    }

    /**
     * @return array<string, mixed>
     */
    public function purchaseUsd(int $userId, int $serviceId, string $link, int $quantity): array
    {
        $service = $this->catalog->service($serviceId);
        if (!$service) {
            throw new RuntimeException('Service not available.');
        }

        $min = (int)$service['min_quantity'];
        $max = (int)$service['max_quantity'];
        if ($quantity < $min || $quantity > $max) {
            throw new RuntimeException('Quantity out of range.');
        }

        $price = $this->catalog->calculatePrice((float)$service['rate_per_1k'], $quantity);
        if ($price <= 0) {
            throw new RuntimeException('Invalid price calculation.');
        }

        $this->wallets->debit($userId, $price, $service['currency']);

        return $this->placeOrder($userId, $service, $link, $quantity, $price, function () use ($userId, $price, $service) {
            $this->wallets->credit($userId, $price, $service['currency']);
        });
    }

    public function purchaseWithStars(
        int $userId,
        array $service,
        string $link,
        int $quantity,
        float $priceUsd,
        int $starsAmount
    ): array {
        return $this->placeOrder($userId, $service, $link, $quantity, $priceUsd, null, [
            'type' => 'stars',
            'amount' => $starsAmount,
        ]);
    }

    /**
     * @param callable|null $onFailure
     * @param array<string, mixed>|null $starsMeta
     */
    private function placeOrder(
        int $userId,
        array $service,
        string $link,
        int $quantity,
        float $price,
        ?callable $onFailure = null,
        ?array $starsMeta = null
    ): array {
        try {
            $providerResponse = $this->provider->placeOrder([
                'service' => $service['provider_code'],
                'link' => $link,
                'quantity' => $quantity,
            ]);

            $order = $this->orders->create([
                'user_id' => $userId,
                'service_id' => $service['id'],
                'link' => $link,
                'quantity' => $quantity,
                'price' => $price,
                'currency' => $service['currency'],
                'status' => 'processing',
                'provider_order_id' => $providerResponse['provider_order_id'],
                'meta' => [],
            ]);

            $method = $starsMeta ? 'stars' : 'smm_purchase';
            $amount = $starsMeta['amount'] ?? $price;
            $currency = $starsMeta ? 'XTR' : $service['currency'];

            $meta = [
                'service' => $service['name'],
                'provider_order_id' => $providerResponse['provider_order_id'],
            ];
            if ($starsMeta) {
                $meta['usd_equivalent'] = $price;
            }

            $this->transactions->log(
                $userId,
                'debit',
                $method,
                $amount,
                $currency,
                (string)$order['id'],
                $meta
            );

            return $order;
        } catch (Throwable $e) {
            if ($onFailure) {
                $onFailure();
            }
            throw $e;
        }
    }
}
