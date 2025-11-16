<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Domain\Localization\LanguageManager;
use App\Domain\Notifications\NotificationService;
use App\Domain\Numbers\NumberCatalogService;
use App\Domain\Numbers\NumberCodeService;
use App\Domain\Numbers\NumberPurchaseService;
use App\Domain\Payments\StarPaymentService;
use App\Domain\Referrals\ReferralService;
use App\Domain\Smm\SmmCatalogService;
use App\Domain\Smm\SmmPurchaseService;
use App\Domain\Settings\ForcedSubscriptionService;
use App\Domain\Settings\SettingsService;
use App\Domain\Support\TicketService;
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
    private SmmCatalogService $smmCatalog;
    private SmmPurchaseService $smmPurchase;
    private StarPaymentService $starPayments;
    private TicketService $ticketService;
    private NotificationService $notifications;
    private ReferralService $referralService;
    private SettingsService $settings;

    /**
     * @var array<int, string>
     */
    private array $languageCache = [];
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $smmFlow = [];
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $ticketFlow = [];
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $adminFlow = [];
    /**
     * @var array<string, bool>
     */
    private array $features = [];

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
        ForcedSubscriptionService $forcedSubscription,
        SmmCatalogService $smmCatalog,
        SmmPurchaseService $smmPurchase,
        StarPaymentService $starPayments,
        TicketService $ticketService,
        NotificationService $notifications,
        ReferralService $referralService,
        SettingsService $settings
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
        $this->smmCatalog = $smmCatalog;
        $this->smmPurchase = $smmPurchase;
        $this->starPayments = $starPayments;
        $this->ticketService = $ticketService;
        $this->notifications = $notifications;
        $this->referralService = $referralService;
        $this->settings = $settings;
        $this->languageCache = $this->store->load('langs', []);
        $this->smmFlow = $this->store->load('smm_flow', []);
        $this->ticketFlow = $this->store->load('support_flow', []);
        $this->adminFlow = $this->store->load('admin_flow', []);
        $this->features = $this->settings->features();
    }

    public function handle(array $update): void
    {
        if (isset($update['pre_checkout_query'])) {
            $this->handlePreCheckoutQuery($update['pre_checkout_query']);
            return;
        }

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

        $userDbId = (int)$userRecord['id'];
        $telegramUserId = (int)$userRecord['telegram_id'];

        $this->refreshFeatures();

        $userLang = $this->languages->ensure($userRecord['language_code'] ?? $languageCode);
        $this->cacheLanguage($userId, $userLang);
        $strings = $this->languages->strings($userLang);
        $changeLabel = $this->languages->label($userLang, 'change_language', 'Change Language');
        $backLabel = $this->languages->label($userLang, 'back', 'Back');

        if (!empty($userRecord['is_banned'])) {
            $this->sendMessage(
                $chatId,
                $strings['user_banned'] ?? 'Access to this bot is restricted.',
                []
            );
            return;
        }

        if (strpos($text, '/start') === 0) {
            $payload = trim(substr($text, 6));
            if ($payload !== '' && $this->referralService->captureFromPayload($userDbId, $payload)) {
                $this->sendMessage(
                    $chatId,
                    $strings['referral_attached'] ?? 'Referral recorded successfully.',
                    []
                );
            }
        }

        if (isset($message['successful_payment'])) {
            $this->handleSuccessfulPayment($message, $userDbId, $strings);
            return;
        }

        if (!$this->enforceSubscription($chatId, (int)$userRecord['telegram_id'], $strings)) {
            return;
        }

        if ($text === '/start' || str_starts_with($text, '/start ')) {
            $this->clearSmmState($userDbId);
            $this->clearTicketState($userDbId);
            $this->sendMessage(
                $chatId,
                $strings['welcome'] ?? 'Welcome',
                $this->keyboardFactory->mainMenu($strings, $changeLabel, [
                    'features' => $this->features,
                    'is_admin' => $this->isAdmin($telegramUserId),
                ])
            );
            return;
        }

        if ($this->isAdmin($telegramUserId) && $this->handleAdminStateInput($chatId, $userDbId, $telegramUserId, $text, $strings)) {
            return;
        }

        if ($this->isAdmin($telegramUserId) && $this->handleAdminTextCommand($chatId, $userDbId, $telegramUserId, $text, $strings)) {
            return;
        }

        if ($this->handleTicketTextInput($chatId, $userDbId, $telegramUserId, $text, $strings)) {
            return;
        }

        if ($this->handleSmmTextInput($chatId, $userDbId, $text, $strings)) {
            return;
        }

        // fallback to help text
        $this->sendMessage(
            $chatId,
            $strings['main_menu'] ?? 'Main Menu',
            $this->keyboardFactory->mainMenu($strings, $changeLabel, [
                'features' => $this->features,
                'is_admin' => $this->isAdmin($telegramUserId),
            ])
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

        $this->refreshFeatures();

        $userLang = $this->languages->ensure($userRecord['language_code'] ?? ($telegramUser['language_code'] ?? 'ar'));
        $this->cacheLanguage($userId, $userLang);
        $strings = $this->languages->strings($userLang);
        $changeLabel = $this->languages->label($userLang, 'change_language', 'Change Language');
        $backLabel = $this->languages->label($userLang, 'back', 'Back');
        $callbackId = $callback['id'] ?? null;
        $userDbId = (int)$userRecord['id'];
        $telegramUserId = (int)$userRecord['telegram_id'];

        if (!empty($userRecord['is_banned'])) {
            $this->answerCallback($callbackId, $strings['user_banned'] ?? 'Access to this bot is restricted.', true);
            return;
        }

        if (!$this->enforceSubscription($chatId, $telegramUserId, $strings, $callbackId)) {
            return;
        }

        $parts = explode(':', $data);
        if (($parts[0] ?? '') === 'admin') {
            $this->handleAdminPanelCallback(
                $chatId,
                $messageId,
                $callbackId,
                $userDbId,
                $telegramUserId,
                $parts,
                $strings
            );
            return;
        }

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

        if (($parts[0] ?? '') === 'smm') {
            $this->handleSmmCallback(
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

        if (($parts[0] ?? '') === 'support') {
            $this->handleSupportCallback(
                $chatId,
                $messageId,
                $callbackId,
                $userDbId,
                $parts,
                $strings,
                $backLabel
            );
            return;
        }

        if (($parts[0] ?? '') === 'ref') {
            $this->handleReferralCallback(
                $chatId,
                $messageId,
                $callbackId,
                $userDbId,
                $parts,
                $strings,
                $backLabel
            );
            return;
        }

        switch ($data) {
            case 'inviteLink':
                $this->handleReferralCallback(
                    $chatId,
                    $messageId,
                    $callbackId,
                    $userDbId,
                    ['ref', 'root'],
                    $strings,
                    $backLabel
                );
                break;
            case 'back':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $strings['main_menu'] ?? 'Main Menu',
                    $this->keyboardFactory->mainMenu($strings, $changeLabel, [
                        'features' => $this->features,
                        'is_admin' => $this->isAdmin($telegramUserId),
                    ])
                );
                break;
            default:
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $strings['main_menu'] ?? 'Main Menu',
                    $this->keyboardFactory->mainMenu($strings, $changeLabel, [
                        'features' => $this->features,
                        'is_admin' => $this->isAdmin($telegramUserId),
                    ])
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
        if (!$this->featureEnabled('numbers')) {
            $this->answerCallback($callbackId, $strings['feature_disabled'] ?? 'This section is disabled.', true);
            return;
        }

        $action = $parts[1] ?? 'root';
        switch ($action) {
            case 'root':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $this->numbersMenuText($strings),
                    $this->keyboardFactory->numbersMenu($strings, $backLabel, $this->starsPaymentsEnabled())
                );
                return;
            case 'usd':
                $this->showNumbersList($chatId, $messageId, $strings, $backLabel, 0);
                return;
            case 'stars':
                if (!$this->starsPaymentsEnabled()) {
                    $this->answerCallback($callbackId, $strings['stars_disabled'] ?? 'Stars option unavailable.', true);
                    return;
                }
                $this->showNumbersStarList($chatId, $messageId, $strings, $backLabel, 0);
                return;
            case 'list':
                $page = max(0, (int)($parts[2] ?? 0));
                $this->showNumbersList($chatId, $messageId, $strings, $backLabel, $page);
                return;
            case 'starslist':
                if (!$this->starsPaymentsEnabled()) {
                    $this->answerCallback($callbackId, $strings['stars_disabled'] ?? 'Stars option unavailable.', true);
                    return;
                }
                $page = max(0, (int)($parts[2] ?? 0));
                $this->showNumbersStarList($chatId, $messageId, $strings, $backLabel, $page);
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
            case 'starscountry':
                if (!$this->starsPaymentsEnabled()) {
                    $this->answerCallback($callbackId, $strings['stars_disabled'] ?? 'Stars option unavailable.', true);
                    return;
                }
                $code = $parts[2] ?? '';
                if ($code === '') {
                    $this->answerCallback($callbackId, $strings['no_numbers'] ?? 'Country unavailable.', true);
                    return;
                }
                $page = max(0, (int)($parts[3] ?? 0));
                $this->showNumberStarDetails($chatId, $messageId, strtoupper($code), $page, $strings, $backLabel);
                return;
            case 'starsbuy':
                if (!$this->starsPaymentsEnabled()) {
                    $this->answerCallback($callbackId, $strings['stars_disabled'] ?? 'Stars option unavailable.', true);
                    return;
                }
                $code = $parts[2] ?? '';
                if ($code === '') {
                    $this->answerCallback($callbackId, $strings['no_numbers'] ?? 'Country unavailable.', true);
                    return;
                }
                $page = max(0, (int)($parts[3] ?? 0));
                $this->handleNumberStarPurchase(
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
            default:
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $this->numbersMenuText($strings),
                    $this->keyboardFactory->numbersMenu($strings, $backLabel, $this->starsPaymentsEnabled())
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
    private function showNumbersStarList(
        int $chatId,
        int $messageId,
        array $strings,
        string $backLabel,
        int $page
    ): void {
        if (!$this->starsPaymentsEnabled()) {
            $this->editMessage(
                $chatId,
                $messageId,
                $strings['stars_disabled'] ?? 'Stars option unavailable.',
                [
                    [
                        ['text' => $backLabel, 'callback_data' => 'numbers:root'],
                    ],
                ]
            );
            return;
        }

        $payload = $this->numbersStarListPayload($strings, $backLabel, $page);
        $this->editMessage($chatId, $messageId, $payload['text'], $payload['keyboard']);
    }

    private function numbersStarListPayload(array $strings, string $backLabel, int $page): array
    {
        $perPage = 6;
        $pagination = $this->numberCatalog->paginate($page, $perPage);
        $items = $pagination['items'];

        $title = $strings['menu_purchase_stars'] ?? 'Buy Accounts (Stars)';
        $text = $title;
        if ($items === []) {
            $text .= PHP_EOL . PHP_EOL . ($strings['no_numbers'] ?? 'No numbers available right now.');
        } else {
            $lines = array_map(
                fn (array $country): string => $this->formatStarCountryLine($country),
                $items
            );
            $text .= PHP_EOL . PHP_EOL . implode(PHP_EOL, $lines);
        }

        $keyboard = [];
        $row = [];
        foreach ($items as $country) {
            $row[] = [
                'text' => sprintf(
                    '%s • $%0.2f ≈ %d⭐️',
                    $country['name'],
                    $country['price_usd'],
                    $this->convertUsdToStars((float)$country['price_usd'])
                ),
                'callback_data' => sprintf('numbers:starscountry:%s:%d', $country['code'], $page),
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
                    'callback_data' => sprintf('numbers:starslist:%d', max(0, $page - 1)),
                ];
            }
            if ($pagination['has_next']) {
                $nav[] = [
                    'text' => $strings['button_next'] ?? 'Next',
                    'callback_data' => sprintf('numbers:starslist:%d', $page + 1),
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
                $this->keyboardFactory->numbersMenu($strings, $backLabel, $this->starsPaymentsEnabled())
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
    private function showNumberStarDetails(
        int $chatId,
        int $messageId,
        string $countryCode,
        int $page,
        array $strings,
        string $backLabel
    ): void {
        if (!$this->starsPaymentsEnabled()) {
            $this->answerCallback(null, $strings['stars_disabled'] ?? 'Stars option unavailable.', true);
            return;
        }

        $country = $this->numberCatalog->find($countryCode);
        if (!$country) {
            $this->editMessage(
                $chatId,
                $messageId,
                $this->numbersMenuText($strings),
                $this->keyboardFactory->numbersMenu($strings, $backLabel, $this->starsPaymentsEnabled())
            );
            return;
        }

        $priceUsd = (float)$country['price_usd'];
        $stars = $this->convertUsdToStars($priceUsd);
        $disclaimer = $strings['stars_purchase_disclaimer'] ?? 'Price: __p__ USD ≈ __s__⭐️';
        $text = str_replace(
            ['__c__', '__p__', '__s__'],
            [$this->esc($country['name']), number_format($priceUsd, 2), (string)$stars],
            $disclaimer
        );

        $keyboard = [
            [
                [
                    'text' => $strings['stars_invoice_button'] ?? 'Pay with Stars',
                    'callback_data' => sprintf('numbers:starsbuy:%s:%d', $country['code'], $page),
                ],
            ],
            [
                [
                    'text' => $backLabel,
                    'callback_data' => sprintf('numbers:starslist:%d', $page),
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
    private function handleNumberStarPurchase(
        int $chatId,
        int $messageId,
        ?string $callbackId,
        int $userDbId,
        int $telegramUserId,
        string $countryCode,
        int $page,
        array $strings
    ): void {
        if (!$this->starsPaymentsEnabled()) {
            $this->answerCallback($callbackId, $strings['stars_disabled'] ?? 'Stars option unavailable.', true);
            return;
        }

        $country = $this->numberCatalog->find($countryCode);
        if (!$country) {
            $this->answerCallback($callbackId, $strings['no_numbers'] ?? 'Country unavailable.', true);
            return;
        }

        try {
            $invoice = $this->starPayments->createNumberInvoice(
                $userDbId,
                $telegramUserId,
                $country,
                (float)$country['price_usd'],
                $strings
            );
        } catch (Throwable $e) {
            $this->answerCallback($callbackId, $strings['stars_disabled'] ?? 'Stars option unavailable.', true);
            return;
        }

        $priceUsd = number_format((float)$country['price_usd'], 2);
        $text = $strings['stars_invoice_message'] ?? 'Price: __p__ USD ≈ __s__⭐️';
        $text = str_replace(
            ['__c__', '__p__', '__s__'],
            [$this->esc($country['name']), $priceUsd, (string)$invoice['stars']],
            $text
        );

        $keyboard = [
            [
                [
                    'text' => $strings['stars_invoice_button'] ?? 'Pay with Stars',
                    'url' => $invoice['link'],
                ],
            ],
            [
                [
                    'text' => $strings['numbers_stars_button'] ?? 'Buy (Stars)',
                    'callback_data' => sprintf('numbers:starslist:%d', $page),
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

    private function formatStarCountryLine(array $country): string
    {
        return sprintf(
            '%s (%s) • $%0.2f ≈ %d⭐️',
            $this->esc($country['name']),
            $this->esc($country['code']),
            $country['price_usd'],
            $this->convertUsdToStars((float)$country['price_usd'])
        );
    }

    private function convertUsdToStars(float $price): int
    {
        $rate = $this->starPayments->usdPerStar();
        if ($rate <= 0) {
            return (int)$price;
        }

        return (int)max(1, ceil($price / $rate));
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

        $payload = $this->numberPurchaseSuccessPayload($strings, $country, $order, $page);
        $this->editMessage($chatId, $messageId, $payload['text'], $payload['keyboard']);
        $this->referralService->handleSuccessfulOrder(
            $userDbId,
            (float)$order['price_usd'],
            sprintf('number:%d', $order['id'])
        );
        $this->answerCallback($callbackId, '✅');
    }

    /**
     * @param array<string, string> $strings
     * @return array{text: string, keyboard: array<int, array<int, array<string, mixed>>>}
     */
    private function numberPurchaseSuccessPayload(
        array $strings,
        array $country,
        array $order,
        int $page,
        string $mode = 'usd'
    ): array
    {
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
        ];

        if ($mode === 'stars') {
            $keyboard[] = [
                [
                    'text' => $strings['numbers_stars_button'] ?? 'Buy (Stars)',
                    'callback_data' => sprintf('numbers:starslist:%d', $page),
                ],
            ];
        } else {
            $keyboard[] = [
                [
                    'text' => $strings['numbers_usd_button'] ?? 'Buy (USD)',
                    'callback_data' => sprintf('numbers:list:%d', $page),
                ],
            ];
        }

        $keyboard[] = [
            [
                'text' => $strings['main_menu'] ?? 'Main Menu',
                'callback_data' => 'back',
            ],
        ];

        return [
            'text' => $text,
            'keyboard' => $keyboard,
        ];
    }

    /**
     * @param array<string, string> $strings
     */
    private function handleSmmCallback(
        int $chatId,
        int $messageId,
        ?string $callbackId,
        int $userDbId,
        int $telegramUserId,
        array $parts,
        array $strings,
        string $backLabel
    ): void {
        if (!$this->featureEnabled('smm')) {
            $this->answerCallback($callbackId, $strings['feature_disabled'] ?? 'This section is disabled.', true);
            return;
        }

        $action = $parts[1] ?? 'root';
        switch ($action) {
            case 'root':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $strings['main_smm_button'] ?? 'Boosting Section',
                    $this->keyboardFactory->smmMenu($strings, $backLabel, $this->starsPaymentsEnabled())
                );
                return;
            case 'usd':
                $this->setSmmPaymentMethod($userDbId, 'usd');
                $this->showSmmCategories($chatId, $messageId, $strings, $backLabel, 'usd');
                return;
            case 'stars':
                if (!$this->starsPaymentsEnabled()) {
                    $this->answerCallback($callbackId, $strings['stars_disabled'] ?? 'Stars option unavailable.', true);
                    return;
                }
                $this->setSmmPaymentMethod($userDbId, 'stars');
                $this->showSmmCategories($chatId, $messageId, $strings, $backLabel, 'stars');
                return;
            case 'cat':
                $categoryId = (int)($parts[2] ?? 0);
                $mode = $this->getSmmState($userDbId)['payment_method'] ?? 'usd';
                $this->showSmmServices($chatId, $messageId, $categoryId, $strings, $backLabel, $callbackId, $mode);
                return;
            case 'serv':
                $serviceId = (int)($parts[2] ?? 0);
                $categoryId = (int)($parts[3] ?? 0);
                $mode = $this->getSmmState($userDbId)['payment_method'] ?? 'usd';
                $this->showSmmServiceDetails($chatId, $messageId, $serviceId, $categoryId, $strings, $backLabel, $callbackId, $mode);
                return;
            case 'link':
                $serviceId = (int)($parts[2] ?? 0);
                $categoryId = (int)($parts[3] ?? 0);
                $this->startSmmLinkCapture($chatId, $userDbId, $serviceId, $categoryId, $strings, $callbackId);
                return;
            case 'confirm':
                $serviceId = (int)($parts[2] ?? 0);
                $this->completeSmmOrder(
                    $chatId,
                    $messageId,
                    $callbackId,
                    $userDbId,
                    $telegramUserId,
                    $serviceId,
                    $strings
                );
                return;
            case 'cancel':
                $this->clearSmmState($userDbId);
                $this->sendMessage($chatId, $strings['smm_input_cancelled'] ?? 'Operation cancelled.', []);
                $this->answerCallback($callbackId, '✅');
                return;
            default:
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $strings['main_smm_button'] ?? 'Boosting Section',
                    $this->keyboardFactory->smmMenu($strings, $backLabel, $this->starsPaymentsEnabled())
                );
                return;
        }
    }

    /**
     * @param array<string, string> $strings
     */
    private function handleSupportCallback(
        int $chatId,
        int $messageId,
        ?string $callbackId,
        int $userDbId,
        array $parts,
        array $strings,
        string $backLabel
    ): void {
        if (!$this->featureEnabled('support')) {
            $this->answerCallback($callbackId, $strings['feature_disabled'] ?? 'This section is disabled.', true);
            return;
        }

        $action = $parts[1] ?? 'root';
        switch ($action) {
            case 'root':
                $this->editMessage(
                    $chatId,
                    $messageId,
                    $strings['support_intro'] ?? 'Need help? Open a ticket and we will assist you shortly.',
                    [
                        [
                            [
                                'text' => $strings['support_new_ticket_button'] ?? 'Open Ticket',
                                'callback_data' => 'support:new',
                            ],
                            [
                                'text' => $strings['support_my_tickets_button'] ?? 'My Tickets',
                                'callback_data' => 'support:list',
                            ],
                        ],
                        [
                            ['text' => $backLabel, 'callback_data' => 'back'],
                        ],
                    ]
                );
                return;
            case 'new':
                $this->setTicketState($userDbId, [
                    'state' => 'await_subject',
                    'role' => 'user',
                ]);
                $this->answerCallback($callbackId, '✍️');
                $this->sendMessage(
                    $chatId,
                    $strings['support_ticket_subject_prompt'] ?? 'Send the subject for your ticket.',
                    []
                );
                return;
            case 'list':
                $this->showUserTickets($chatId, $messageId, $userDbId, $strings, $backLabel);
                return;
            case 'view':
                $ticketId = (int)($parts[2] ?? 0);
                if ($ticketId <= 0) {
                    $this->answerCallback($callbackId, $strings['support_ticket_list_empty'] ?? 'No tickets found.', true);
                    return;
                }
                $this->showTicketConversation(
                    $chatId,
                    $messageId,
                    $ticketId,
                    $strings,
                    $backLabel,
                    'user',
                    $userDbId
                );
                return;
            case 'reply':
                $ticketId = (int)($parts[2] ?? 0);
                if ($ticketId <= 0) {
                    $this->answerCallback($callbackId, '❌', true);
                    return;
                }
                $ticket = $this->ticketService->find($ticketId);
                if (!$ticket || (int)$ticket['user_id'] !== $userDbId) {
                    $this->answerCallback($callbackId, '❌', true);
                    return;
                }
                $this->setTicketState($userDbId, [
                    'state' => 'await_reply',
                    'ticket_id' => $ticketId,
                    'role' => 'user',
                ]);
                $this->answerCallback($callbackId, '✍️');
                $this->sendMessage(
                    $chatId,
                    $strings['support_ticket_reply_prompt'] ?? 'Please type your reply.',
                    []
                );
                return;
            case 'close':
                $ticketId = (int)($parts[2] ?? 0);
                if ($ticketId <= 0) {
                    $this->answerCallback($callbackId, '❌', true);
                    return;
                }
                $ticket = $this->ticketService->find($ticketId);
                if (!$ticket || (int)$ticket['user_id'] !== $userDbId) {
                    $this->answerCallback($callbackId, '❌', true);
                    return;
                }
                $this->ticketService->updateStatus($ticketId, 'closed');
                $this->notifications->notifyTicketUpdate($ticket, 'Ticket closed by user.', 'user');
                $this->clearTicketState($userDbId);
                $this->answerCallback($callbackId, '✅');
                $this->sendMessage(
                    $chatId,
                    $strings['support_ticket_closed'] ?? 'Ticket closed. Thank you!',
                    []
                );
                return;
            default:
                $this->answerCallback($callbackId, '❔', true);
                return;
        }
    }

    /**
     * @param array<string, string> $strings
     */
    private function showUserTickets(
        int $chatId,
        int $messageId,
        int $userDbId,
        array $strings,
        string $backLabel
    ): void {
        $tickets = $this->ticketService->userTickets($userDbId, 10);
        $text = $strings['support_ticket_list_title'] ?? 'Your tickets';

        $keyboard = [];
        if ($tickets === []) {
            $text .= PHP_EOL . PHP_EOL . ($strings['support_ticket_list_empty'] ?? 'No tickets yet.');
        } else {
            foreach ($tickets as $ticket) {
                $status = strtoupper((string)$ticket['status']);
                $label = sprintf(
                    '#%d • %s • %s',
                    $ticket['id'],
                    $ticket['subject'] ?? '-',
                    $status
                );
                $keyboard[] = [
                    [
                        'text' => $label,
                        'callback_data' => sprintf('support:view:%d', $ticket['id']),
                    ],
                ];
            }
        }

        $keyboard[] = [
            ['text' => $strings['support_new_ticket_button'] ?? 'Open Ticket', 'callback_data' => 'support:new'],
        ];
        $keyboard[] = [
            ['text' => $backLabel, 'callback_data' => 'support:root'],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    /**
     * @param array<string, string> $strings
     */
    private function handleAdminPanelCallback(
        int $chatId,
        int $messageId,
        ?string $callbackId,
        int $userDbId,
        int $telegramUserId,
        array $parts,
        array $strings
    ): void {
        if (!$this->isAdmin($telegramUserId)) {
            $this->answerCallback($callbackId, $strings['admin_only'] ?? 'Admins only.', true);
            return;
        }

        $section = $parts[1] ?? 'root';
        switch ($section) {
            case 'root':
                $this->showAdminMenu($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
                return;
            case 'tickets':
                $this->handleAdminTicketCallback(
                    $chatId,
                    $messageId,
                    $callbackId,
                    $telegramUserId,
                    $userDbId,
                    $parts,
                    $strings
                );
                return;
            case 'features':
                if (($parts[2] ?? '') === 'toggle') {
                    $this->toggleFeatureFlag($parts[3] ?? '', $strings);
                    $this->answerCallback($callbackId, '✅');
                }
                $this->showAdminFeaturesPanel($chatId, $messageId, $strings);
                return;
            case 'stars':
                $action = $parts[2] ?? 'panel';
                if ($action === 'toggle') {
                    $this->toggleStarsPayments($strings);
                    $this->answerCallback($callbackId, '✅');
                    $this->showAdminStarsPanel($chatId, $messageId, $strings);
                    return;
                }
                if ($action === 'setprice') {
                    $this->setAdminState($userDbId, ['state' => 'await_star_price']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage(
                        $chatId,
                        $strings['admin_prompt_star_price'] ?? 'Send the new USD price per single star.',
                        []
                    );
                    return;
                }
                $this->showAdminStarsPanel($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
                return;
            case 'forcesub':
                $action = $parts[2] ?? 'panel';
                if ($action === 'toggle') {
                    $this->toggleForcedSubscription($strings);
                    $this->answerCallback($callbackId, '✅');
                    $this->showForcedSubscriptionPanel($chatId, $messageId, $strings);
                    return;
                }
                if ($action === 'setlink') {
                    $this->setAdminState($userDbId, ['state' => 'await_force_link']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage(
                        $chatId,
                        $strings['admin_forcesub_link_prompt'] ?? 'Send the fallback link/channel invite.',
                        []
                    );
                    return;
                }
                if ($action === 'setchannel') {
                    $this->setAdminState($userDbId, ['state' => 'await_force_channel']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage(
                        $chatId,
                        $strings['admin_forcesub_channel_prompt'] ?? 'Send channel ID and link in the format ID|https://t.me/... .',
                        []
                    );
                    return;
                }
                $this->showForcedSubscriptionPanel($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
                return;
            case 'referrals':
                if (($parts[2] ?? '') === 'toggle') {
                    $this->toggleReferralsEnabled($strings);
                    $this->answerCallback($callbackId, '✅');
                    $this->showAdminReferralsPanel($chatId, $messageId, $strings);
                    return;
                }
                $this->showAdminReferralsPanel($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
                return;
            case 'users':
                $action = $parts[2] ?? 'panel';
                if (in_array($action, ['ban', 'unban'], true)) {
                    $userId = (int)($parts[3] ?? 0);
                    if ($userId > 0) {
                        $this->userManager->setBanStatus($userId, $action === 'ban');
                        $statusLabel = $action === 'ban' ? 'banned' : 'unbanned';
                        $this->logAdminAction(sprintf('User #%d %s', $userId, $statusLabel));
                    }
                    $this->answerCallback($callbackId, '✅');
                    return;
                }
                $this->showAdminUsersPanel($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
                return;
            default:
                $this->showAdminMenu($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
                return;
        }
    }

    private function showAdminMenu(int $chatId, int $messageId, array $strings): void
    {
        $text = $strings['admin_panel_title'] ?? 'Admin Panel';
        $keyboard = [
            [
                [
                    'text' => $strings['admin_section_tickets'] ?? 'Tickets',
                    'callback_data' => 'admin:tickets:list',
                ],
                [
                    'text' => $strings['admin_section_users'] ?? 'Users',
                    'callback_data' => 'admin:users',
                ],
            ],
            [
                [
                    'text' => $strings['admin_section_features'] ?? 'Features',
                    'callback_data' => 'admin:features',
                ],
                [
                    'text' => $strings['admin_section_stars'] ?? 'Stars',
                    'callback_data' => 'admin:stars',
                ],
            ],
            [
                [
                    'text' => $strings['admin_section_forcesub'] ?? 'Forced Subscription',
                    'callback_data' => 'admin:forcesub',
                ],
                [
                    'text' => $strings['admin_section_referrals'] ?? 'Referrals',
                    'callback_data' => 'admin:referrals',
                ],
            ],
            [
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'back'],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function showAdminFeaturesPanel(int $chatId, int $messageId, array $strings): void
    {
        $labels = [
            'numbers' => $strings['main_numbers_button'] ?? 'Numbers',
            'smm' => $strings['main_smm_button'] ?? 'Boosting',
            'support' => $strings['menu_support'] ?? 'Support',
            'referrals' => $strings['menu_free_balance'] ?? 'Referrals',
            'stars' => $strings['numbers_stars_button'] ?? 'Stars',
        ];

        $text = $strings['admin_features_title'] ?? 'Feature Toggles';
        $keyboard = [];
        foreach ($labels as $key => $label) {
            $enabled = $this->featureEnabled($key);
            $keyboard[] = [
                [
                    'text' => sprintf('%s: %s', $label, $enabled ? 'ON' : 'OFF'),
                    'callback_data' => sprintf('admin:features:toggle:%s', $key),
                ],
            ];
        }
        $keyboard[] = [
            ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:root'],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function showAdminStarsPanel(int $chatId, int $messageId, array $strings): void
    {
        $config = $this->settings->stars();
        $text = ($strings['admin_stars_title'] ?? 'Stars Payments') . PHP_EOL;
        $text .= sprintf(
            "%s: %s\n%s: %0.4f",
            $strings['admin_stars_enabled_label'] ?? 'Enabled',
            ($config['enabled'] ?? true) ? 'ON' : 'OFF',
            $strings['admin_stars_price_label'] ?? 'USD per Star',
            (float)($config['usd_per_star'] ?? 0.011)
        );

        $keyboard = [
            [
                [
                    'text' => $strings['admin_stars_toggle_button'] ?? 'Toggle',
                    'callback_data' => 'admin:stars:toggle',
                ],
                [
                    'text' => $strings['admin_stars_set_price_button'] ?? 'Set Price',
                    'callback_data' => 'admin:stars:setprice',
                ],
            ],
            [
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:root'],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function showForcedSubscriptionPanel(int $chatId, int $messageId, array $strings): void
    {
        $config = $this->settings->forcedSubscription();
        $channel = $config['channels'][0] ?? null;
        $text = ($strings['admin_forcesub_title'] ?? 'Forced Subscription') . PHP_EOL;
        $text .= sprintf(
            "%s: %s\nID: %s\nLink: %s",
            $strings['admin_stars_enabled_label'] ?? 'Enabled',
            ($config['enabled'] ?? false) ? 'ON' : 'OFF',
            $channel['id'] ?? '-',
            $channel['link'] ?? ($config['fallback_link'] ?? '-')
        );

        $keyboard = [
            [
                [
                    'text' => $strings['admin_forcesub_toggle_button'] ?? 'Toggle',
                    'callback_data' => 'admin:forcesub:toggle',
                ],
            ],
            [
                [
                    'text' => $strings['admin_forcesub_set_link_button'] ?? 'Set Link',
                    'callback_data' => 'admin:forcesub:setlink',
                ],
                [
                    'text' => $strings['admin_forcesub_set_channel_button'] ?? 'Set Channel',
                    'callback_data' => 'admin:forcesub:setchannel',
                ],
            ],
            [
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:root'],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function showAdminReferralsPanel(int $chatId, int $messageId, array $strings): void
    {
        $config = $this->settings->referrals();
        $text = ($strings['referral_title'] ?? 'Referral Program') . PHP_EOL;
        $text .= sprintf(
            "%s: %s\n%s",
            $strings['admin_stars_enabled_label'] ?? 'Enabled',
            ($config['enabled'] ?? false) ? 'ON' : 'OFF',
            $strings['admin_referrals_hint'] ?? 'Use /referrals <telegram_id> to review a partner.'
        );

        $keyboard = [
            [
                [
                    'text' => $strings['admin_referrals_toggle_button'] ?? 'Toggle',
                    'callback_data' => 'admin:referrals:toggle',
                ],
            ],
            [
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:root'],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function showAdminUsersPanel(int $chatId, int $messageId, array $strings): void
    {
        $text = $strings['admin_users_title'] ?? "User Controls\nUse /user <telegram_id> to inspect a profile.\nUse /ban <telegram_id> or /unban <telegram_id>.";
        $keyboard = [
            [
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:root'],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    /**
     * @param array<string, string> $strings
     */
    private function showTicketConversation(
        int $chatId,
        int $messageId,
        int $ticketId,
        array $strings,
        string $backLabel,
        string $role,
        ?int $userDbId = null
    ): void {
        $ticket = $this->ticketService->find($ticketId);
        if (!$ticket) {
            $this->editMessage($chatId, $messageId, $strings['support_ticket_list_empty'] ?? 'Ticket not found.', [
                [
                    ['text' => $backLabel, 'callback_data' => $role === 'admin' ? 'admin:tickets:list' : 'support:list'],
                ],
            ]);
            return;
        }

        if ($role === 'user' && $userDbId !== null && (int)$ticket['user_id'] !== $userDbId) {
            $this->editMessage($chatId, $messageId, $strings['support_ticket_list_empty'] ?? 'Ticket not found.', [
                [
                    ['text' => $backLabel, 'callback_data' => 'support:list'],
                ],
            ]);
            return;
        }

        $messages = $this->ticketService->messages($ticketId, 15);
        $text = $this->formatTicketConversation($ticket, $messages, $strings);

        $keyboard = [];
        if ($role === 'user') {
            $keyboard[] = [
                [
                    'text' => $strings['support_ticket_reply_button'] ?? 'Reply',
                    'callback_data' => sprintf('support:reply:%d', $ticketId),
                ],
            ];
            if (($ticket['status'] ?? '') !== 'closed') {
                $keyboard[0][] = [
                    'text' => $strings['support_ticket_close_button'] ?? 'Close',
                    'callback_data' => sprintf('support:close:%d', $ticketId),
                ];
            }
            $keyboard[] = [
                ['text' => $backLabel, 'callback_data' => 'support:list'],
            ];
        } else {
            $keyboard[] = [
                [
                    'text' => $strings['support_ticket_reply_button'] ?? 'Reply',
                    'callback_data' => sprintf('admin:tickets:reply:%d', $ticketId),
                ],
            ];
            if (($ticket['status'] ?? '') !== 'closed') {
                $keyboard[0][] = [
                    'text' => $strings['support_ticket_close_button'] ?? 'Close',
                    'callback_data' => sprintf('admin:tickets:close:%d', $ticketId),
                ];
            }
            $keyboard[] = [
                ['text' => $backLabel, 'callback_data' => 'admin:tickets:list'],
            ];
        }

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    /**
     * @param array<int, array<string, mixed>> $messages
     * @param array<string, string> $strings
     */
    private function formatTicketConversation(array $ticket, array $messages, array $strings): string
    {
        $statusLabel = strtoupper((string)($ticket['status'] ?? 'open'));
        $header = sprintf(
            "%s #%d\n%s: %s\n%s: %s",
            $strings['support_ticket_header'] ?? 'Ticket',
            $ticket['id'],
            $strings['support_ticket_status_label'] ?? 'Status',
            $statusLabel,
            $strings['support_ticket_subject_label'] ?? 'Subject',
            $this->esc($ticket['subject'] ?? '-')
        );

        $body = [];
        foreach ($messages as $message) {
            $sender = ($message['sender_type'] ?? '') === 'admin'
                ? ($strings['support_admin_label'] ?? 'Admin')
                : ($strings['support_user_label'] ?? 'You');
            $body[] = sprintf(
                "%s:\n%s",
                $sender,
                $this->esc((string)$message['message'])
            );
        }

        return $header . PHP_EOL . PHP_EOL . implode(PHP_EOL . '---' . PHP_EOL, $body);
    }

    private function showSmmCategories(
        int $chatId,
        int $messageId,
        array $strings,
        string $backLabel,
        string $mode = 'usd'
    ): void
    {
        $categories = $this->smmCatalog->categories();
        $text = $mode === 'stars'
            ? ($strings['smm_stars_button'] ?? 'Boost (Stars)')
            : ($strings['smm_select_category'] ?? 'Select a category.');

        if ($categories === []) {
            $this->editMessage($chatId, $messageId, $text . PHP_EOL . ($strings['no_numbers'] ?? 'No data.'), [
                [
                    ['text' => $backLabel, 'callback_data' => 'back'],
                ],
            ]);
            return;
        }

        $keyboard = [];
        $row = [];
        foreach ($categories as $category) {
            $row[] = [
                'text' => $category['name'],
                'callback_data' => sprintf('smm:cat:%d', $category['id']),
            ];
            if (count($row) === 2) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        if ($row !== []) {
            $keyboard[] = $row;
        }
        $keyboard[] = [
            ['text' => $backLabel, 'callback_data' => 'back'],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function showSmmServices(
        int $chatId,
        int $messageId,
        int $categoryId,
        array $strings,
        string $backLabel,
        ?string $callbackId = null,
        string $mode = 'usd'
    ): void {
        $services = $this->smmCatalog->servicesByCategory($categoryId);
        $category = $this->smmCatalog->category($categoryId);
        if (!$category) {
            $this->answerCallback($callbackId, $strings['smm_order_failed'] ?? 'Category unavailable.', true);
            return;
        }

        $label = $mode === 'stars'
            ? ($strings['smm_stars_button'] ?? 'Boost (Stars)')
            : ($strings['smm_select_service'] ?? 'Select a service.');
        $text = $label . PHP_EOL . $category['name'];
        if ($services === []) {
            $text .= PHP_EOL . ($strings['no_numbers'] ?? 'No services available.');
            $keyboard = [
                [
                    ['text' => $backLabel, 'callback_data' => 'smm:root'],
                ],
            ];
            $this->editMessage($chatId, $messageId, $text, $keyboard);
            return;
        }

        $keyboard = [];
        foreach ($services as $service) {
            $keyboard[] = [
                [
                    'text' => sprintf('%s • $%0.2f/1k', $service['name'], $service['rate_per_1k']),
                    'callback_data' => sprintf('smm:serv:%d:%d', $service['id'], $categoryId),
                ],
            ];
        }
        $keyboard[] = [
            ['text' => $backLabel, 'callback_data' => 'smm:root'],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function showSmmServiceDetails(
        int $chatId,
        int $messageId,
        int $serviceId,
        int $categoryId,
        array $strings,
        string $backLabel,
        ?string $callbackId = null,
        string $mode = 'usd'
    ): void {
        $service = $this->smmCatalog->service($serviceId);
        if (!$service) {
            $this->answerCallback($callbackId, $strings['smm_order_failed'] ?? 'Service unavailable.', true);
            return;
        }

        $priceInfo = $strings['smm_price_info'] ?? 'Price/1k: __rate__$ | Min: __min__ | Max: __max__';
        $priceInfo = str_replace(
            ['__rate__', '__min__', '__max__'],
            [
                number_format((float)$service['rate_per_1k'], 2),
                (string)$service['min_quantity'],
                (string)$service['max_quantity'],
            ],
            $priceInfo
        );

        $text = ($strings['smm_service_details'] ?? 'Service details') . PHP_EOL;
        $text .= $service['name'] . PHP_EOL;
        if (!empty($service['description'])) {
            $text .= $service['description'] . PHP_EOL;
        }
        $text .= $priceInfo;
        if ($mode === 'stars') {
            $starsLine = $strings['stars_price_perk'] ?? 'Approx Stars/1k: __s__⭐️';
            $starsLine = str_replace(
                '__s__',
                (string)$this->convertUsdToStars((float)$service['rate_per_1k']),
                $starsLine
            );
            $text .= PHP_EOL . $starsLine;
        }

        $keyboard = [
            [
                [
                    'text' => $strings['smm_continue'] ?? 'Continue',
                    'callback_data' => sprintf('smm:link:%d:%d', $serviceId, $categoryId),
                ],
            ],
            [
                [
                    'text' => $backLabel,
                    'callback_data' => sprintf('smm:cat:%d', $categoryId),
                ],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function startSmmLinkCapture(
        int $chatId,
        int $userDbId,
        int $serviceId,
        int $categoryId,
        array $strings,
        ?string $callbackId
    ): void {
        $service = $this->smmCatalog->service($serviceId);
        if (!$service) {
            $this->answerCallback($callbackId, $strings['smm_order_failed'] ?? 'Service unavailable.', true);
            return;
        }

        $existing = $this->getSmmState($userDbId) ?? [];
        $paymentMethod = $existing['payment_method'] ?? 'usd';

        $this->setSmmState($userDbId, [
            'state' => 'await_link',
            'service_id' => $serviceId,
            'service_name' => $service['name'],
            'category_id' => $categoryId,
            'rate_per_1k' => (float)$service['rate_per_1k'],
            'currency' => $service['currency'],
            'min' => (int)$service['min_quantity'],
            'max' => (int)$service['max_quantity'],
            'link' => null,
            'quantity' => null,
            'price' => null,
            'payment_method' => $paymentMethod,
            'stars_amount' => null,
        ]);

        $this->answerCallback($callbackId, '✅');
        $this->sendMessage($chatId, $strings['smm_enter_link'] ?? 'Send the link.', []);
    }

    private function completeSmmOrder(
        int $chatId,
        int $messageId,
        ?string $callbackId,
        int $userDbId,
        int $telegramUserId,
        int $serviceId,
        array $strings
    ): void {
        $state = $this->getSmmState($userDbId);
        if (!$state || ($state['service_id'] ?? 0) !== $serviceId || ($state['state'] ?? '') !== 'await_confirm') {
            $this->answerCallback($callbackId, $strings['smm_order_failed'] ?? 'Nothing to confirm.', true);
            return;
        }

        $paymentMethod = $state['payment_method'] ?? 'usd';
        $service = $this->smmCatalog->service($serviceId);
        if (!$service) {
            $this->answerCallback($callbackId, $strings['smm_order_failed'] ?? 'Service unavailable.', true);
            return;
        }

        if ($paymentMethod === 'stars') {
            if (!$this->starsPaymentsEnabled()) {
                $this->answerCallback($callbackId, $strings['stars_disabled'] ?? 'Stars option unavailable.', true);
                return;
            }
            try {
                $invoice = $this->starPayments->createSmmInvoice(
                    $userDbId,
                    $telegramUserId,
                    $service,
                    (string)$state['link'],
                    (int)$state['quantity'],
                    (float)$state['price'],
                    $strings
                );
            } catch (Throwable $e) {
                $this->answerCallback($callbackId, $strings['stars_disabled'] ?? 'Stars option unavailable.', true);
                return;
            }

            $text = $strings['stars_invoice_message'] ?? 'Price: __p__ USD ≈ __s__⭐️';
            $text = str_replace(
                ['__c__', '__p__', '__s__'],
                [
                    $this->esc($state['service_name'] ?? $service['name']),
                    number_format((float)$state['price'], 2),
                    (string)$invoice['stars'],
                ],
                $text
            );

            $keyboard = [
                [
                    [
                        'text' => $strings['stars_invoice_button'] ?? 'Pay with Stars',
                        'url' => $invoice['link'],
                    ],
                ],
                [
                    [
                        'text' => $strings['smm_stars_button'] ?? 'Boost (Stars)',
                        'callback_data' => 'smm:stars',
                    ],
                ],
                [
                    [
                        'text' => $strings['main_menu'] ?? 'Main Menu',
                        'callback_data' => 'back',
                    ],
                ],
            ];

            $this->clearSmmState($userDbId);
            $this->editMessage($chatId, $messageId, $text, $keyboard);
            $this->answerCallback($callbackId, '✅');
            return;
        }

        try {
            $order = $this->smmPurchase->purchaseUsd($userDbId, $serviceId, (string)$state['link'], (int)$state['quantity']);
        } catch (Throwable $e) {
            $this->answerCallback($callbackId, $strings['smm_order_failed'] ?? 'Order failed.', true);
            return;
        }

        $this->clearSmmState($userDbId);

        $text = ($strings['smm_order_success'] ?? 'Order placed.') . PHP_EOL;
        $text .= sprintf(
            "%s\n%s",
            $state['service_name'],
            sprintf('ID: %s', $order['provider_order_id'] ?? '-')
        );

        $this->editMessage($chatId, $messageId, $text, [
            [
                ['text' => $strings['smm_usd_button'] ?? 'Boost (USD)', 'callback_data' => 'smm:usd'],
            ],
            [
                ['text' => $strings['main_menu'] ?? 'Main Menu', 'callback_data' => 'back'],
            ],
        ]);

        $this->referralService->handleSuccessfulOrder(
            $userDbId,
            (float)$order['price'],
            sprintf('smm:%d', $order['id'])
        );

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

    private function handleSmmTextInput(
        int $chatId,
        int $userDbId,
        string $text,
        array $strings
    ): bool {
        $state = $this->getSmmState($userDbId);
        if (!$state) {
            return false;
        }

        $trimmed = trim($text);
        if ($trimmed === '') {
            return false;
        }

        if ($trimmed === '/cancel') {
            $this->clearSmmState($userDbId);
            $this->sendMessage($chatId, $strings['smm_input_cancelled'] ?? 'Operation cancelled.', []);
            return true;
        }

        switch ($state['state'] ?? '') {
            case 'await_link':
                $state['link'] = $trimmed;
                $state['state'] = 'await_quantity';
                $this->setSmmState($userDbId, $state);
                $this->sendMessage($chatId, $strings['smm_link_saved'] ?? 'Link saved. Send quantity.', []);
                $message = str_replace(
                    ['__min__', '__max__'],
                    [(string)$state['min'], (string)$state['max']],
                    $strings['smm_enter_quantity'] ?? 'Send quantity between __min__ and __max__.'
                );
                $this->sendMessage($chatId, $message, []);
                return true;
            case 'await_quantity':
                if (!ctype_digit($trimmed)) {
                    $this->sendMessage($chatId, $strings['smm_quantity_invalid'] ?? 'Invalid quantity.', []);
                    return true;
                }
                $quantity = (int)$trimmed;
                $min = (int)$state['min'];
                $max = (int)$state['max'];
                if ($quantity < $min || $quantity > $max) {
                    $this->sendMessage($chatId, $strings['smm_quantity_invalid'] ?? 'Invalid quantity.', []);
                    return true;
                }

                $price = $this->smmCatalog->calculatePrice((float)$state['rate_per_1k'], $quantity);
                $state['quantity'] = $quantity;
                $state['price'] = $price;
                $state['state'] = 'await_confirm';
                if (($state['payment_method'] ?? 'usd') === 'stars') {
                    $state['stars_amount'] = $this->convertUsdToStars($price);
                } else {
                    $state['stars_amount'] = null;
                }
                $this->setSmmState($userDbId, $state);

                $summary = str_replace(
                    ['__service__', '__quantity__', '__price__'],
                    [$state['service_name'], (string)$quantity, number_format($price, 2)],
                    $strings['smm_order_summary'] ?? 'Service: __service__ / Qty: __quantity__ / Price: __price__$'
                );
                if (($state['payment_method'] ?? 'usd') === 'stars') {
                    $summary .= PHP_EOL . str_replace(
                        '__s__',
                        (string)$state['stars_amount'],
                        $strings['stars_price_total'] ?? 'Approx Stars: __s__⭐️'
                    );
                }

                $keyboard = [
                    [
                        [
                            'text' => $strings['smm_confirm_button'] ?? 'Confirm',
                            'callback_data' => sprintf('smm:confirm:%d', $state['service_id']),
                        ],
                        [
                            'text' => $strings['smm_cancel_button'] ?? 'Cancel',
                            'callback_data' => 'smm:cancel',
                        ],
                    ],
                ];

                $this->sendMessage($chatId, $summary, $keyboard);
                return true;
        }

        return false;
    }

    /**
     * @param array<string, string> $strings
     */
    private function handleReferralCallback(
        int $chatId,
        int $messageId,
        ?string $callbackId,
        int $userDbId,
        array $parts,
        array $strings,
        string $backLabel
    ): void {
        if (!$this->featureEnabled('referrals')) {
            $this->answerCallback($callbackId, $strings['feature_disabled'] ?? 'This section is disabled.', true);
            return;
        }

        if (!$this->referralService->isEnabled()) {
            $this->answerCallback($callbackId, $strings['referral_disabled'] ?? 'Referral program is disabled.', true);
            return;
        }

        $action = $parts[1] ?? 'root';
        switch ($action) {
            case 'withdraw':
                $amount = $this->referralService->withdraw($userDbId);
                if ($amount <= 0) {
                    $this->answerCallback($callbackId, $strings['referral_no_rewards'] ?? 'No rewards to withdraw.', true);
                    return;
                }
                $message = str_replace(
                    '__amount__',
                    number_format($amount, 2),
                    $strings['referral_withdraw_success'] ?? 'Transferred __amount__$ to your wallet.'
                );
                $this->answerCallback($callbackId, '✅');
                $this->sendMessage($chatId, $message, []);
                $this->showReferralOverview($chatId, $messageId, $userDbId, $strings, $backLabel);
                return;
            case 'root':
            default:
                $this->showReferralOverview($chatId, $messageId, $userDbId, $strings, $backLabel);
                return;
        }
    }

    /**
     * @param array<string, string> $strings
     */
    private function showReferralOverview(
        int $chatId,
        int $messageId,
        int $userDbId,
        array $strings,
        string $backLabel
    ): void {
        $stats = $this->referralService->stats($userDbId);
        $eligible = (float)($stats['eligible_amount'] ?? 0);
        $text = ($strings['referral_title'] ?? 'Referral Program') . PHP_EOL . PHP_EOL;
        $text .= str_replace(
            ['__link__', '__code__'],
            [$stats['link'] ?? '', $stats['code'] ?? ''],
            $strings['referral_link_label'] ?? "Share link:\n__link__\nCode: __code__"
        );
        $text .= PHP_EOL . PHP_EOL;
        $text .= str_replace(
            ['__invited__', '__pending__', '__eligible__', '__rewarded__'],
            [
                (string)($stats['total'] ?? 0),
                number_format((float)($stats['pending_count'] ?? 0), 0),
                number_format($eligible, 2),
                number_format((float)($stats['rewarded_amount'] ?? 0), 2),
            ],
            $strings['referral_stats'] ?? "Invited: __invited__\nPending: __pending__\nAvailable: __eligible__$\nPaid: __rewarded__$"
        );

        $keyboard = [];
        if ($eligible > 0) {
            $keyboard[] = [
                [
                    'text' => $strings['referral_withdraw_button'] ?? 'Withdraw earnings',
                    'callback_data' => 'ref:withdraw',
                ],
            ];
        }
        $keyboard[] = [
            ['text' => $strings['button_refresh'] ?? 'Refresh', 'callback_data' => 'ref:root'],
        ];
        $keyboard[] = [
            ['text' => $backLabel, 'callback_data' => 'back'],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    /**
     * @param array<string, string> $strings
     */
    private function handleAdminTicketCallback(
        int $chatId,
        int $messageId,
        ?string $callbackId,
        int $telegramUserId,
        int $userDbId,
        array $parts,
        array $strings
    ): void {
        if (!$this->isAdmin($telegramUserId)) {
            $this->answerCallback($callbackId, '🚫', true);
            return;
        }

        $entity = $parts[1] ?? '';
        if ($entity !== 'tickets') {
            $this->answerCallback($callbackId, '❔', true);
            return;
        }

        $action = $parts[2] ?? 'list';
        $arg = $parts[3] ?? null;

        switch ($action) {
            case 'view':
                $ticketId = (int)$arg;
                $this->showTicketConversation(
                    $chatId,
                    $messageId,
                    $ticketId,
                    $strings,
                    $strings['back'] ?? 'Back',
                    'admin'
                );
                return;
            case 'reply':
                $ticketId = (int)$arg;
                $ticket = $this->ticketService->find($ticketId);
                if (!$ticket) {
                    $this->answerCallback($callbackId, '❌', true);
                    return;
                }
                $this->setTicketState($userDbId, [
                    'state' => 'await_admin_reply',
                    'ticket_id' => $ticketId,
                    'role' => 'admin',
                ]);
                $this->answerCallback($callbackId, '✍️');
                $this->sendMessage(
                    $chatId,
                    $strings['support_ticket_reply_prompt'] ?? 'Please type your reply.',
                    []
                );
                return;
            case 'close':
                $ticketId = (int)$arg;
                $ticket = $this->ticketService->find($ticketId);
                if (!$ticket) {
                    $this->answerCallback($callbackId, '❌', true);
                    return;
                }
                $this->ticketService->updateStatus($ticketId, 'closed');
                $this->notifications->notifyTicketUpdate($ticket, 'Ticket closed by admin.', 'admin');
                $this->answerCallback($callbackId, '✅');
                $this->showTicketConversation(
                    $chatId,
                    $messageId,
                    $ticketId,
                    $strings,
                    $strings['back'] ?? 'Back',
                    'admin'
                );
                return;
            case 'list':
            default:
                $this->showAdminTicketList($chatId, $messageId, $strings, $arg);
                $this->answerCallback($callbackId, '✅');
                return;
        }
    }

    /**
     * @param array<string, string> $strings
     */
    private function showAdminTicketList(
        int $chatId,
        ?int $messageId,
        array $strings,
        ?string $statusFilter = null
    ): void {
        $statusFilter = $statusFilter && $statusFilter !== 'all' ? $statusFilter : null;
        $tickets = $this->ticketService->adminTickets($statusFilter, 10);

        $text = ($strings['support_ticket_list_title'] ?? 'Tickets') . PHP_EOL;
        if ($statusFilter) {
            $text .= strtoupper($statusFilter) . PHP_EOL;
        }
        if ($tickets === []) {
            $text .= PHP_EOL . ($strings['support_ticket_list_empty'] ?? 'No tickets found.');
        } else {
            foreach ($tickets as $ticket) {
                $text .= sprintf(
                    "#%d • %s • %s\n",
                    $ticket['id'],
                    $ticket['subject'] ?? '-',
                    strtoupper((string)$ticket['status'])
                );
            }
        }

        $keyboard = [
            [
                ['text' => 'Open', 'callback_data' => 'admin:tickets:list:open'],
                ['text' => 'Pending', 'callback_data' => 'admin:tickets:list:pending'],
                ['text' => 'Closed', 'callback_data' => 'admin:tickets:list:closed'],
            ],
        ];
        foreach ($tickets as $ticket) {
            $keyboard[] = [
                [
                    'text' => sprintf('#%d • %s', $ticket['id'], strtoupper((string)$ticket['status'])),
                    'callback_data' => sprintf('admin:tickets:view:%d', $ticket['id']),
                ],
            ];
        }

        if ($messageId === null) {
            $this->sendMessage($chatId, $text, $keyboard);
        } else {
            $this->editMessage($chatId, $messageId, $text, $keyboard);
        }
    }

    private function handleAdminTextCommand(
        int $chatId,
        int $userDbId,
        int $telegramUserId,
        string $text,
        array $strings
    ): bool {
        if (!$this->isAdmin($telegramUserId)) {
            return false;
        }

        $trimmed = trim($text);
        if ($trimmed === '') {
            return false;
        }

        $parts = preg_split('/\s+/', $trimmed);
        $command = strtolower($parts[0] ?? '');

        switch ($command) {
            case '/tickets':
                if (($parts[1] ?? '') === 'user' && isset($parts[2])) {
                    $this->sendTicketsForUser($chatId, $strings, $parts[2]);
                    return true;
                }
                $status = $parts[1] ?? null;
                $this->showAdminTicketList($chatId, null, $strings, $status);
                return true;
            case '/user':
                if (!isset($parts[1])) {
                    $this->sendMessage($chatId, $strings['admin_user_id_prompt'] ?? 'Usage: /user <telegram_id>', []);
                    return true;
                }
                $this->sendAdminUserSummary($chatId, $strings, $parts[1]);
                return true;
            case '/ban':
            case '/unban':
                if (!isset($parts[1])) {
                    $this->sendMessage($chatId, 'Usage: ' . $command . ' <telegram_id>', []);
                    return true;
                }
                $telegramId = (int)$parts[1];
                $user = $this->userManager->setBanStatusByTelegramId($telegramId, $command === '/ban');
                if ($user) {
                    $this->logAdminAction(sprintf('User #%d (%d) %s', $user['id'], $telegramId, $command === '/ban' ? 'banned' : 'unbanned'));
                    $this->sendMessage($chatId, $strings['admin_user_updated'] ?? 'User updated.', []);
                } else {
                    $this->sendMessage($chatId, $strings['admin_user_not_found'] ?? 'User not found.', []);
                }
                return true;
            case '/referrals':
                if (!isset($parts[1])) {
                    $this->sendMessage($chatId, $strings['admin_user_id_prompt'] ?? 'Usage: /referrals <telegram_id>', []);
                    return true;
                }
                $status = $parts[2] ?? null;
                $this->sendAdminReferralReport($chatId, $strings, $parts[1], $status);
                return true;
            default:
                return false;
        }
    }

    private function handleAdminStateInput(
        int $chatId,
        int $userDbId,
        int $telegramUserId,
        string $text,
        array $strings
    ): bool {
        if (!$this->isAdmin($telegramUserId)) {
            return false;
        }

        $state = $this->getAdminState($userDbId);
        if (!$state) {
            return false;
        }

        $trimmed = trim($text);
        if ($trimmed === '/cancel') {
            $this->clearAdminState($userDbId);
            $this->sendMessage($chatId, $strings['support_input_cancelled'] ?? 'Operation cancelled.', []);
            return true;
        }

        switch ($state['state'] ?? '') {
            case 'await_star_price':
                $value = (float)$trimmed;
                if ($value <= 0) {
                    $this->sendMessage($chatId, $strings['admin_prompt_star_price'] ?? 'Send a value greater than 0.', []);
                    return true;
                }
                $config = $this->settings->stars();
                $config['usd_per_star'] = round($value, 4);
                $this->settings->updateStars($config);
                $this->clearAdminState($userDbId);
                $this->logAdminAction(sprintf('Star price updated to %0.4f USD', $config['usd_per_star']));
                $this->sendMessage($chatId, $strings['admin_star_price_updated'] ?? 'Star price updated.', []);
                return true;
            case 'await_force_link':
                $config = $this->settings->forcedSubscription();
                $config['fallback_link'] = $trimmed;
                $this->settings->updateForcedSubscription($config);
                $this->clearAdminState($userDbId);
                $this->logAdminAction('Forced subscription link updated.');
                $this->sendMessage($chatId, $strings['admin_forcesub_link_updated'] ?? 'Link updated.', []);
                return true;
            case 'await_force_channel':
                $parts = array_map('trim', explode('|', $trimmed, 2));
                $channelId = (int)($parts[0] ?? 0);
                if ($channelId === 0) {
                    $this->sendMessage($chatId, $strings['admin_forcesub_channel_prompt'] ?? 'Send channel ID and link in the format ID|https://t.me/... .', []);
                    return true;
                }
                $link = $parts[1] ?? '';
                $config = $this->settings->forcedSubscription();
                $config['channels'] = [
                    [
                        'id' => $channelId,
                        'link' => $link !== '' ? $link : ($config['fallback_link'] ?? ''),
                    ],
                ];
                $this->settings->updateForcedSubscription($config);
                $this->clearAdminState($userDbId);
                $this->logAdminAction(sprintf('Forced subscription channel set to %d.', $channelId));
                $this->sendMessage($chatId, $strings['admin_forcesub_channel_updated'] ?? 'Channel updated.', []);
                return true;
        }

        return false;
    }

    private function handleTicketTextInput(
        int $chatId,
        int $userDbId,
        int $telegramUserId,
        string $text,
        array $strings
    ): bool {
        $state = $this->getTicketState($userDbId);
        if (!$state) {
            return false;
        }

        $trimmed = trim($text);
        if ($trimmed === '') {
            return true;
        }

        if ($trimmed === '/cancel') {
            $this->clearTicketState($userDbId);
            $this->sendMessage($chatId, $strings['support_input_cancelled'] ?? 'Operation cancelled.', []);
            return true;
        }

        switch ($state['state'] ?? '') {
            case 'await_subject':
                $state['subject'] = $trimmed;
                $state['state'] = 'await_message';
                $this->setTicketState($userDbId, $state);
                $this->sendMessage(
                    $chatId,
                    $strings['support_ticket_message_prompt'] ?? 'Describe your issue.',
                    []
                );
                return true;
            case 'await_message':
                $ticketId = $this->ticketService->open($userDbId, (string)($state['subject'] ?? 'Support'), $trimmed);
                $ticket = $this->ticketService->find($ticketId) ?? [
                    'id' => $ticketId,
                    'subject' => $state['subject'] ?? 'Support',
                    'status' => 'open',
                ];
                $this->notifications->notifyTicketUpdate($ticket, $trimmed, 'user');
                $this->clearTicketState($userDbId);
                $this->sendMessage(
                    $chatId,
                    $strings['support_ticket_created'] ?? 'Ticket created, our team will contact you soon.',
                    []
                );
                return true;
            case 'await_reply':
                $ticketId = (int)($state['ticket_id'] ?? 0);
                $ticket = $this->ticketService->find($ticketId);
                if (!$ticket || (int)$ticket['user_id'] !== $userDbId) {
                    $this->clearTicketState($userDbId);
                    return true;
                }
                $this->ticketService->addMessage($ticketId, 'user', $trimmed);
                $this->notifications->notifyTicketUpdate($ticket, $trimmed, 'user');
                $this->clearTicketState($userDbId);
                $this->sendMessage(
                    $chatId,
                    $strings['support_waiting_for_admin'] ?? 'Reply sent. Please wait for admin response.',
                    []
                );
                return true;
            case 'await_admin_reply':
                $ticketId = (int)($state['ticket_id'] ?? 0);
                $ticket = $this->ticketService->find($ticketId);
                if (!$ticket) {
                    $this->clearTicketState($userDbId);
                    return true;
                }
                $this->ticketService->addMessage($ticketId, 'admin', $trimmed);
                $this->notifications->notifyTicketUpdate($ticket, $trimmed, 'admin');
                $user = $this->userManager->findById((int)$ticket['user_id']);
                if ($user && !empty($user['telegram_id'])) {
                    $this->telegram->call('sendMessage', [
                        'chat_id' => $user['telegram_id'],
                        'text' => $strings['support_admin_reply_notice'] ?? 'Support team replied to your ticket.',
                    ]);
                }
                $this->clearTicketState($userDbId);
                $this->sendMessage(
                    $chatId,
                    $strings['support_admin_reply_sent'] ?? 'Reply sent to user.',
                    []
                );
                return true;
        }

        return false;
    }

    private function sendAdminUserSummary(int $chatId, array $strings, $telegramId): void
    {
        $telegramId = (int)$telegramId;
        if ($telegramId <= 0) {
            $this->sendMessage($chatId, $strings['admin_user_id_prompt'] ?? 'Provide a valid Telegram ID.', []);
            return;
        }

        $user = $this->userManager->findByTelegramId($telegramId);
        if (!$user) {
            $this->sendMessage($chatId, $strings['admin_user_not_found'] ?? 'User not found.', []);
            return;
        }

        $balance = $this->wallets->balance((int)$user['id'], 'USD');
        $statusLabel = !empty($user['is_banned'])
            ? ($strings['admin_user_status_banned'] ?? 'BANNED')
            : ($strings['admin_user_status_active'] ?? 'ACTIVE');

        $text = sprintf(
            "User #%d\nTelegram: %d\nUsername: @%s\nLanguage: %s\nBalance: %0.2f USD\nStatus: %s",
            $user['id'],
            $user['telegram_id'],
            $user['username'] ?? '-',
            $user['language_code'] ?? '-',
            $balance,
            $statusLabel
        );

        $keyboard = [
            [
                [
                    'text' => empty($user['is_banned'])
                        ? ($strings['admin_user_ban_button'] ?? 'Ban User')
                        : ($strings['admin_user_unban_button'] ?? 'Unban User'),
                    'callback_data' => sprintf(
                        'admin:users:%s:%d',
                        empty($user['is_banned']) ? 'ban' : 'unban',
                        $user['id']
                    ),
                ],
            ],
            [
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:root'],
            ],
        ];

        $this->sendMessage($chatId, $text, $keyboard);
    }

    private function sendTicketsForUser(int $chatId, array $strings, $identifier): void
    {
        $telegramId = (int)$identifier;
        if ($telegramId <= 0) {
            $this->sendMessage($chatId, 'Usage: /tickets user <telegram_id>', []);
            return;
        }

        $user = $this->userManager->findByTelegramId($telegramId);
        if (!$user) {
            $this->sendMessage($chatId, $strings['admin_user_not_found'] ?? 'User not found.', []);
            return;
        }

        $tickets = $this->ticketService->userTickets((int)$user['id'], 10);
        $text = sprintf("Tickets for %d", $telegramId) . PHP_EOL;
        if ($tickets === []) {
            $text .= $strings['support_ticket_list_empty'] ?? 'No tickets yet.';
        } else {
            foreach ($tickets as $ticket) {
                $text .= sprintf(
                    "#%d • %s • %s\n",
                    $ticket['id'],
                    $ticket['subject'] ?? '-',
                    strtoupper((string)$ticket['status'])
                );
            }
        }

        $this->sendMessage($chatId, $text, []);
    }

    private function sendAdminReferralReport(int $chatId, array $strings, $identifier, ?string $status = null): void
    {
        $telegramId = (int)$identifier;
        if ($telegramId <= 0) {
            $this->sendMessage($chatId, 'Usage: /referrals <telegram_id>', []);
            return;
        }

        $details = $this->referralService->detailsByTelegram($telegramId, $status);
        if (!$details) {
            $this->sendMessage($chatId, $strings['admin_user_not_found'] ?? 'User not found.', []);
            return;
        }

        $stats = $details['stats'];
        $text = sprintf(
            "Referrals for %d\nInvited: %d\nEligible: %0.2f\nPaid: %0.2f",
            $telegramId,
            $stats['total'] ?? 0,
            $stats['eligible_amount'] ?? 0,
            $stats['rewarded_amount'] ?? 0
        );
        $text .= PHP_EOL . '---' . PHP_EOL;
        foreach ($details['items'] as $item) {
            $text .= sprintf(
                "#%d • user %d • %s • %0.2f USD\n",
                $item['id'],
                $item['referred_user_id'],
                strtoupper((string)$item['status']),
                (float)$item['reward_amount']
            );
        }

        $this->sendMessage($chatId, $text, []);
    }

    private function toggleFeatureFlag(string $feature, array $strings): void
    {
        $allowed = ['numbers', 'smm', 'support', 'referrals', 'stars'];
        if (!in_array($feature, $allowed, true)) {
            return;
        }

        $key = $feature . '_enabled';
        $features = $this->features;
        $features[$key] = !($features[$key] ?? true);
        $this->settings->updateFeatures($features);
        $this->refreshFeatures();
        $this->logAdminAction(sprintf('Feature %s %s', $feature, $features[$key] ? 'enabled' : 'disabled'));
    }

    private function toggleStarsPayments(array $strings): void
    {
        $config = $this->settings->stars();
        $config['enabled'] = !($config['enabled'] ?? true);
        $this->settings->updateStars($config);
        $this->logAdminAction(sprintf('Stars payments %s', ($config['enabled'] ?? true) ? 'enabled' : 'disabled'));
    }

    private function toggleForcedSubscription(array $strings): void
    {
        $config = $this->settings->forcedSubscription();
        $config['enabled'] = !($config['enabled'] ?? false);
        $this->settings->updateForcedSubscription($config);
        $this->logAdminAction(sprintf('Forced subscription %s', ($config['enabled'] ?? false) ? 'enabled' : 'disabled'));
    }

    private function toggleReferralsEnabled(array $strings): void
    {
        $config = $this->settings->referrals();
        $config['enabled'] = !($config['enabled'] ?? false);
        $this->settings->updateReferrals($config);
        $this->logAdminAction(sprintf('Referrals %s', ($config['enabled'] ?? false) ? 'enabled' : 'disabled'));
    }

    private function logAdminAction(string $message): void
    {
        $this->notifications->notifyAdminAction($message);
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

    /**
     * @param array<string, mixed> $query
     */
    private function handlePreCheckoutQuery(array $query): void
    {
        $payload = (string)($query['invoice_payload'] ?? '');
        $queryId = $query['id'] ?? null;

        if (!$queryId) {
            return;
        }

        $record = $payload !== '' ? $this->starPayments->findPending($payload) : null;
        $ok = $record !== null;

        $params = [
            'pre_checkout_query_id' => $queryId,
            'ok' => $ok,
        ];

        if (!$ok) {
            $params['error_message'] = 'Payment request expired. Please initiate again.';
        }

        $this->telegram->call('answerPreCheckoutQuery', $params);
    }

    /**
     * @param array<string, mixed> $message
     * @param array<string, string> $strings
     */
    private function handleSuccessfulPayment(array $message, int $userDbId, array $strings): void
    {
        $success = $message['successful_payment'] ?? null;
        if (!$success) {
            return;
        }

        $payload = (string)($success['invoice_payload'] ?? '');
        if ($payload === '') {
            return;
        }

        $record = $this->starPayments->findPending($payload);
        if (!$record || ($record['status'] ?? 'pending') !== 'pending') {
            return;
        }

        $this->starPayments->markCompleted($record, $success);

        $meta = [];
        if (!empty($record['meta'])) {
            $decoded = json_decode((string)$record['meta'], true);
            if (is_array($decoded)) {
                $meta = $decoded;
            }
        }

        $chatId = (int)($message['chat']['id'] ?? 0);
        if ($chatId === 0) {
            return;
        }

        $targetUserId = (int)($record['user_id'] ?? $userDbId);

        switch ($record['type']) {
            case 'number':
                $this->finalizeStarNumberPayment($chatId, $targetUserId, $record, $meta, $strings);
                break;
            case 'smm':
                $this->finalizeStarSmmPayment($chatId, $targetUserId, $record, $meta, $strings);
                break;
        }
    }

    /**
     * @param array<string, mixed> $record
     * @param array<string, mixed> $meta
     * @param array<string, string> $strings
     */
    private function finalizeStarNumberPayment(
        int $chatId,
        int $userDbId,
        array $record,
        array $meta,
        array $strings
    ): void {
        $countryCode = strtoupper((string)($meta['country_code'] ?? ''));
        if ($countryCode === '') {
            $this->sendMessage($chatId, $strings['purchase_failed'] ?? 'Purchase failed.', []);
            return;
        }

        try {
            $order = $this->numberPurchase->purchaseWithStars(
                $userDbId,
                (int)$record['telegram_user_id'],
                $countryCode,
                (int)$record['stars_amount'],
                (float)$record['price_usd']
            );
        } catch (Throwable $e) {
            $this->sendMessage($chatId, $strings['purchase_failed'] ?? 'Purchase failed.', []);
            return;
        }

        $country = $this->numberCatalog->find($countryCode) ?? [
            'code' => $countryCode,
            'name' => $meta['country_name'] ?? $countryCode,
            'price_usd' => $order['price_usd'],
        ];

        $payload = $this->numberPurchaseSuccessPayload($strings, $country, $order, 0, 'stars');
        $this->sendMessage($chatId, $payload['text'], $payload['keyboard']);
        $this->referralService->handleSuccessfulOrder(
            $userDbId,
            (float)$order['price_usd'],
            sprintf('number:%d', $order['id'])
        );
    }

    /**
     * @param array<string, mixed> $record
     * @param array<string, mixed> $meta
     * @param array<string, string> $strings
     */
    private function finalizeStarSmmPayment(
        int $chatId,
        int $userDbId,
        array $record,
        array $meta,
        array $strings
    ): void {
        $serviceId = (int)($meta['service_id'] ?? 0);
        $service = $this->smmCatalog->service($serviceId);
        if (!$service) {
            $this->sendMessage($chatId, $strings['smm_order_failed'] ?? 'Order failed.', []);
            return;
        }

        try {
            $order = $this->smmPurchase->purchaseWithStars(
                $userDbId,
                $service,
                (string)$meta['link'],
                (int)$meta['quantity'],
                (float)$record['price_usd'],
                (int)$record['stars_amount']
            );
        } catch (Throwable $e) {
            $this->sendMessage($chatId, $strings['smm_order_failed'] ?? 'Order failed.', []);
            return;
        }

        $this->clearSmmState($userDbId);

        $text = ($strings['smm_order_success'] ?? 'Order placed.') . PHP_EOL;
        $text .= sprintf(
            "%s\n%s",
            $service['name'],
            sprintf('ID: %s', $order['provider_order_id'] ?? '-')
        );

        $keyboard = [
            [
                ['text' => $strings['smm_stars_button'] ?? 'Boost (Stars)', 'callback_data' => 'smm:stars'],
            ],
            [
                ['text' => $strings['main_menu'] ?? 'Main Menu', 'callback_data' => 'back'],
            ],
        ];

        $this->sendMessage($chatId, $text, $keyboard);
        $this->referralService->handleSuccessfulOrder(
            $userDbId,
            (float)$order['price'],
            sprintf('smm:%d', $order['id'])
        );
    }

    private function getSmmState(int $userId): ?array
    {
        return $this->smmFlow[$userId] ?? null;
    }

    private function setSmmPaymentMethod(int $userId, string $method): void
    {
        $state = $this->getSmmState($userId) ?? [];
        $state['payment_method'] = $method;
        $state['state'] = 'select_category';
        $this->setSmmState($userId, $state);
    }

    private function setSmmState(int $userId, array $state): void
    {
        $this->smmFlow[$userId] = $state;
        $this->store->persist('smm_flow', $this->smmFlow);
    }

    private function clearSmmState(int $userId): void
    {
        if (isset($this->smmFlow[$userId])) {
            unset($this->smmFlow[$userId]);
            $this->store->persist('smm_flow', $this->smmFlow);
        }
    }

    private function getTicketState(int $userId): ?array
    {
        return $this->ticketFlow[$userId] ?? null;
    }

    private function setTicketState(int $userId, array $state): void
    {
        $this->ticketFlow[$userId] = $state;
        $this->store->persist('support_flow', $this->ticketFlow);
    }

    private function clearTicketState(int $userId): void
    {
        if (isset($this->ticketFlow[$userId])) {
            unset($this->ticketFlow[$userId]);
            $this->store->persist('support_flow', $this->ticketFlow);
        }
    }

    private function isAdmin(int $telegramId): bool
    {
        return in_array($telegramId, $this->settings->admins(), true);
    }

    private function refreshFeatures(): void
    {
        $this->features = $this->settings->features();
    }

    private function featureEnabled(string $feature): bool
    {
        $key = $feature . '_enabled';
        return ($this->features[$key] ?? true) === true;
    }

    private function starsPaymentsEnabled(): bool
    {
        $stars = $this->settings->stars();
        return $this->featureEnabled('stars') && ($stars['enabled'] ?? true) === true;
    }

    private function getAdminState(int $userId): ?array
    {
        return $this->adminFlow[$userId] ?? null;
    }

    private function setAdminState(int $userId, array $state): void
    {
        $this->adminFlow[$userId] = $state;
        $this->store->persist('admin_flow', $this->adminFlow);
    }

    private function clearAdminState(int $userId): void
    {
        if (isset($this->adminFlow[$userId])) {
            unset($this->adminFlow[$userId]);
            $this->store->persist('admin_flow', $this->adminFlow);
        }
    }
}
