#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Infrastructure\Database\Connection;
use RuntimeException;
use Throwable;

$config = require APP_BASE_PATH . '/config/database.php';
$databasePath = $config['path'] ?? APP_BASE_PATH . '/storage/database.sqlite';

if ($databasePath !== ':memory:' && !is_file($databasePath)) {
    throw new RuntimeException(sprintf('SQLite database not found at %s', $databasePath));
}

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

$issues = [];

try {
    $integrity = $pdo->query('PRAGMA integrity_check')->fetchColumn();
    if ($integrity !== 'ok') {
        $issues[] = 'PRAGMA integrity_check failed: ' . var_export($integrity, true);
        fwrite(STDERR, "[FAIL] integrity_check reason={$integrity}" . PHP_EOL);
    } else {
        echo "[OK] integrity_check => ok\n";
    }
} catch (Throwable $e) {
    $issues[] = 'PRAGMA integrity_check failed: ' . $e->getMessage();
    fwrite(STDERR, "[FAIL] integrity_check reason={$e->getMessage()}\n");
}

foreach ($tables as $table => $consumer) {
    try {
        $stmt = $pdo->query(sprintf('SELECT COUNT(*) FROM "%s"', $table));
        $count = $stmt->fetchColumn();
        if ($count === false) {
            throw new RuntimeException('Unable to fetch count.');
        }
        printf("[OK] %-16s rows=%-6d used_by=%s\n", $table, (int)$count, $consumer);
    } catch (Throwable $e) {
        $issues[] = sprintf('%s => %s', $table, $e->getMessage());
        printf("[FAIL] %-16s reason=%s\n", $table, $e->getMessage());
    }
}

if ($issues !== []) {
    fwrite(STDERR, "One or more tables are missing or the database is corrupted.\n");
    exit(1);
}

echo "SQLite database looks healthy.\n";
