<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Domain\Localization\LanguageManager;
use App\Domain\Numbers\NumberCatalogService;
use App\Domain\Users\UserManager;
use App\Domain\Wallet\WalletService;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Repository\NumberCountryRepository;
use App\Infrastructure\Repository\UserRepository;
use App\Infrastructure\Repository\WalletRepository;
use App\Infrastructure\Storage\JsonStore;
use App\Infrastructure\Telegram\TelegramClient;
use App\Presentation\BotKernel;
use App\Presentation\Keyboard\KeyboardFactory;

$telegramConfig = require APP_BASE_PATH . '/config/telegram.php';
$databaseConfig = require APP_BASE_PATH . '/config/database.php';
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
$userManager = new UserManager($userRepository);
$wallets = new WalletService($walletRepository);
$numberCatalog = new NumberCatalogService($countryRepository);

$kernel = new BotKernel(
    $languages,
    $store,
    $keyboardFactory,
    $telegram,
    $userManager,
    $wallets,
    $numberCatalog
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
