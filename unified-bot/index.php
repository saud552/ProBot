<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Domain\Localization\LanguageManager;
use App\Domain\Numbers\NumberCatalogService;
use App\Domain\Numbers\NumberCodeService;
use App\Domain\Numbers\NumberPurchaseService;
use App\Domain\Notifications\NotificationService;
use App\Domain\Settings\ForcedSubscriptionService;
use App\Domain\Settings\SettingsService;
use App\Domain\Support\TicketService;
use App\Domain\Users\UserManager;
use App\Domain\Wallet\WalletService;
use App\Domain\Wallet\TransactionService;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Numbers\SpiderNumberProvider;
use App\Infrastructure\Repository\NumberCountryRepository;
use App\Infrastructure\Repository\NumberOrderRepository;
use App\Infrastructure\Repository\SettingsRepository;
use App\Infrastructure\Repository\TicketRepository;
use App\Infrastructure\Repository\TransactionRepository;
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
$settingsRepository = new SettingsRepository($connection);
$transactionRepository = new TransactionRepository($connection);
$ticketRepository = new TicketRepository($connection);
$userManager = new UserManager($userRepository);
$wallets = new WalletService($walletRepository);
$numberCatalog = new NumberCatalogService($countryRepository);
$numberProvider = new SpiderNumberProvider($providersConfig['numbers']['spider'] ?? []);
$settingsService = new SettingsService($settingsRepository);
$forcedSubscription = new ForcedSubscriptionService($settingsService, $telegram);
$notificationService = new NotificationService($settingsService, $telegram);
$transactionService = new TransactionService($transactionRepository);
$ticketService = new TicketService($ticketRepository);
$numberPurchase = new NumberPurchaseService(
    $numberCatalog,
    $wallets,
    $numberProvider,
    $orderRepository,
    $notificationService,
    $transactionService
);
$numberCodes = new NumberCodeService(
    $numberProvider,
    $orderRepository,
    $notificationService,
    $ticketService,
    $transactionService
);

$kernel = new BotKernel(
    $languages,
    $store,
    $keyboardFactory,
    $telegram,
    $userManager,
    $wallets,
    $numberCatalog,
    $numberPurchase,
    $numberCodes,
    $forcedSubscription
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
