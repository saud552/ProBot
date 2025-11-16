<?php

declare(strict_types=1);

namespace App\Domain\Smm;

interface SmmProviderInterface
{
    /**
     * @param array<string, mixed> $payload
     * @return array{provider_order_id: string}
     */
    public function placeOrder(array $payload): array;
}
