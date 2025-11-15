<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Domain\Localization\LanguageManager;
use App\Infrastructure\Storage\JsonStore;
use App\Infrastructure\Telegram\TelegramClient;
use App\Presentation\Keyboard\KeyboardFactory;

class BotKernel
{
    private LanguageManager $languages;
    private JsonStore $store;
    private KeyboardFactory $keyboardFactory;
    private TelegramClient $telegram;

    public function __construct(
        LanguageManager $languages,
        JsonStore $store,
        KeyboardFactory $keyboardFactory,
        TelegramClient $telegram
    ) {
        $this->languages = $languages;
        $this->store = $store;
        $this->keyboardFactory = $keyboardFactory;
        $this->telegram = $telegram;
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
        $userId = (int)($message['from']['id'] ?? 0);
        $text = trim((string)($message['text'] ?? ''));
        $languageCode = (string)($message['from']['language_code'] ?? 'ar');

        if ($chatId === 0 || $userId === 0) {
            return;
        }

        $userLang = $this->resolveLanguage($userId, $languageCode);
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

        $userLang = $this->resolveLanguage($userId, (string)($user['language_code'] ?? 'ar'));
        $strings = $this->languages->strings($userLang);
        $changeLabel = $this->languages->label($userLang, 'change_language', 'Change Language');
        $backLabel = $this->languages->label($userLang, 'back', 'Back');

        switch ($data) {
            case 'numbers:root':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $strings['menu_purchase'] ?? 'Numbers',
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
                    $strings['numbers_usd_button'] ?? 'Buy with USD',
                    $this->keyboardFactory->numbersMenu($strings, $backLabel)
                );
                break;
            case 'numbers:stars':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $strings['numbers_stars_button'] ?? 'Buy with Stars',
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

    private function resolveLanguage(int $userId, string $preferred): string
    {
        $langs = $this->store->load('langs', []);

        if (isset($langs[$userId])) {
            return $this->languages->ensure((string)$langs[$userId]);
        }

        $code = $this->languages->ensure($preferred);
        $langs[$userId] = $code;
        $this->store->persist('langs', $langs);

        return $code;
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
