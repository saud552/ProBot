<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Domain\Localization\LanguageManager;
use App\Domain\Numbers\NumberCatalogService;
use App\Domain\Numbers\NumberPurchaseService;
use App\Domain\Users\UserManager;
use App\Domain\Wallet\WalletService;
use App\Infrastructure\Storage\JsonStore;
use App\Infrastructure\Telegram\TelegramClient;
use App\Presentation\Keyboard\KeyboardFactory;

class BotKernel
{
    private LanguageManager $languages;
    private JsonStore $store;
    private KeyboardFactory $keyboardFactory;
    private TelegramClient $telegram;
    private UserManager $userManager;
    private WalletService $wallets;
    private NumberCatalogService $numberCatalog;
    private NumberPurchaseService $numberPurchase;

    /**
     * @var array<int, string>
     */
    private array $languageCache = [];

    public function __construct(
        LanguageManager $languages,
        JsonStore $store,
        KeyboardFactory $keyboardFactory,
        TelegramClient $telegram,
        UserManager $userManager,
        WalletService $wallets,
        NumberCatalogService $numberCatalog,
        NumberPurchaseService $numberPurchase
    ) {
        $this->languages = $languages;
        $this->store = $store;
        $this->keyboardFactory = $keyboardFactory;
        $this->telegram = $telegram;
        $this->userManager = $userManager;
        $this->wallets = $wallets;
        $this->numberCatalog = $numberCatalog;
        $this->numberPurchase = $numberPurchase;
        $this->languageCache = $this->store->load('langs', []);
    }

    public function handle(array $update): void
    {
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
            return;
        }

        if (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
        }
    }

    /**
     * @param array<string, mixed> $message
     */
    private function handleMessage(array $message): void
    {
        $chatId = (int)($message['chat']['id'] ?? 0);
        $telegramUser = $message['from'] ?? [];
        $userId = (int)($telegramUser['id'] ?? 0);
        $text = trim((string)($message['text'] ?? ''));
        $languageCode = (string)($telegramUser['language_code'] ?? 'ar');

        if ($chatId === 0 || $userId === 0) {
            return;
        }

        $userRecord = $this->userManager->sync([
            'telegram_id' => $userId,
            'language_code' => $languageCode,
            'first_name' => $telegramUser['first_name'] ?? null,
            'username' => $telegramUser['username'] ?? null,
        ]);
        $this->wallets->ensure((int)$userRecord['id'], 'USD');

        $userLang = $this->languages->ensure($userRecord['language_code'] ?? $languageCode);
        $this->cacheLanguage($userId, $userLang);
        $strings = $this->languages->strings($userLang);
        $changeLabel = $this->languages->label($userLang, 'change_language', 'Change Language');

        if ($text === '/start') {
            $this->sendMessage($chatId, $strings['welcome'] ?? 'Welcome', $this->keyboardFactory->mainMenu($strings, $changeLabel));
            return;
        }

        // fallback to help text
        $this->sendMessage(
            $chatId,
            $strings['main_menu'] ?? 'Main Menu',
            $this->keyboardFactory->mainMenu($strings, $changeLabel)
        );
    }

    /**
     * @param array<string, mixed> $callback
     */
    private function handleCallback(array $callback): void
    {
        $data = (string)($callback['data'] ?? '');
        $message = $callback['message'] ?? [];
        $chatId = (int)($message['chat']['id'] ?? 0);
        $messageId = (int)($message['message_id'] ?? 0);
        $user = $callback['from'] ?? [];
        $userId = (int)($user['id'] ?? 0);

        if ($chatId === 0 || $messageId === 0 || $userId === 0) {
            return;
        }

        $userRecord = $this->userManager->findByTelegramId($userId);
        if ($userRecord) {
            $userLang = $this->languages->ensure($userRecord['language_code'] ?? 'ar');
            $this->cacheLanguage($userId, $userLang);
        } else {
            $userLang = $this->determineLanguage($userId, (string)($user['language_code'] ?? 'ar'));
        }
        $strings = $this->languages->strings($userLang);
        $changeLabel = $this->languages->label($userLang, 'change_language', 'Change Language');
        $backLabel = $this->languages->label($userLang, 'back', 'Back');

        switch ($data) {
            case 'numbers:root':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $this->numbersMenuText($strings),
                    $this->keyboardFactory->numbersMenu($strings, $backLabel)
                );
                break;
            case 'smm:root':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $strings['menu_purchase'] ?? 'Boosting',
                    $this->keyboardFactory->smmMenu($strings, $backLabel)
                );
                break;
            case 'numbers:usd':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $this->numbersMenuText($strings, $strings['numbers_usd_button'] ?? 'Buy with USD'),
                    $this->keyboardFactory->numbersMenu($strings, $backLabel)
                );
                break;
            case 'numbers:stars':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $this->numbersMenuText($strings, $strings['numbers_stars_button'] ?? 'Buy with Stars'),
                    $this->keyboardFactory->numbersMenu($strings, $backLabel)
                );
                break;
            case 'smm:usd':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $strings['smm_usd_button'] ?? 'Boost with USD',
                    $this->keyboardFactory->smmMenu($strings, $backLabel)
                );
                break;
            case 'smm:stars':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $strings['smm_stars_button'] ?? 'Boost with Stars',
                    $this->keyboardFactory->smmMenu($strings, $backLabel)
                );
                break;
            case 'back':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $strings['main_menu'] ?? 'Main Menu',
                    $this->keyboardFactory->mainMenu($strings, $changeLabel)
                );
                break;
            default:
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $strings['main_menu'] ?? 'Main Menu',
                    $this->keyboardFactory->mainMenu($strings, $changeLabel)
                );
                break;
        }
    }

    private function determineLanguage(int $userId, string $preferred): string
    {
        if (isset($this->languageCache[$userId])) {
            return $this->languages->ensure((string)$this->languageCache[$userId]);
        }

        $code = $this->languages->ensure($preferred);
        $this->cacheLanguage($userId, $code);

        return $code;
    }

    private function cacheLanguage(int $userId, string $languageCode): void
    {
        if (($this->languageCache[$userId] ?? null) === $languageCode) {
            return;
        }

        $this->languageCache[$userId] = $languageCode;
        $this->store->persist('langs', $this->languageCache);
    }

    /**
     * @param array<string, string> $strings
     */
    private function numbersMenuText(array $strings, ?string $headline = null): string
    {
        $title = $headline ?? ($strings['menu_purchase'] ?? 'Numbers');
        $countries = $this->numberCatalog->list();

        if ($countries === []) {
            $fallback = $strings['no_numbers'] ?? 'No numbers available right now.';
            return $title . PHP_EOL . PHP_EOL . $fallback;
        }

        $preview = array_slice($countries, 0, 5);
        $lines = array_map(
            static fn (array $country): string => sprintf(
                '%s (%s) • $%0.2f',
                $country['name'],
                $country['code'],
                $country['price_usd']
            ),
            $preview
        );

        return $title . PHP_EOL . PHP_EOL . implode(PHP_EOL, $lines);
    }

    /**
     * @param array<int, array<int, array<string, string>>> $keyboard
     */
    private function sendMessage(int $chatId, string $text, array $keyboard): void
    {
        $this->telegram->call('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }

    /**
     * @param array<int, array<int, array<string, string>>> $keyboard
     */
    private function editMessage(int $chatId, int $messageId, string $text, array $keyboard): void
    {
        $this->telegram->call('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }
}
