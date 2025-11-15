<?php

declare(strict_types=1);

namespace App\Domain\Numbers;

interface NumberProviderInterface
{
    /**
     * @return array{number: string, hash_code: string}
     */
    public function requestNumber(string $countryCode): array;
}
