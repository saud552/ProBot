#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Infrastructure\Database\Connection;
use RuntimeException;
use Throwable;

$config = require APP_BASE_PATH . '/config/database.php';
$connection = new Connection($config);
$pdo = $connection->pdo();

$tables = [
    'users' => 'App\Domain\Users\UserManager',
    'wallets' => 'App\Domain\Wallet\WalletService',
    'transactions' => 'App\Domain\Wallet\TransactionService',
    'orders_numbers' => 'App\Domain\Numbers\NumberPurchaseService',
    'orders_smm' => 'App\Domain\Smm\SmmPurchaseService',
    'referrals' => 'App\Domain\Referrals\ReferralService',
    'tickets' => 'App\Domain\Support\TicketService',
    'ticket_messages' => 'App\Domain\Support\TicketService',
    'star_payments' => 'App\Domain\Payments\StarPaymentService',
    'settings' => 'App\Domain\Settings\SettingsService',
];

$failing = [];

foreach ($tables as $table => $consumer) {
    try {
        $count = $pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
        if ($count === false) {
            throw new RuntimeException('Unable to fetch count.');
        }
        printf(
            "[OK] %-16s rows=%-6d used_by=%s\n",
            $table,
            (int)$count,
            $consumer
        );
    } catch (Throwable $e) {
        $failing[$table] = $e->getMessage();
        printf("[FAIL] %-16s reason=%s\n", $table, $e->getMessage());
    }
}

if ($failing !== []) {
    fwrite(STDERR, "One or more tables are missing or inaccessible.\n");
    exit(1);
}

echo "MySQL synchronization looks healthy.\n";
