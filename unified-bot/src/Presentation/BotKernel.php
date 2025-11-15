<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Domain\Localization\LanguageManager;
use App\Domain\Numbers\NumberCatalogService;
use App\Domain\Numbers\NumberCodeService;
use App\Domain\Numbers\NumberPurchaseService;
use App\Domain\Settings\ForcedSubscriptionService;
use App\Domain\Users\UserManager;
use App\Domain\Wallet\WalletService;
use App\Infrastructure\Storage\JsonStore;
use App\Infrastructure\Telegram\TelegramClient;
use App\Presentation\Keyboard\KeyboardFactory;
use RuntimeException;
use Throwable;

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
    private NumberCodeService $numberCodes;
    private ForcedSubscriptionService $forcedSubscription;

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
        NumberPurchaseService $numberPurchase,
        NumberCodeService $numberCodes,
        ForcedSubscriptionService $forcedSubscription
    ) {
        $this->languages = $languages;
        $this->store = $store;
        $this->keyboardFactory = $keyboardFactory;
        $this->telegram = $telegram;
        $this->userManager = $userManager;
        $this->wallets = $wallets;
        $this->numberCatalog = $numberCatalog;
        $this->numberPurchase = $numberPurchase;
        $this->numberCodes = $numberCodes;
        $this->forcedSubscription = $forcedSubscription;
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

        if (!$this->enforceSubscription($chatId, (int)$userRecord['telegram_id'], $strings)) {
            return;
        }

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
        $telegramUser = $callback['from'] ?? [];
        $userId = (int)($telegramUser['id'] ?? 0);

        if ($chatId === 0 || $messageId === 0 || $userId === 0) {
            return;
        }

        $userRecord = $this->userManager->sync([
            'telegram_id' => $userId,
            'language_code' => $telegramUser['language_code'] ?? 'ar',
            'first_name' => $telegramUser['first_name'] ?? null,
            'username' => $telegramUser['username'] ?? null,
        ]);
        $this->wallets->ensure((int)$userRecord['id'], 'USD');

        $userLang = $this->languages->ensure($userRecord['language_code'] ?? ($telegramUser['language_code'] ?? 'ar'));
        $this->cacheLanguage($userId, $userLang);
        $strings = $this->languages->strings($userLang);
        $changeLabel = $this->languages->label($userLang, 'change_language', 'Change Language');
        $backLabel = $this->languages->label($userLang, 'back', 'Back');
        $callbackId = $callback['id'] ?? null;
        $userDbId = (int)$userRecord['id'];
        $telegramUserId = (int)$userRecord['telegram_id'];

        if (!$this->enforceSubscription($chatId, $telegramUserId, $strings, $callbackId)) {
            return;
        }

        $parts = explode(':', $data);
        if (($parts[0] ?? '') === 'numbers') {
            $this->handleNumbersCallback(
                $chatId,
                $messageId,
                $callbackId,
                $userDbId,
                $telegramUserId,
                $parts,
                $strings,
                $backLabel
            );
            return;
        }

        switch ($data) {
            case 'smm:root':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $strings['menu_purchase'] ?? 'Boosting',
                    $this->keyboardFactory->smmMenu($strings, $backLabel)
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

    /**
     * @param array<string, string> $strings
     */
    private function handleNumbersCallback(
        int $chatId,
        int $messageId,
        ?string $callbackId,
        int $userDbId,
        int $telegramUserId,
        array $parts,
        array $strings,
        string $backLabel
    ): void {
        $action = $parts[1] ?? 'root';
        switch ($action) {
            case 'root':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $this->numbersMenuText($strings),
                    $this->keyboardFactory->numbersMenu($strings, $backLabel)
                );
                return;
            case 'usd':
                $this->showNumbersList($chatId, $messageId, $strings, $backLabel, 0);
                return;
            case 'list':
                $page = max(0, (int)($parts[2] ?? 0));
                $this->showNumbersList($chatId, $messageId, $strings, $backLabel, $page);
                return;
            case 'country':
                $code = $parts[2] ?? '';
                if ($code === '') {
                    $this->answerCallback($callbackId, $strings['no_numbers'] ?? 'Country unavailable.', true);
                    return;
                }
                $page = max(0, (int)($parts[3] ?? 0));
                $this->showNumberDetails($chatId, $messageId, strtoupper($code), $page, $strings, $backLabel);
                return;
            case 'buy':
                $code = $parts[2] ?? '';
                if ($code === '') {
                    $this->answerCallback($callbackId, $strings['no_numbers'] ?? 'Country unavailable.', true);
                    return;
                }
                $page = max(0, (int)($parts[3] ?? 0));
                $this->handleNumberPurchaseAction(
                    $chatId,
                    $messageId,
                    $callbackId,
                    $userDbId,
                    $telegramUserId,
                    strtoupper($code),
                    $page,
                    $strings
                );
                return;
            case 'code':
                $orderId = (int)($parts[2] ?? 0);
                $page = max(0, (int)($parts[3] ?? 0));
                $this->handleNumberCodeAction(
                    $chatId,
                    $messageId,
                    $callbackId,
                    $userDbId,
                    $orderId,
                    $page,
                    $strings
                );
                return;
            case 'stars':
                $this->answerCallback(
                    $callbackId,
                    $strings['stars_disabled'] ?? 'Stars payments are not available right now.',
                    true
                );
                return;
            default:
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $this->numbersMenuText($strings),
                    $this->keyboardFactory->numbersMenu($strings, $backLabel)
                );
        }
    }

    /**
     * @param array<string, string> $strings
     */
    private function showNumbersList(
        int $chatId,
        int $messageId,
        array $strings,
        string $backLabel,
        int $page
    ): void {
        $payload = $this->numbersListPayload($strings, $backLabel, $page);
        $this->editMessage($chatId, $messageId, $payload['text'], $payload['keyboard']);
    }

    /**
     * @param array<string, string> $strings
     */
    private function numbersListPayload(array $strings, string $backLabel, int $page): array
    {
        $perPage = 6;
        $pagination = $this->numberCatalog->paginate($page, $perPage);
        $items = $pagination['items'];

        $text = $this->numbersMenuText($strings, $strings['numbers_usd_button'] ?? 'Buy with USD');
        if ($items === []) {
            $text .= PHP_EOL . PHP_EOL . ($strings['no_numbers'] ?? 'No numbers available right now.');
        } else {
            $lines = array_map(
                fn (array $country): string => $this->formatCountryLine($country),
                $items
            );
            $text .= PHP_EOL . PHP_EOL . implode(PHP_EOL, $lines);
        }

        $keyboard = [];
        $row = [];
        foreach ($items as $country) {
            $row[] = [
                'text' => sprintf('%s • $%0.2f', $country['name'], $country['price_usd']),
                'callback_data' => sprintf('numbers:country:%s:%d', $country['code'], $page),
            ];
            if (count($row) === 2) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        if ($row !== []) {
            $keyboard[] = $row;
        }

        if ($page > 0 || $pagination['has_next']) {
            $nav = [];
            if ($page > 0) {
                $nav[] = [
                    'text' => $strings['button_previous'] ?? 'Previous',
                    'callback_data' => sprintf('numbers:list:%d', max(0, $page - 1)),
                ];
            }
            if ($pagination['has_next']) {
                $nav[] = [
                    'text' => $strings['button_next'] ?? 'Next',
                    'callback_data' => sprintf('numbers:list:%d', $page + 1),
                ];
            }
            if ($nav !== []) {
                $keyboard[] = $nav;
            }
        }

        $keyboard[] = [
            ['text' => $strings['main_numbers_button'] ?? 'Numbers', 'callback_data' => 'numbers:root'],
        ];
        $keyboard[] = [
            ['text' => $strings['main_menu'] ?? 'Main Menu', 'callback_data' => 'back'],
        ];

        return [
            'text' => $text,
            'keyboard' => $keyboard,
        ];
    }

    /**
     * @param array<string, string> $strings
     */
    private function showNumberDetails(
        int $chatId,
        int $messageId,
        string $countryCode,
        int $page,
        array $strings,
        string $backLabel
    ): void {
        $country = $this->numberCatalog->find($countryCode);
        if (!$country) {
            $this->editMessage(
                $chatId,
                $messageId,
                $this->numbersMenuText($strings),
                $this->keyboardFactory->numbersMenu($strings, $backLabel)
            );
            return;
        }

        $text = $this->numberCountryText($strings, $country);
        $keyboard = [
            [
                [
                    'text' => $strings['confirm_purchase'] ?? 'Confirm Purchase',
                    'callback_data' => sprintf('numbers:buy:%s:%d', $country['code'], $page),
                ],
            ],
            [
                [
                    'text' => $backLabel,
                    'callback_data' => sprintf('numbers:list:%d', $page),
                ],
            ],
            [
                [
                    'text' => $strings['main_menu'] ?? 'Main Menu',
                    'callback_data' => 'back',
                ],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    /**
     * @param array<string, string> $strings
     */
    private function numberCountryText(array $strings, array $country): string
    {
        $title = sprintf('%s (%s)', $this->esc($country['name']), $this->esc($country['code']));
        $priceLine = sprintf('%s: $%0.2f', $strings['numbers_usd_button'] ?? 'USD Price', $country['price_usd']);
        $disclaimer = $strings['disclaimer'] ?? 'By confirming you accept the purchase terms.';

        return $title . PHP_EOL . $priceLine . PHP_EOL . PHP_EOL . $disclaimer;
    }

    private function formatCountryLine(array $country): string
    {
        return sprintf(
            '%s (%s) • $%0.2f',
            $this->esc($country['name']),
            $this->esc($country['code']),
            $country['price_usd']
        );
    }

    /**
     * @param array<string, string> $strings
     */
    private function handleNumberPurchaseAction(
        int $chatId,
        int $messageId,
        ?string $callbackId,
        int $userDbId,
        int $telegramUserId,
        string $countryCode,
        int $page,
        array $strings
    ): void {
        $country = $this->numberCatalog->find($countryCode);
        if (!$country) {
            $this->answerCallback($callbackId, $strings['no_numbers'] ?? 'Country unavailable.', true);
            return;
        }

        try {
            $order = $this->numberPurchase->purchaseWithUsd($userDbId, $telegramUserId, $countryCode);
        } catch (RuntimeException $e) {
            $message = $strings['purchase_failed'] ?? 'Purchase could not be completed.';
            if (stripos($e->getMessage(), 'Insufficient balance') !== false) {
                $message = $strings['insufficient_balance'] ?? 'Insufficient balance.';
            }
            $this->answerCallback($callbackId, $message, true);
            return;
        } catch (Throwable $e) {
            $this->answerCallback($callbackId, $strings['purchase_failed'] ?? 'Purchase failed.', true);
            return;
        }

        $successTemplate = $strings['purchase_success'] ?? 'Purchase complete for __c__ number __num__ ($__p__)';
        $countryName = $this->esc($country['name']);
        $numberValue = $this->esc((string)$order['number']);
        $priceValue = number_format((float)$order['price_usd'], 2);
        $text = str_replace(
            ['__c__', '__num__', '__p__'],
            [$countryName, $numberValue, $priceValue],
            $successTemplate
        );

        $text .= PHP_EOL . 'Hash: <code>' . htmlspecialchars((string)$order['hash_code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code>';
        $text .= PHP_EOL . ($strings['request_code'] ?? 'Use the request code option when you are ready.');

        $keyboard = [
            [
                [
                    'text' => $strings['request_code'] ?? 'Request Code',
                    'callback_data' => sprintf('numbers:code:%d:%d', $order['id'], $page),
                ],
            ],
            [
                [
                    'text' => $strings['numbers_usd_button'] ?? 'Buy (USD)',
                    'callback_data' => sprintf('numbers:list:%d', $page),
                ],
            ],
            [
                [
                    'text' => $strings['main_menu'] ?? 'Main Menu',
                    'callback_data' => 'back',
                ],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
        $this->answerCallback($callbackId, '✅');
    }

    /**
     * @param array<string, string> $strings
     */
    private function handleNumberCodeAction(
        int $chatId,
        int $messageId,
        ?string $callbackId,
        int $userDbId,
        int $orderId,
        int $page,
        array $strings
    ): void {
        if ($orderId <= 0) {
            $this->answerCallback($callbackId, $strings['no_numbers'] ?? 'Order not found.', true);
            return;
        }

        try {
            $result = $this->numberCodes->retrieveCode($userDbId, $orderId);
        } catch (RuntimeException $e) {
            $this->answerCallback($callbackId, $e->getMessage(), true);
            return;
        }

        $order = $result['order'];
        $codeData = $result['code'];

        $countryCode = strtoupper((string)$order['country_code']);
        $country = $this->numberCatalog->find($countryCode) ?? [
            'name' => $order['country_code'],
            'code' => $order['country_code'],
        ];

        $template = $strings['code_received'] ?? 'Code: __code__';
        $text = str_replace(
            ['__num__', '__p__', '__c__', '__code__', '__pass__'],
            [
                $this->esc((string)$order['number']),
                number_format((float)$order['price_usd'], 2),
                $this->esc($country['name']),
                $this->esc((string)$codeData['code']),
                $this->esc((string)$codeData['password']),
            ],
            $template
        );

        $keyboard = [
            [
                [
                    'text' => $strings['numbers_usd_button'] ?? 'Buy (USD)',
                    'callback_data' => sprintf('numbers:list:%d', $page),
                ],
            ],
            [
                [
                    'text' => $strings['main_menu'] ?? 'Main Menu',
                    'callback_data' => 'back',
                ],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
        $this->answerCallback($callbackId, '✅');
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
            fn (array $country): string => sprintf(
                '%s (%s) • $%0.2f',
                $this->esc($country['name']),
                $this->esc($country['code']),
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

    private function answerCallback(?string $callbackId, string $text, bool $alert = false): void
    {
        if (!$callbackId) {
            return;
        }

        $this->telegram->call('answerCallbackQuery', [
            'callback_query_id' => $callbackId,
            'text' => $text,
            'show_alert' => $alert,
        ]);
    }

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * @param array<string, mixed> $strings
     */
    private function enforceSubscription(
        int $chatId,
        int $telegramUserId,
        array $strings,
        ?string $callbackId = null
    ): bool {
        $result = $this->forcedSubscription->validate($telegramUserId, $strings);
        if ($result['allowed'] ?? false) {
            return true;
        }

        if ($callbackId) {
            $this->answerCallback($callbackId, $strings['subscription_not_verified'] ?? 'Subscription required.', true);
        }

        $this->telegram->call('sendMessage', [
            'chat_id' => $chatId,
            'text' => $result['message'] ?? ($strings['verify_text'] ?? 'Please join the channel to continue.'),
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => $result['keyboard'] ?? [],
            ]),
        ]);

        return false;
    }
}
