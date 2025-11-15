<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Domain\Localization\LanguageManager;
use App\Infrastructure\Storage\JsonStore;
use App\Infrastructure\Telegram\TelegramClient;
use App\Presentation\BotKernel;
use App\Presentation\Keyboard\KeyboardFactory;

$telegramConfig = require APP_BASE_PATH . '/config/telegram.php';
$languages = LanguageManager::fromFile(APP_BASE_PATH . '/lang/translations.php');
$store = new JsonStore([
    'langs' => APP_BASE_PATH . '/storage/langs.json',
]);
$keyboardFactory = new KeyboardFactory();
$telegram = new TelegramClient($telegramConfig);

$kernel = new BotKernel(
    $languages,
    $store,
    $keyboardFactory,
    $telegram
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
