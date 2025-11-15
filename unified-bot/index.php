<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Domain\Localization\LanguageManager;
use App\Domain\Numbers\NumberCatalogService;
use App\Domain\Numbers\NumberPurchaseService;
use App\Domain\Users\UserManager;
use App\Domain\Wallet\WalletService;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Numbers\SpiderNumberProvider;
use App\Infrastructure\Repository\NumberCountryRepository;
use App\Infrastructure\Repository\NumberOrderRepository;
use App\Infrastructure\Repository\UserRepository;
use App\Infrastructure\Repository\WalletRepository;
use App\Infrastructure\Storage\JsonStore;
use App\Infrastructure\Telegram\TelegramClient;
use App\Presentation\BotKernel;
use App\Presentation\Keyboard\KeyboardFactory;

$telegramConfig = require APP_BASE_PATH . '/config/telegram.php';
$databaseConfig = require APP_BASE_PATH . '/config/database.php';
$providersConfig = require APP_BASE_PATH . '/config/providers.php';
$connection = new Connection($databaseConfig);

$languages = LanguageManager::fromFile(APP_BASE_PATH . '/lang/translations.php');
$store = new JsonStore([
    'langs' => APP_BASE_PATH . '/storage/langs.json',
]);
$keyboardFactory = new KeyboardFactory();
$telegram = new TelegramClient($telegramConfig);
$userRepository = new UserRepository($connection);
$walletRepository = new WalletRepository($connection);
$countryRepository = new NumberCountryRepository($connection);
$orderRepository = new NumberOrderRepository($connection);
$userManager = new UserManager($userRepository);
$wallets = new WalletService($walletRepository);
$numberCatalog = new NumberCatalogService($countryRepository);
$numberProvider = new SpiderNumberProvider($providersConfig['numbers']['spider'] ?? []);
$numberPurchase = new NumberPurchaseService($numberCatalog, $wallets, $numberProvider, $orderRepository);

$kernel = new BotKernel(
    $languages,
    $store,
    $keyboardFactory,
    $telegram,
    $userManager,
    $wallets,
    $numberCatalog,
    $numberPurchase
);

$payload = file_get_contents('php://input');
if (!$payload) {
    exit;
}

$update = json_decode($payload, true);
if (!is_array($update)) {
    exit;
}

$kernel->handle($update);
