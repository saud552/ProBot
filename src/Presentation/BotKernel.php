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
use App\Domain\Wallet\TransactionService;
use App\Infrastructure\Numbers\SpiderNumberProvider;
use App\Infrastructure\Storage\JsonStore;
use App\Infrastructure\Repository\NumberOrderRepository;
use App\Infrastructure\Repository\SmmOrderRepository;
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
    private TransactionService $transactions;
    private NumberOrderRepository $numberOrders;
    private SmmOrderRepository $smmOrders;
    private SpiderNumberProvider $numberProvider;

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
        SettingsService $settings,
        TransactionService $transactions,
        NumberOrderRepository $numberOrders,
        SmmOrderRepository $smmOrders,
        SpiderNumberProvider $numberProvider
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
        $this->transactions = $transactions;
        $this->numberOrders = $numberOrders;
        $this->smmOrders = $smmOrders;
        $this->numberProvider = $numberProvider;
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

        $maintenance = $this->settings->maintenance();
        if (($maintenance['enabled'] ?? false) && !$this->isAdmin($telegramUserId)) {
            $messageText = $maintenance['message'] ?? ($strings['maintenance_message'] ?? 'Bot is under maintenance.');
            $this->sendMessage($chatId, $messageText, []);
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

        // التحقق من الاشتراك الإجباري (لكن لا نمنع الأدمن)
        if (!$this->isAdmin($telegramUserId) && !$this->enforceSubscription($chatId, (int)$userRecord['telegram_id'], $strings)) {
            return;
        }

        if ($text === '/start' || str_starts_with($text, '/start ')) {
            $this->clearSmmState($userDbId);
            $this->clearTicketState($userDbId);
            $startMessage = $this->buildStartMessage($strings, $userRecord, $telegramUser);
            $this->sendMessage(
                $chatId,
                $startMessage,
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

        $maintenance = $this->settings->maintenance();
        if (($maintenance['enabled'] ?? false) && !$this->isAdmin($telegramUserId)) {
            $messageText = $maintenance['message'] ?? ($strings['maintenance_message'] ?? 'Bot is under maintenance.');
            $this->answerCallback($callbackId, $messageText, true);
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

        if (($parts[0] ?? '') === 'lang') {
            $this->handleLanguageCallback(
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
            case 'requestPoint':
                $this->showRechargeInfo($chatId, $messageId, $userDbId, $strings, $backLabel);
                $this->answerCallback($callbackId, '✅');
                break;
            case 'agents':
                $this->showPublicAgents($chatId, $messageId, $strings, $backLabel);
                $this->answerCallback($callbackId, '✅');
                break;
            case 'activations':
                $this->showActivationsInfo($chatId, $messageId, $userDbId, $strings, $backLabel);
                $this->answerCallback($callbackId, '✅');
                break;
            case 'changeLanguage':
                $this->showLanguageMenu($chatId, $messageId, $strings, $backLabel);
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
        $perPage = 20; // 20 دولة لكل صفحة (10 صفوف، كل صف دولتين)
        $languageCode = $strings['_lang'] ?? null;
        $pagination = $this->numberCatalog->paginate($page, $perPage, $languageCode);
        $items = $pagination['items'];

        // بناء النص الترحيبي
        $text = ($strings['numbers_welcome_title'] ?? '🏆 - أهلاً بك عزيزي في قسم حسابات تيليجرام الجاهزة 💥.') . PHP_EOL . PHP_EOL;
        $text .= ($strings['numbers_welcome_description'] ?? 'من خلال هذا القسم يمكنك شراء حسابات تيليجرام جاهز من أي دولة موجودة في الأسفل وبالسعر الموضح بجانبها بكل سهولة، بأسعار مغرية وضمان تفعيل الرقم ☑️ .') . PHP_EOL . PHP_EOL;
        $text .= ($strings['numbers_welcome_instruction'] ?? '♻️ - يرجى إختيار الدولة التي تريد شراء حساب منها ، جميع الدول بالأسفل متوفر لديها أرقام ☑️ .');

        if ($items === []) {
            $text .= PHP_EOL . PHP_EOL . ($strings['no_numbers'] ?? 'No numbers available right now.');
        }

        // بناء لوحة المفاتيح: 20 دولة (كل دولتين في سطر)
        $keyboard = [];
        $row = [];
        foreach ($items as $country) {
            $name = $this->countryDisplayName($country, $languageCode);
            $row[] = [
                'text' => sprintf('%s %0.2f$', $name, $country['price_usd']),
                'callback_data' => sprintf('numbers:country:%s:%d', $country['code'], $page),
            ];
            if (count($row) === 2) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        // إضافة الصف الأخير إذا كان يحتوي على دولة واحدة فقط
        if ($row !== []) {
            $keyboard[] = $row;
        }

        // أزرار التنقل (التالي/السابق)
        if ($page > 0 || $pagination['has_next']) {
            $nav = [];
            if ($page > 0) {
                $nav[] = [
                    'text' => $strings['button_previous'] ?? 'السابق',
                    'callback_data' => sprintf('numbers:list:%d', max(0, $page - 1)),
                ];
            }
            if ($pagination['has_next']) {
                $nav[] = [
                    'text' => $strings['button_next'] ?? 'التالي',
                    'callback_data' => sprintf('numbers:list:%d', $page + 1),
                ];
            }
            if ($nav !== []) {
                $keyboard[] = $nav;
            }
        }

        // زر الرجوع للقائمة الرئيسية
        $keyboard[] = [
            ['text' => $strings['main_menu'] ?? 'القائمة الرئيسية', 'callback_data' => 'back'],
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
        $perPage = 20; // 20 دولة لكل صفحة (10 صفوف، كل صف دولتين)
        $languageCode = $strings['_lang'] ?? null;
        $pagination = $this->numberCatalog->paginate($page, $perPage, $languageCode);
        $items = $pagination['items'];

        // بناء النص الترحيبي
        $text = ($strings['numbers_stars_welcome_title'] ?? '🏆 - أهلاً بك عزيزي في قسم شراء حسابات تيليجرام بالنجوم 💥.') . PHP_EOL . PHP_EOL;
        $text .= ($strings['numbers_stars_welcome_description'] ?? 'من خلال هذا القسم يمكنك شراء حسابات تيليجرام جاهز من أي دولة موجودة في الأسفل وبالسعر الموضح بجانبها (بالدولار والنجوم) بكل سهولة، بأسعار مغرية وضمان تفعيل الرقم ☑️ .') . PHP_EOL . PHP_EOL;
        $text .= ($strings['numbers_stars_welcome_instruction'] ?? '♻️ - يرجى إختيار الدولة التي تريد شراء حساب منها ، جميع الدول بالأسفل متوفر لديها أرقام ☑️ .');

        if ($items === []) {
            $text .= PHP_EOL . PHP_EOL . ($strings['no_numbers'] ?? 'No numbers available right now.');
        }

        // بناء لوحة المفاتيح: 20 دولة (كل دولتين في سطر)
        $keyboard = [];
        $row = [];
        foreach ($items as $country) {
            $name = $this->countryDisplayName($country, $languageCode);
            $stars = $this->convertUsdToStars((float)$country['price_usd']);
            $row[] = [
                'text' => sprintf('%s %d⭐️', $name, $stars),
                'callback_data' => sprintf('numbers:starscountry:%s:%d', $country['code'], $page),
            ];
            if (count($row) === 2) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        // إضافة الصف الأخير إذا كان يحتوي على دولة واحدة فقط
        if ($row !== []) {
            $keyboard[] = $row;
        }

        // أزرار التنقل (التالي/السابق)
        if ($page > 0 || $pagination['has_next']) {
            $nav = [];
            if ($page > 0) {
                $nav[] = [
                    'text' => $strings['button_previous'] ?? 'السابق',
                    'callback_data' => sprintf('numbers:starslist:%d', max(0, $page - 1)),
                ];
            }
            if ($pagination['has_next']) {
                $nav[] = [
                    'text' => $strings['button_next'] ?? 'التالي',
                    'callback_data' => sprintf('numbers:starslist:%d', $page + 1),
                ];
            }
            if ($nav !== []) {
                $keyboard[] = $nav;
            }
        }

        // زر الرجوع للقائمة الرئيسية
        $keyboard[] = [
            ['text' => $strings['main_menu'] ?? 'القائمة الرئيسية', 'callback_data' => 'back'],
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
        $languageCode = $strings['_lang'] ?? null;
        $country = $this->numberCatalog->find($countryCode, $languageCode);
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

        $languageCode = $strings['_lang'] ?? null;
        $country = $this->numberCatalog->find($countryCode, $languageCode);
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
            [$this->esc($this->countryDisplayName($country)), number_format($priceUsd, 2), (string)$stars],
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

        $languageCode = $strings['_lang'] ?? null;
        $country = $this->numberCatalog->find($countryCode, $languageCode);
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
        $countryName = $this->esc($this->countryDisplayName($country));
        $text = str_replace(['__c__', '__p__', '__s__'], [$countryName, $priceUsd, (string)$invoice['stars']], $text);

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
        $title = sprintf('%s (%s)', $this->esc($this->countryDisplayName($country)), $this->esc($country['code']));
        $priceLine = sprintf('%s: $%0.2f', $strings['numbers_usd_button'] ?? 'USD Price', $country['price_usd']);
        $disclaimer = $strings['disclaimer'] ?? 'By confirming you accept the purchase terms.';

        return $title . PHP_EOL . $priceLine . PHP_EOL . PHP_EOL . $disclaimer;
    }

    private function formatCountryLine(array $country, ?string $languageCode = null): string
    {
        return sprintf(
            '%s • $%0.2f',
            $this->esc($this->countryDisplayName($country, $languageCode)),
            $country['price_usd']
        );
    }

    private function formatStarCountryLine(array $country, ?string $languageCode = null): string
    {
        return sprintf(
            '%s • $%0.2f ≈ %d⭐️',
            $this->esc($this->countryDisplayName($country, $languageCode)),
            $country['price_usd'],
            $this->convertUsdToStars((float)$country['price_usd'])
        );
    }

    private function countryDisplayName(array $country, ?string $languageCode = null): string
    {
        $display = (string)($country['display_name'] ?? '');
        if ($display !== '' && $display !== ($country['code'] ?? '')) {
            return $display;
        }

        // إذا لم يكن هناك display_name أو كان نفس الكود، جرب استخدام الترجمات من الملف
        $code = strtoupper((string)($country['code'] ?? ''));
        
        if ($code !== '') {
            static $countryNames = null;
            if ($countryNames === null) {
                $countryNames = require APP_BASE_PATH . '/lang/country_names.php';
            }
            
            // استخدام اللغة الحالية للمستخدم
            if ($languageCode && isset($countryNames[$languageCode][$code])) {
                return $countryNames[$languageCode][$code];
            }
            
            // جرب العربية كبديل
            if (isset($countryNames['ar'][$code])) {
                return $countryNames['ar'][$code];
            }
            
            // جرب الإنجليزية كبديل
            if (isset($countryNames['en'][$code])) {
                return $countryNames['en'][$code];
            }
        }

        return (string)($country['name'] ?? $code);
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
        $languageCode = $strings['_lang'] ?? null;
        $country = $this->numberCatalog->find($countryCode, $languageCode);
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
        $countryName = $this->esc($this->countryDisplayName($country));
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
            case 'wallet':
                $action = $parts[2] ?? 'panel';
                if ($action === 'credit') {
                    $this->setAdminState($userDbId, ['state' => 'await_wallet_user', 'mode' => 'credit']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage($chatId, $strings['admin_prompt_wallet_user'] ?? 'Send the Telegram ID for the user.', []);
                    return;
                }
                if ($action === 'debit') {
                    $this->setAdminState($userDbId, ['state' => 'await_wallet_user', 'mode' => 'debit']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage($chatId, $strings['admin_prompt_wallet_user'] ?? 'Send the Telegram ID for the user.', []);
                    return;
                }
                if ($action === 'refund') {
                    $this->setAdminState($userDbId, ['state' => 'await_wallet_refund_reference']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage($chatId, $strings['admin_wallet_refund_reference_prompt'] ?? 'Send the order reference as numbers:123 or smm:45.', []);
                    return;
                }
                $this->showAdminWalletPanel($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
                return;
            case 'catalog':
                $section = $parts[2] ?? 'panel';
                if ($section === 'numbers') {
                    $action = $parts[3] ?? 'list';
                    if ($action === 'list') {
                        $page = (int)($parts[4] ?? 0);
                        $this->sendNumberCatalogOverview($chatId, $messageId, $page, $strings);
                        $this->answerCallback($callbackId, '✅');
                        return;
                    }
                    if ($action === 'add') {
                        $this->setAdminState($userDbId, ['state' => 'await_country_payload']);
                        $this->answerCallback($callbackId, '✍️');
                        $this->sendMessage(
                            $chatId,
                            $strings['admin_catalog_country_prompt'] ?? 'Send data as CODE|Name|PriceUSD|ProviderID|Margin% (translations optional JSON).',
                            []
                        );
                        return;
                    }
                    if ($action === 'remove') {
                        $this->setAdminState($userDbId, ['state' => 'await_country_delete']);
                        $this->answerCallback($callbackId, '✍️');
                        $this->sendMessage($chatId, $strings['admin_catalog_remove_prompt'] ?? 'Send the country code to remove.', []);
                        return;
                    }
                    if ($action === 'import') {
                        $this->setAdminState($userDbId, ['state' => 'await_country_import']);
                        $this->answerCallback($callbackId, '✍️');
                        $this->sendMessage(
                            $chatId,
                            $strings['admin_catalog_import_prompt'] ?? 'Send lines in the format CODE PRICE per line.',
                            []
                        );
                        return;
                    }
                    if ($action === 'auto_import') {
                        $this->answerCallback($callbackId, '⏳');
                        try {
                            $countries = $this->numberProvider->getCountries();
                            if (empty($countries)) {
                                $this->sendMessage(
                                    $chatId,
                                    $strings['admin_catalog_auto_import_error'] ?? 'Failed to fetch countries from provider.',
                                    []
                                );
                                return;
                            }
                            // تحميل ترجمات أسماء الدول
                            $countryNames = require APP_BASE_PATH . '/lang/country_names.php';
                            $general = $this->settings->general();
                            $margin = (float)($general['pricing_margin_percent'] ?? 0);
                            $applied = 0;
                            $existingCountries = [];
                            foreach ($this->numberCatalog->allRaw() as $existing) {
                                $existingCountries[strtoupper($existing['code'])] = $existing;
                            }
                            foreach ($countries as $code => $basePrice) {
                                $basePrice = round((float)$basePrice, 2);
                                $existing = $existingCountries[$code] ?? null;
                                
                                // إعداد الترجمات من ملف country_names.php
                                $translations = [];
                                foreach ($countryNames as $lang => $names) {
                                    if (isset($names[$code])) {
                                        $translations[$lang] = $names[$code];
                                    }
                                }
                                
                                // تحديد الاسم الافتراضي (العربي أو الإنجليزي)
                                $defaultName = $translations['ar'] ?? $translations['en'] ?? $code;
                                
                                if ($existing) {
                                    // دمج الترجمات الموجودة مع الجديدة
                                    $existingTranslations = [];
                                    if (isset($existing['name_translations'])) {
                                        if (is_string($existing['name_translations'])) {
                                            $decoded = json_decode($existing['name_translations'], true);
                                            $existingTranslations = is_array($decoded) ? $decoded : [];
                                        } elseif (is_array($existing['name_translations'])) {
                                            $existingTranslations = $existing['name_translations'];
                                        }
                                    }
                                    $mergedTranslations = array_merge($translations, $existingTranslations);
                                    $payload = [
                                        'code' => $code,
                                        'name' => $existing['name'] ?: $defaultName,
                                        'name_translations' => $mergedTranslations,
                                        'price_usd' => $basePrice,
                                        'margin_percent' => $margin,
                                        'provider_id' => $existing['provider_id'] ?? 1,
                                        'is_active' => $existing['is_active'] ?? 1,
                                    ];
                                } else {
                                    $payload = [
                                        'code' => $code,
                                        'name' => $defaultName,
                                        'name_translations' => $translations,
                                        'price_usd' => $basePrice,
                                        'margin_percent' => $margin,
                                        'provider_id' => 1,
                                        'is_active' => 1,
                                    ];
                                }
                                $this->numberCatalog->upsert($payload);
                                $applied++;
                            }
                            $this->sendMessage(
                                $chatId,
                                sprintf(
                                    $strings['admin_catalog_auto_import_done'] ?? 'Successfully imported %d countries with %0.2f%% margin applied.',
                                    $applied,
                                    $margin
                                ),
                                []
                            );
                        } catch (Throwable $e) {
                            $errorMsg = $e->getMessage();
                            // تسجيل الخطأ في السجلات
                            error_log("Auto import error: " . $errorMsg . "\n" . $e->getTraceAsString());
                            $this->sendMessage(
                                $chatId,
                                ($strings['admin_catalog_auto_import_error'] ?? 'Failed to import countries') . ': ' . $errorMsg,
                                []
                            );
                        }
                        return;
                    }
                    if ($action === 'margin') {
                        $this->setAdminState($userDbId, ['state' => 'await_catalog_margin']);
                        $this->answerCallback($callbackId, '✍️');
                        $this->sendMessage(
                            $chatId,
                            $strings['admin_pricing_margin_prompt'] ?? 'أرسل النسبة المئوية للأرباح (مثال 15 أو 12.5).',
                            []
                        );
                        return;
                    }
                }
                $this->showAdminCatalogPanel($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
                return;
            case 'pricing':
                $action = $parts[2] ?? 'panel';
                if ($action === 'margin') {
                    $this->setAdminState($userDbId, ['state' => 'await_pricing_margin']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage(
                        $chatId,
                        $strings['admin_pricing_margin_prompt'] ?? 'Send the global margin percentage (e.g. 15 or 12.5).',
                        []
                    );
                    return;
                }
                if ($action === 'custom') {
                    $this->setAdminState($userDbId, ['state' => 'await_pricing_custom']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage(
                        $chatId,
                        $strings['admin_pricing_custom_prompt'] ?? 'Send the country code and price as: US 1.75',
                        []
                    );
                    return;
                }
                if ($action === 'fee') {
                    $this->setAdminState($userDbId, ['state' => 'await_transfer_fee']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage(
                        $chatId,
                        $strings['admin_transfer_fee_prompt'] ?? 'Send the transfer fee percent (e.g. 2.5).',
                        []
                    );
                    return;
                }
                if ($action === 'min') {
                    $this->setAdminState($userDbId, ['state' => 'await_transfer_min']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage(
                        $chatId,
                        $strings['admin_transfer_min_prompt'] ?? 'Send the minimum transfer amount.',
                        []
                    );
                    return;
                }
                $this->showAdminPricingPanel($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
                return;
            case 'content':
                $action = $parts[2] ?? 'panel';
                if ($action === 'start') {
                    $this->setAdminState($userDbId, ['state' => 'await_general_start']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage($chatId, $strings['admin_content_start_prompt'] ?? 'Send the new start message.', []);
                    return;
                }
                if ($action === 'help') {
                    $this->setAdminState($userDbId, ['state' => 'await_general_help']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage($chatId, $strings['admin_content_help_prompt'] ?? 'Send the help/guide text.', []);
                    return;
                }
                $this->showAdminContentPanel($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
                return;
            case 'agents':
                $action = $parts[2] ?? 'panel';
                if ($action === 'add') {
                    $this->setAdminState($userDbId, ['state' => 'await_agent_add']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage($chatId, $strings['admin_agents_add_prompt'] ?? 'Send as Name|username (username optional).', []);
                    return;
                }
                if ($action === 'remove') {
                    $this->setAdminState($userDbId, ['state' => 'await_agent_remove']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage($chatId, $strings['admin_agents_remove_prompt'] ?? 'Send the agent username or name to remove.', []);
                    return;
                }
                $this->showAdminAgentsPanel($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
                return;
            case 'maintenance':
                $action = $parts[2] ?? 'panel';
                if ($action === 'toggle') {
                    $config = $this->settings->maintenance();
                    $config['enabled'] = !($config['enabled'] ?? false);
                    $this->settings->updateMaintenance($config);
                    $this->logAdminAction(sprintf('Maintenance %s', $config['enabled'] ? 'enabled' : 'disabled'));
                    $this->showAdminMaintenancePanel($chatId, $messageId, $strings);
                    $this->answerCallback($callbackId, '✅');
                    return;
                }
                if ($action === 'message') {
                    $this->setAdminState($userDbId, ['state' => 'await_maintenance_message']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage($chatId, $strings['admin_maintenance_message_prompt'] ?? 'Send the maintenance message.', []);
                    return;
                }
                $this->showAdminMaintenancePanel($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
                return;
            case 'broadcast':
                $this->promptBroadcast($chatId, $messageId, $callbackId, $userDbId, $strings);
                return;
            case 'stats':
                $this->showAdminStats($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
                return;
            case 'smm':
                $action = $parts[2] ?? 'categories';
                if ($action === 'categories') {
                    $this->showAdminSmmCategories($chatId, $messageId, $strings);
                    $this->answerCallback($callbackId, '✅');
                    return;
                }
                if ($action === 'categories_add') {
                    $this->setAdminState($userDbId, ['state' => 'await_smm_category_add']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage(
                        $chatId,
                        $strings['admin_smm_category_prompt'] ?? 'Send as CODE|Name|Caption|SortOrder.',
                        []
                    );
                    return;
                }
                if ($action === 'categories_remove') {
                    $this->setAdminState($userDbId, ['state' => 'await_smm_category_remove']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage($chatId, $strings['admin_smm_category_remove_prompt'] ?? 'Send the category code to remove.', []);
                    return;
                }
                if ($action === 'services') {
                    $this->showAdminSmmServices($chatId, $messageId, $strings);
                    $this->answerCallback($callbackId, '✅');
                    return;
                }
                if ($action === 'services_add') {
                    $this->setAdminState($userDbId, ['state' => 'await_smm_service_add']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage(
                        $chatId,
                        $strings['admin_smm_service_prompt'] ?? 'Send as CATEGORY_CODE|ProviderCode|Name|Rate|Min|Max|Currency.',
                        []
                    );
                    return;
                }
                if ($action === 'services_remove') {
                    $this->setAdminState($userDbId, ['state' => 'await_smm_service_remove']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage($chatId, $strings['admin_smm_service_remove_prompt'] ?? 'Send the service ID to remove.', []);
                    return;
                }
                $this->showAdminCatalogPanel($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
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
                $action = $parts[2] ?? 'panel';
                if ($action === 'toggle') {
                    $this->toggleReferralsEnabled($strings);
                    $this->answerCallback($callbackId, '✅');
                    $this->showAdminReferralsPanel($chatId, $messageId, $strings);
                    return;
                }
                if ($action === 'config') {
                    $this->setAdminState($userDbId, ['state' => 'await_referral_config']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage(
                        $chatId,
                        $strings['admin_referrals_config_prompt'] ?? 'Send flat reward, percent, min order (e.g. 1.5|5|2).',
                        []
                    );
                    return;
                }
                $this->showAdminReferralsPanel($chatId, $messageId, $strings);
                $this->answerCallback($callbackId, '✅');
                return;
            case 'users':
                $action = $parts[2] ?? 'panel';
                if ($action === 'ban') {
                    $this->setAdminState($userDbId, ['state' => 'await_user_ban']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage($chatId, $strings['admin_user_id_prompt'] ?? 'Provide a valid Telegram ID.', []);
                    return;
                }
                if ($action === 'unban') {
                    $this->setAdminState($userDbId, ['state' => 'await_user_unban']);
                    $this->answerCallback($callbackId, '✍️');
                    $this->sendMessage($chatId, $strings['admin_user_id_prompt'] ?? 'Provide a valid Telegram ID.', []);
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

    private function showAdminMenu(int $chatId, ?int $messageId, array $strings): void
    {
        $text = $strings['admin_panel_title'] ?? 'Admin Panel';
        $keyboard = [
            [
                ['text' => $strings['admin_section_tickets'] ?? 'Tickets', 'callback_data' => 'admin:tickets:list'],
                ['text' => $strings['admin_section_users'] ?? 'Users', 'callback_data' => 'admin:users'],
            ],
            [
                ['text' => $strings['admin_section_wallet'] ?? 'Wallet', 'callback_data' => 'admin:wallet'],
                ['text' => $strings['admin_section_catalog'] ?? 'Catalog', 'callback_data' => 'admin:catalog'],
            ],
            [
                ['text' => $strings['admin_section_pricing'] ?? 'Pricing', 'callback_data' => 'admin:pricing'],
                ['text' => $strings['admin_section_stars'] ?? 'Stars', 'callback_data' => 'admin:stars'],
            ],
            [
                ['text' => $strings['admin_section_content'] ?? 'Content', 'callback_data' => 'admin:content'],
                ['text' => $strings['admin_section_broadcast'] ?? 'Broadcast', 'callback_data' => 'admin:broadcast'],
            ],
            [
                ['text' => $strings['admin_section_forcesub'] ?? 'Forced Subscription', 'callback_data' => 'admin:forcesub'],
                ['text' => $strings['admin_section_maintenance'] ?? 'Maintenance', 'callback_data' => 'admin:maintenance'],
            ],
            [
                ['text' => $strings['admin_section_agents'] ?? 'Agents', 'callback_data' => 'admin:agents'],
                ['text' => $strings['admin_section_referrals'] ?? 'Referrals', 'callback_data' => 'admin:referrals'],
            ],
            [
                ['text' => $strings['admin_section_features'] ?? 'Features', 'callback_data' => 'admin:features'],
                ['text' => $strings['admin_section_stats'] ?? 'Stats', 'callback_data' => 'admin:stats'],
            ],
            [
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'back'],
            ],
        ];

        if ($messageId !== null) {
            $this->editMessage($chatId, $messageId, $text, $keyboard);
        } else {
            $this->sendMessage($chatId, $text, $keyboard);
        }
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

    private function showAdminWalletPanel(int $chatId, int $messageId, array $strings): void
    {
        $text = $strings['admin_wallet_title'] ?? 'Wallet & Users';
        $keyboard = [
            [
                ['text' => $strings['admin_wallet_credit'] ?? 'Credit Balance', 'callback_data' => 'admin:wallet:credit'],
                ['text' => $strings['admin_wallet_debit'] ?? 'Debit Balance', 'callback_data' => 'admin:wallet:debit'],
            ],
            [
                ['text' => $strings['admin_wallet_refund'] ?? 'Refund User', 'callback_data' => 'admin:wallet:refund'],
                ['text' => $strings['admin_user_ban_button'] ?? 'Ban user', 'callback_data' => 'admin:users:ban'],
            ],
            [
                ['text' => $strings['admin_user_unban_button'] ?? 'Unban user', 'callback_data' => 'admin:users:unban'],
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:root'],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function showAdminCatalogPanel(int $chatId, int $messageId, array $strings): void
    {
        $general = $this->settings->general();
        $margin = (float)($general['pricing_margin_percent'] ?? 0);
        $text = $strings['admin_catalog_title'] ?? 'Catalog Management';
        $text .= PHP_EOL . PHP_EOL;
        $text .= sprintf(
            "%s: %0.2f%%",
            $strings['admin_pricing_margin_label'] ?? 'هامش الربح العام',
            $margin
        );
        $keyboard = [
            [
                ['text' => $strings['admin_catalog_numbers_list'] ?? 'List Countries', 'callback_data' => 'admin:catalog:numbers:list:0'],
                ['text' => $strings['admin_catalog_numbers_add'] ?? 'Add Country', 'callback_data' => 'admin:catalog:numbers:add'],
            ],
            [
                ['text' => $strings['admin_catalog_numbers_remove'] ?? 'Remove Country', 'callback_data' => 'admin:catalog:numbers:remove'],
                ['text' => $strings['admin_catalog_numbers_import'] ?? 'Import Countries', 'callback_data' => 'admin:catalog:numbers:import'],
            ],
            [
                ['text' => $strings['admin_catalog_numbers_auto_import'] ?? 'Auto Import Countries', 'callback_data' => 'admin:catalog:numbers:auto_import'],
            ],
            [
                ['text' => $strings['admin_catalog_set_margin'] ?? 'تحديد نسبة الربح', 'callback_data' => 'admin:catalog:margin'],
            ],
            [
                ['text' => $strings['admin_smm_categories'] ?? 'SMM Categories', 'callback_data' => 'admin:smm:categories'],
                ['text' => $strings['admin_smm_services'] ?? 'SMM Services', 'callback_data' => 'admin:smm:services'],
            ],
            [
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:root'],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function showAdminPricingPanel(int $chatId, int $messageId, array $strings): void
    {
        $general = $this->settings->general();
        $stars = $this->settings->stars();
        $margin = (float)($general['pricing_margin_percent'] ?? 0);
        $transferFee = (float)($general['transfer_fee_percent'] ?? 0);
        $transferMinimum = (float)($general['transfer_minimum'] ?? 0);
        $starPrice = $stars['usd_per_star'] ?? 0.011;
        $text = ($strings['admin_pricing_title'] ?? 'Pricing Settings') . PHP_EOL;
        $text .= sprintf(
            "%s: %0.2f%%%s%s: %0.2f%%%s%s: %0.2f%s%s: %0.4f",
            $strings['admin_pricing_margin_label'] ?? 'Global margin',
            $margin,
            PHP_EOL,
            $strings['admin_transfer_fee_label'] ?? 'Transfer fee',
            $transferFee,
            PHP_EOL,
            $strings['admin_transfer_min_label'] ?? 'Minimum transfer',
            $transferMinimum,
            PHP_EOL,
            $strings['admin_stars_price_label'] ?? 'USD per Star',
            $starPrice
        );

        $keyboard = [
            [
                ['text' => $strings['admin_pricing_set_margin'] ?? 'Set Margin', 'callback_data' => 'admin:pricing:margin'],
                ['text' => $strings['admin_pricing_set_custom'] ?? 'Custom Country Price', 'callback_data' => 'admin:pricing:custom'],
            ],
            [
                ['text' => $strings['admin_transfer_fee_button'] ?? 'Transfer fee', 'callback_data' => 'admin:pricing:fee'],
                ['text' => $strings['admin_transfer_min_button'] ?? 'Transfer min', 'callback_data' => 'admin:pricing:min'],
            ],
            [
                ['text' => $strings['admin_stars_set_price_button'] ?? 'Set Star Price', 'callback_data' => 'admin:stars:setprice'],
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:root'],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function showAdminContentPanel(int $chatId, int $messageId, array $strings): void
    {
        $general = $this->settings->general();
        $text = ($strings['admin_content_title'] ?? 'Content Settings') . PHP_EOL;
        $text .= sprintf(
            "%s: %s%s%s: %s",
            $strings['admin_content_start_label'] ?? 'Start message',
            $general['start_message'] ? $this->esc($general['start_message']) : ($strings['none'] ?? '-'),
            PHP_EOL,
            $strings['admin_content_help_label'] ?? 'Help text',
            $general['help_text'] ? $this->esc($general['help_text']) : ($strings['none'] ?? '-')
        );

        $keyboard = [
            [
                ['text' => $strings['admin_content_set_start'] ?? 'Update Start', 'callback_data' => 'admin:content:start'],
                ['text' => $strings['admin_content_set_help'] ?? 'Update Help', 'callback_data' => 'admin:content:help'],
            ],
            [
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:root'],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function showAdminAgentsPanel(int $chatId, int $messageId, array $strings): void
    {
        $agents = $this->settings->agents();
        $lines = [];
        foreach ($agents as $index => $agent) {
            $label = sprintf('%d. %s', $index + 1, $agent['name']);
            if ($agent['username']) {
                $label .= ' (@' . ltrim($agent['username'], '@') . ')';
            }
            $lines[] = $label;
        }
        $text = ($strings['admin_agents_title'] ?? 'Agents') . PHP_EOL;
        $text .= $lines ? implode(PHP_EOL, $lines) : ($strings['admin_agents_empty'] ?? 'No agents configured.');

        $keyboard = [
            [
                ['text' => $strings['admin_agents_add_button'] ?? 'Add Agent', 'callback_data' => 'admin:agents:add'],
                ['text' => $strings['admin_agents_remove_button'] ?? 'Remove Agent', 'callback_data' => 'admin:agents:remove'],
            ],
            [
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:root'],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function showAdminMaintenancePanel(int $chatId, int $messageId, array $strings): void
    {
        $maintenance = $this->settings->maintenance();
        $text = ($strings['admin_maintenance_title'] ?? 'Maintenance Mode') . PHP_EOL;
        $text .= sprintf(
            "%s: %s\n%s",
            $strings['admin_stars_enabled_label'] ?? 'Enabled',
            ($maintenance['enabled'] ?? false) ? 'ON' : 'OFF',
            $maintenance['message'] ?? ($strings['admin_maintenance_default'] ?? 'Bot is under maintenance.')
        );

        $keyboard = [
            [
                ['text' => $strings['admin_maintenance_toggle'] ?? 'Toggle', 'callback_data' => 'admin:maintenance:toggle'],
                ['text' => $strings['admin_maintenance_set_message'] ?? 'Set Message', 'callback_data' => 'admin:maintenance:message'],
            ],
            [
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:root'],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function promptBroadcast(int $chatId, int $messageId, ?string $callbackId, int $userId, array $strings): void
    {
        $this->setAdminState($userId, ['state' => 'await_broadcast_message']);
        $this->answerCallback($callbackId, '✍️');
        $this->editMessage(
            $chatId,
            $messageId,
            $strings['admin_broadcast_prompt'] ?? 'Send the message you want to broadcast to all users.',
            [
                [
                    ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:root'],
                ],
            ]
        );
    }

    private function showAdminStats(int $chatId, int $messageId, array $strings): void
    {
        $users = $this->userManager->listAll();
        $totalUsers = count($users);
        $agents = $this->settings->agents();
        $referrals = $this->settings->referrals();
        $stats = sprintf(
            "%s: %d\n%s: %d\n%s: %s",
            $strings['admin_stats_users'] ?? 'Users',
            $totalUsers,
            $strings['admin_stats_agents'] ?? 'Agents',
            count($agents),
            $strings['admin_stats_referrals'] ?? 'Referrals enabled',
            ($referrals['enabled'] ?? false) ? 'YES' : 'NO'
        );

        $this->editMessage($chatId, $messageId, $stats, [
            [
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:root'],
            ],
        ]);
    }

    private function showAdminSmmCategories(int $chatId, int $messageId, array $strings): void
    {
        $categories = $this->smmCatalog->allCategories();
        $lines = [];
        foreach ($categories as $category) {
            $status = ((int)($category['is_active'] ?? 1)) === 1 ? '✅' : '⛔';
            $lines[] = sprintf('%s • %s', $category['code'], $status);
        }
        $text = ($strings['admin_smm_categories_title'] ?? 'SMM Categories') . PHP_EOL;
        $text .= $lines ? implode(PHP_EOL, $lines) : ($strings['admin_smm_empty'] ?? 'No entries yet.');

        $keyboard = [
            [
                ['text' => $strings['admin_smm_add_category'] ?? 'Add Category', 'callback_data' => 'admin:smm:categories_add'],
                ['text' => $strings['admin_smm_remove_category'] ?? 'Remove Category', 'callback_data' => 'admin:smm:categories_remove'],
            ],
            [
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:catalog'],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function showAdminSmmServices(int $chatId, int $messageId, array $strings): void
    {
        $services = array_slice($this->smmCatalog->allServices(), 0, 12);
        $lines = [];
        foreach ($services as $service) {
            $status = ((int)($service['is_active'] ?? 1)) === 1 ? '✅' : '⛔';
            $lines[] = sprintf('#%d • %s (%s)', $service['id'], $service['name'], $status);
        }
        $text = ($strings['admin_smm_services_title'] ?? 'SMM Services') . PHP_EOL;
        $text .= $lines ? implode(PHP_EOL, $lines) : ($strings['admin_smm_empty'] ?? 'No entries yet.');

        $keyboard = [
            [
                ['text' => $strings['admin_smm_add_service'] ?? 'Add Service', 'callback_data' => 'admin:smm:services_add'],
                ['text' => $strings['admin_smm_remove_service'] ?? 'Remove Service', 'callback_data' => 'admin:smm:services_remove'],
            ],
            [
                ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:catalog'],
            ],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function sendNumberCatalogOverview(int $chatId, int $messageId, int $page, array $strings): void
    {
        $records = $this->numberCatalog->allRaw();
        $perPage = 10;
        $total = count($records);
        $page = max(0, $page);
        $offset = $page * $perPage;
        $slice = array_slice($records, $offset, $perPage);

        $lines = [];
        foreach ($slice as $record) {
            $status = ((int)($record['is_active'] ?? 1)) === 1 ? '✅' : '⛔';
            $lines[] = sprintf('%s • %s • $%0.2f %s', $record['code'], $record['name'], $record['price_usd'], $status);
        }

        $text = ($strings['admin_catalog_numbers_title'] ?? 'Numbers Countries') . PHP_EOL;
        $text .= $lines ? implode(PHP_EOL, $lines) : ($strings['admin_catalog_empty'] ?? 'No countries configured.');

        $keyboard = [];
        if ($offset > 0 || $offset + $perPage < $total) {
            $nav = [];
            if ($offset > 0) {
                $nav[] = [
                    'text' => $strings['button_previous'] ?? 'Previous',
                    'callback_data' => sprintf('admin:catalog:numbers:list:%d', max(0, $page - 1)),
                ];
            }
            if ($offset + $perPage < $total) {
                $nav[] = [
                    'text' => $strings['button_next'] ?? 'Next',
                    'callback_data' => sprintf('admin:catalog:numbers:list:%d', $page + 1),
                ];
            }
            if ($nav !== []) {
                $keyboard[] = $nav;
            }
        }

        $keyboard[] = [
            ['text' => $strings['back'] ?? 'Back', 'callback_data' => 'admin:catalog'],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    /**
     * @param array<string, mixed> $state
     * @param array<string, string> $strings
     */
    private function completeWalletAdjustment(
        int $chatId,
        int $adminUserId,
        int $adminTelegramId,
        array $state,
        float $amount,
        string $mode,
        array $strings,
        array $options = []
    ): void {
        $targetUserId = $state['target_user_id'] ?? null;
        $targetTelegramId = $state['target_telegram_id'] ?? null;

        if (!$targetUserId || !$targetTelegramId) {
            $this->sendMessage($chatId, $strings['admin_wallet_error'] ?? 'Operation aborted.', []);
            $this->clearAdminState($adminUserId);
            return;
        }

        $currency = $options['currency'] ?? 'USD';
        try {
            if ($mode === 'debit') {
                $this->wallets->debit((int)$targetUserId, $amount, $currency);
            } else {
                $this->wallets->credit((int)$targetUserId, $amount, $currency);
            }
        } catch (Throwable $e) {
            $this->sendMessage($chatId, $strings['admin_wallet_error'] ?? 'Operation aborted.', []);
            $this->clearAdminState($adminUserId);
            return;
        }

        $this->recordAdminTransaction(
            (int)$targetUserId,
            $mode === 'debit' ? 'debit' : 'credit',
            $amount,
            $currency,
            $mode,
            [
                'admin_user_id' => $adminUserId,
                'admin_telegram_id' => $adminTelegramId,
            ]
        );

        $actionText = $mode === 'debit'
            ? ($strings['admin_wallet_debit_done'] ?? 'Debited %0.2f USD from %d.')
            : ($strings['admin_wallet_credit_done'] ?? 'Credited %0.2f USD to %d.');
        if ($mode === 'refund') {
            $actionText = $strings['admin_wallet_refund_done'] ?? 'Refunded %0.2f USD to %d.';
        }

        $this->sendMessage(
            $chatId,
            sprintf($actionText, $amount, $targetTelegramId),
            []
        );

        $userMessage = $mode === 'debit'
            ? ($strings['admin_wallet_user_debit'] ?? 'An administrator removed %0.2f USD from your wallet.')
            : ($strings['admin_wallet_user_credit'] ?? 'An administrator added %0.2f USD to your wallet.');
        if ($mode === 'refund') {
            $userMessage = $strings['admin_wallet_user_refund'] ?? 'A refund of %0.2f USD has been added to your wallet.';
        }

        $this->telegram->call('sendMessage', [
            'chat_id' => $targetTelegramId,
            'text' => sprintf($userMessage, $amount),
        ]);

        $this->logAdminAction(sprintf(
            'Wallet %s %0.2f %s for user #%d',
            $mode,
            $amount,
            $currency,
            $targetTelegramId
        ));

        $this->clearAdminState($adminUserId);
    }

    private function handleBroadcast(string $message, int $chatId, array $strings): void
    {
        $trimmed = trim($message);
        if ($trimmed === '') {
            $this->sendMessage($chatId, $strings['admin_broadcast_prompt'] ?? 'Send the message you want to broadcast to all users.', []);
            return;
        }

        $audience = $this->userManager->listAll();
        $sent = 0;
        foreach ($audience as $user) {
            $telegramId = (int)$user['telegram_id'];
            if ($telegramId <= 0) {
                continue;
            }

            try {
                $this->telegram->call('sendMessage', [
                    'chat_id' => $telegramId,
                    'text' => $trimmed,
                ]);
                $sent++;
                usleep(150000); // reduce spam risk
            } catch (Throwable $e) {
                continue;
            }
        }

        $this->sendMessage(
            $chatId,
            sprintf($strings['admin_broadcast_done'] ?? 'Broadcast sent to %d users.', $sent),
            []
        );
    }

    /**
     * @return array{type:string,id:int}|null
     */
    private function parseRefundReference(string $value): ?array
    {
        $normalized = strtolower(trim($value));
        $parts = explode(':', $normalized);
        if (count($parts) !== 2) {
            return null;
        }
        if (!in_array($parts[0], ['numbers', 'smm'], true)) {
            return null;
        }
        if (!ctype_digit($parts[1])) {
            return null;
        }

        return [
            'type' => $parts[0],
            'id' => (int)$parts[1],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveRefundDetails(string $type, int $orderId): ?array
    {
        if ($type === 'numbers') {
            $order = $this->numberOrders->find($orderId);
            if (!$order) {
                return null;
            }
            $amount = (float)($order['price_usd'] ?? 0);
            $currency = (string)($order['currency'] ?? 'USD');
            $status = (string)($order['status'] ?? '');
            $userId = (int)($order['user_id'] ?? 0);
            $telegramId = $this->resolveTelegramIdByUserId($userId);

            return [
                'type' => 'numbers',
                'order_id' => $orderId,
                'order' => $order,
                'user_id' => $userId,
                'telegram_id' => $telegramId,
                'amount' => $amount,
                'currency' => $currency,
                'reference' => sprintf('number:%d', $orderId),
                'already_refunded' => $status === 'refunded',
            ];
        }

        $order = $this->smmOrders->find($orderId);
        if (!$order) {
            return null;
        }
        $amount = (float)($order['price'] ?? 0);
        $currency = (string)($order['currency'] ?? 'USD');
        $status = (string)($order['status'] ?? '');
        $userId = (int)($order['user_id'] ?? 0);
        $telegramId = $this->resolveTelegramIdByUserId($userId);

        return [
            'type' => 'smm',
            'order_id' => $orderId,
            'order' => $order,
            'user_id' => $userId,
            'telegram_id' => $telegramId,
            'amount' => $amount,
            'currency' => $currency,
            'reference' => sprintf('smm:%d', $orderId),
            'already_refunded' => in_array($status, ['canceled', 'refunded'], true),
        ];
    }

    /**
     * @param array<string, mixed> $details
     */
    private function finalizeOrderRefund(
        int $chatId,
        int $adminDbId,
        int $adminTelegramId,
        array $details,
        array $strings
    ): void {
        $userId = (int)$details['user_id'];
        $amount = (float)$details['amount'];
        $currency = (string)$details['currency'];
        $telegramId = $details['telegram_id'] ?? null;

        if ($userId <= 0) {
            $this->clearAdminState($adminDbId);
            $this->sendMessage($chatId, $strings['admin_wallet_error'] ?? 'Operation aborted.', []);
            return;
        }

        try {
            $this->wallets->credit($userId, $amount, $currency);
        } catch (Throwable $e) {
            $this->clearAdminState($adminDbId);
            $this->sendMessage($chatId, $strings['admin_wallet_error'] ?? 'Operation aborted.', []);
            return;
        }

        $this->recordAdminTransaction(
            $userId,
            'credit',
            $amount,
            $currency,
            'refund',
            [
                'admin_user_id' => $adminDbId,
                'admin_telegram_id' => $adminTelegramId,
                'order_reference' => $details['reference'],
            ]
        );

        $order = $details['order'];
        $meta = $this->decodeOrderMetadata($order['meta'] ?? ($order['metadata'] ?? null));
        $meta['admin_refund'] = [
            'admin' => $adminTelegramId,
            'amount' => $amount,
            'currency' => $currency,
            'timestamp' => time(),
        ];

        if ($details['type'] === 'numbers') {
            $this->numberOrders->updateStatus((int)$details['order_id'], 'refunded', $meta);
        } else {
            $this->smmOrders->updateStatus((int)$details['order_id'], 'canceled', $meta);
        }

        $this->referralService->revertRewardByReference($details['reference']);

        if ($telegramId) {
            $this->telegram->call('sendMessage', [
                'chat_id' => $telegramId,
                'text' => sprintf($strings['admin_wallet_user_refund'] ?? 'A refund of %0.2f USD has been added to your wallet.', $amount),
            ]);
        }

        $this->logAdminAction(sprintf(
            'Refunded %0.2f %s for %s',
            $amount,
            $currency,
            $details['reference']
        ));

        $this->clearAdminState($adminDbId);
        $this->sendMessage($chatId, $strings['admin_wallet_refund_done'] ?? 'Refund completed successfully.', []);
    }

    /**
     * @param mixed $value
     * @return array<string, mixed>
     */
    private function decodeOrderMetadata($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function recordAdminTransaction(
        int $userId,
        string $direction,
        float $amount,
        string $currency,
        string $mode,
        array $meta = []
    ): void {
        $this->transactions->log(
            $userId,
            $direction,
            $mode === 'refund' ? 'refund' : 'admin_manual',
            $amount,
            $currency,
            $meta['order_reference'] ?? null,
            $meta
        );
    }

    private function resolveTelegramIdByUserId(int $userId): ?int
    {
        $user = $this->userManager->findById($userId);
        return $user ? (int)$user['telegram_id'] : null;
    }

    private function showAdminReferralsPanel(int $chatId, int $messageId, array $strings): void
    {
        $config = $this->settings->referrals();
        $text = ($strings['referral_title'] ?? 'Referral Program') . PHP_EOL;
        $text .= sprintf(
            "%s: %s\n%s",
            $strings['admin_stars_enabled_label'] ?? 'Enabled',
            ($config['enabled'] ?? false) ? 'ON' : 'OFF',
            sprintf(
                "%s: %0.2f • %s: %0.2f%% • %s: %0.2f",
                $strings['admin_referrals_flat'] ?? 'Flat reward',
                (float)($config['reward_flat_usd'] ?? 0),
                $strings['admin_referrals_percent'] ?? 'Percent',
                (float)($config['reward_percent'] ?? 0),
                $strings['admin_referrals_min_order'] ?? 'Min order',
                (float)($config['min_order_usd'] ?? 0)
            )
        );

        $keyboard = [
            [
                [
                    'text' => $strings['admin_referrals_toggle_button'] ?? 'Toggle',
                    'callback_data' => 'admin:referrals:toggle',
                ],
                [
                    'text' => $strings['admin_referrals_config_button'] ?? 'Configure',
                    'callback_data' => 'admin:referrals:config',
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
        $languageCode = $strings['_lang'] ?? null;
        $country = $this->numberCatalog->find($countryCode, $languageCode) ?? [
            'name' => $order['country_code'],
            'display_name' => $order['country_code'],
            'code' => $order['country_code'],
        ];

        $template = $strings['code_received'] ?? 'Code: __code__';
        $text = str_replace(
            ['__num__', '__p__', '__c__', '__code__', '__pass__'],
            [
                $this->esc((string)$order['number']),
                number_format((float)$order['price_usd'], 2),
                $this->esc($this->countryDisplayName($country)),
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
    private function showLanguageMenu(
        int $chatId,
        int $messageId,
        array $strings,
        string $backLabel
    ): void {
        $options = $this->languages->options();
        $text = $strings['change_language_prompt'] ?? 'Choose your preferred language:';
        $keyboard = [];
        $row = [];
        $current = $strings['_lang'] ?? '';

        foreach ($options as $code => $label) {
            $buttonText = $label;
            if ($code === $current) {
                $buttonText = '• ' . $label . ' •';
            }

            $row[] = [
                'text' => $buttonText,
                'callback_data' => sprintf('lang:set:%s', $code),
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

    /**
     * @param array<string, string> $strings
     */
    private function handleLanguageCallback(
        int $chatId,
        int $messageId,
        ?string $callbackId,
        int $userDbId,
        int $telegramUserId,
        array $parts,
        array $strings,
        string $backLabel
    ): void {
        $action = $parts[1] ?? 'list';
        if ($action !== 'set') {
            $this->showLanguageMenu($chatId, $messageId, $strings, $backLabel);
            return;
        }

        $code = $parts[2] ?? '';
        $options = $this->languages->options();
        if ($code === '' || !isset($options[$code])) {
            $this->answerCallback(
                $callbackId,
                $strings['change_language_error'] ?? 'Unable to change language right now.',
                true
            );
            return;
        }

        $normalized = $this->languages->ensure($code);
        $this->userManager->updateLanguagePreference($userDbId, $normalized);
        $this->cacheLanguage($telegramUserId, $normalized);

        $updatedStrings = $this->languages->strings($normalized);
        $changeLabel = $this->languages->label($normalized, 'change_language', 'Change Language');

        $this->answerCallback(
            $callbackId,
            $updatedStrings['change_language_success'] ?? 'Language updated successfully.'
        );

        $this->editMessage(
            $chatId,
            $messageId,
            $updatedStrings['main_menu'] ?? 'Main Menu',
            $this->keyboardFactory->mainMenu($updatedStrings, $changeLabel, [
                'features' => $this->features,
                'is_admin' => $this->isAdmin($telegramUserId),
            ])
        );
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

    private function showRechargeInfo(
        int $chatId,
        int $messageId,
        int $userDbId,
        array $strings,
        string $backLabel
    ): void {
        $template = $strings['charge_info'] ?? '';
        if ($template === '') {
            $template = $strings['menu_recharge'] ?? 'Recharge your balance.';
        }

        $text = trim($this->renderUserTemplate($template, $userDbId));
        if ($text === '') {
            $text = $strings['menu_recharge'] ?? 'Recharge your balance.';
        }

        $keyboard = [];
        $chargeLink = $this->generalLink('charge_link');
        if ($chargeLink) {
            $keyboard[] = [
                [
                    'text' => $strings['menu_recharge'] ?? 'Recharge',
                    'url' => $chargeLink,
                ],
            ];
        }
        $keyboard[] = [
            ['text' => $backLabel, 'callback_data' => 'back'],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function showPublicAgents(
        int $chatId,
        int $messageId,
        array $strings,
        string $backLabel
    ): void {
        $agents = $this->settings->agents();
        $lines = [];
        foreach ($agents as $index => $agent) {
            $label = sprintf('%d. %s', $index + 1, $agent['name']);
            if (!empty($agent['username'])) {
                $label .= ' (@' . ltrim((string)$agent['username'], '@') . ')';
            }
            $lines[] = $label;
        }

        $text = $strings['menu_agents'] ?? 'Agents';
        $text .= PHP_EOL . PHP_EOL;
        $text .= $lines !== [] ? implode(PHP_EOL, $lines) : ($strings['no_agents'] ?? 'No agents available.');

        $keyboard = [
            [
                ['text' => $backLabel, 'callback_data' => 'back'],
            ],
        ];

        $this->editMessage($chatId, $messageId, trim($text), $keyboard);
    }

    private function showActivationsInfo(
        int $chatId,
        int $messageId,
        int $userDbId,
        array $strings,
        string $backLabel
    ): void {
        $activationLink = $this->generalLink('activation_link');
        $text = $strings['menu_bot_activations'] ?? 'Bot Activations';

        $keyboard = [];
        if ($activationLink) {
            $text .= PHP_EOL . PHP_EOL . $activationLink;
            $keyboard[] = [
                [
                    'text' => $strings['menu_bot_activations'] ?? 'Bot Activations',
                    'url' => $activationLink,
                ],
            ];
        } else {
            $fallback = $strings['support_info'] ?? '';
            if ($fallback !== '') {
                $text .= PHP_EOL . PHP_EOL . $this->renderUserTemplate($fallback, $userDbId);
            }
        }

        $keyboard[] = [
            ['text' => $backLabel, 'callback_data' => 'back'],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    private function renderUserTemplate(string $template, int $userDbId): string
    {
        if ($template === '') {
            return '';
        }

        $general = $this->settings->general();
        $chargeLink = (string)($general['charge_link'] ?? '');
        $supportLink = (string)($general['support_link'] ?? '');
        $invitePoints = (string)($general['invite_points'] ?? 0);

        $refLink = '';
        if ($userDbId > 0) {
            try {
                $refLink = $this->referralService->generateShareLink($userDbId);
            } catch (RuntimeException $e) {
                $refLink = '';
            }
        }

        return strtr($template, [
            '{{charge_link}}' => $chargeLink,
            '{{support_link}}' => $supportLink,
            '{{invite_point}}' => $invitePoints,
            '{{ref_link}}' => $refLink,
        ]);
    }

    private function generalLink(string $key): ?string
    {
        $value = $this->settings->general()[$key] ?? '';
        $value = is_string($value) ? trim($value) : '';
        return $value !== '' ? $value : null;
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
            case '/admin':
                $this->showAdminMenu($chatId, null, $strings);
                return true;
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
            case 'await_wallet_user':
                $telegramId = (int)preg_replace('/\D+/', '', $trimmed);
                if ($telegramId <= 0) {
                    $this->sendMessage($chatId, $strings['admin_user_id_prompt'] ?? 'Provide a valid Telegram ID.', []);
                    return true;
                }
                $user = $this->userManager->findByTelegramId($telegramId);
                if (!$user) {
                    $this->sendMessage($chatId, $strings['admin_user_not_found'] ?? 'User not found.', []);
                    return true;
                }
                $state['state'] = 'await_wallet_amount';
                $state['target_user_id'] = (int)$user['id'];
                $state['target_telegram_id'] = $telegramId;
                $this->setAdminState($userDbId, $state);
                $this->sendMessage($chatId, $strings['admin_wallet_amount_prompt'] ?? 'Send the amount in USD.', []);
                return true;
            case 'await_wallet_amount':
                if (!is_numeric($trimmed) || (float)$trimmed <= 0) {
                    $this->sendMessage($chatId, $strings['admin_wallet_invalid_amount'] ?? 'Send a positive numeric amount.', []);
                    return true;
                }
                $this->completeWalletAdjustment(
                    $chatId,
                    $userDbId,
                    $telegramUserId,
                    $state,
                    (float)$trimmed,
                    $state['mode'] ?? 'credit',
                    $strings
                );
                return true;
            case 'await_wallet_refund_reference':
                $parsed = $this->parseRefundReference($trimmed);
                if ($parsed === null) {
                    $this->sendMessage($chatId, $strings['admin_wallet_refund_invalid'] ?? 'Please follow the format numbers:ID or smm:ID.', []);
                    return true;
                }
                $details = $this->resolveRefundDetails($parsed['type'], $parsed['id']);
                if ($details === null) {
                    $this->sendMessage($chatId, $strings['admin_wallet_refund_not_found'] ?? 'Order not found.', []);
                    return true;
                }
                if ($details['already_refunded']) {
                    $this->sendMessage($chatId, $strings['admin_wallet_refund_already'] ?? 'This order has already been processed.', []);
                    return true;
                }
                $state['state'] = 'await_wallet_refund_confirm';
                $state['refund_details'] = $details;
                $this->setAdminState($userDbId, $state);
                $this->sendMessage(
                    $chatId,
                    sprintf(
                        $strings['admin_wallet_refund_confirm'] ?? 'Refund %0.2f USD for %s. Reply CONFIRM to proceed or /cancel to abort.',
                        $details['amount'],
                        strtoupper($details['reference'])
                    ),
                    []
                );
                return true;
            case 'await_wallet_refund_confirm':
                if (strcasecmp($trimmed, 'confirm') !== 0) {
                    $this->sendMessage($chatId, $strings['admin_wallet_refund_confirm'] ?? 'Reply CONFIRM to proceed.', []);
                    return true;
                }
                $details = $state['refund_details'] ?? null;
                if (!is_array($details)) {
                    $this->clearAdminState($userDbId);
                    $this->sendMessage($chatId, $strings['admin_wallet_error'] ?? 'Operation aborted.', []);
                    return true;
                }
                $this->finalizeOrderRefund($chatId, $userDbId, $telegramUserId, $details, $strings);
                return true;
            case 'await_user_ban':
                $telegramId = (int)preg_replace('/\D+/', '', $trimmed);
                if ($telegramId <= 0) {
                    $this->sendMessage($chatId, $strings['admin_user_id_prompt'] ?? 'Provide a valid Telegram ID.', []);
                    return true;
                }
                $updated = $this->userManager->setBanStatusByTelegramId($telegramId, true);
                if ($updated) {
                    $this->sendMessage($chatId, $strings['admin_user_updated'] ?? 'User updated.', []);
                } else {
                    $this->sendMessage($chatId, $strings['admin_user_not_found'] ?? 'User not found.', []);
                }
                $this->clearAdminState($userDbId);
                return true;
            case 'await_user_unban':
                $telegramId = (int)preg_replace('/\D+/', '', $trimmed);
                if ($telegramId <= 0) {
                    $this->sendMessage($chatId, $strings['admin_user_id_prompt'] ?? 'Provide a valid Telegram ID.', []);
                    return true;
                }
                $updated = $this->userManager->setBanStatusByTelegramId($telegramId, false);
                if ($updated) {
                    $this->sendMessage($chatId, $strings['admin_user_updated'] ?? 'User updated.', []);
                } else {
                    $this->sendMessage($chatId, $strings['admin_user_not_found'] ?? 'User not found.', []);
                }
                $this->clearAdminState($userDbId);
                return true;
            case 'await_general_start':
                $general = $this->settings->general();
                $general['start_message'] = $trimmed === '/clear' ? null : $trimmed;
                $this->settings->updateGeneral($general);
                $this->clearAdminState($userDbId);
                $this->sendMessage($chatId, $strings['admin_content_saved'] ?? 'Saved.', []);
                return true;
            case 'await_general_help':
                $general = $this->settings->general();
                $general['help_text'] = $trimmed === '/clear' ? null : $trimmed;
                $this->settings->updateGeneral($general);
                $this->clearAdminState($userDbId);
                $this->sendMessage($chatId, $strings['admin_content_saved'] ?? 'Saved.', []);
                return true;
            case 'await_agent_add':
                $parts = array_map('trim', explode('|', $trimmed));
                if ($parts[0] === '') {
                    $this->sendMessage($chatId, $strings['admin_agents_add_prompt'] ?? 'Send as Name|username (username optional).', []);
                    return true;
                }
                $agents = $this->settings->agents();
                $agents[] = [
                    'name' => $parts[0],
                    'username' => $parts[1] ?? null,
                ];
                $this->settings->updateAgents(['items' => $agents]);
                $this->clearAdminState($userDbId);
                $this->sendMessage($chatId, $strings['admin_agents_saved'] ?? 'Saved.', []);
                return true;
            case 'await_agent_remove':
                $needle = trim($trimmed, '@ ');
                if ($needle === '') {
                    $this->sendMessage($chatId, $strings['admin_agents_remove_prompt'] ?? 'Send the agent username or name to remove.', []);
                    return true;
                }
                $agents = $this->settings->agents();
                $filtered = array_values(array_filter($agents, function (array $agent) use ($needle): bool {
                    if (strcasecmp($agent['name'], $needle) === 0) {
                        return false;
                    }
                    if ($agent['username'] && strcasecmp(ltrim($agent['username'], '@'), ltrim($needle, '@')) === 0) {
                        return false;
                    }
                    return true;
                }));
                $this->settings->updateAgents(['items' => $filtered]);
                $this->clearAdminState($userDbId);
                $this->sendMessage($chatId, $strings['admin_agents_saved'] ?? 'Saved.', []);
                return true;
            case 'await_maintenance_message':
                $config = $this->settings->maintenance();
                $config['message'] = $trimmed === '/clear' ? null : $trimmed;
                $this->settings->updateMaintenance($config);
                $this->clearAdminState($userDbId);
                $this->sendMessage($chatId, $strings['admin_content_saved'] ?? 'Saved.', []);
                return true;
            case 'await_catalog_margin':
                if (!is_numeric($trimmed)) {
                    $this->sendMessage($chatId, $strings['admin_pricing_margin_prompt'] ?? 'أرسل النسبة المئوية للأرباح (مثال 10 أو 15 أو 12.5). سيتم إضافة هذه النسبة إلى سعر الأرقام من المزود.', []);
                    return true;
                }
                $newMargin = (float)$trimmed;
                $general = $this->settings->general();
                $general['pricing_margin_percent'] = $newMargin;
                $this->settings->updateGeneral($general);
                
                // تحديث نسبة الربح لجميع الدول
                try {
                    $allCountries = $this->numberCatalog->allRaw();
                    $updated = 0;
                    // التأكد من وجود provider_id افتراضي
                    $defaultProviderId = 1;
                    foreach ($allCountries as $country) {
                        $providerId = isset($country['provider_id']) && $country['provider_id'] > 0 
                            ? (int)$country['provider_id'] 
                            : $defaultProviderId;
                        
                        // التحقق من وجود provider_id في قاعدة البيانات
                        if ($providerId <= 0) {
                            $providerId = $defaultProviderId;
                        }
                        
                        $this->numberCatalog->upsert([
                            'code' => $country['code'],
                            'name' => $country['name'],
                            'name_translations' => $country['name_translations'] ?? null,
                            'price_usd' => $country['price_usd'],
                            'margin_percent' => $newMargin,
                            'provider_id' => $providerId,
                            'is_active' => isset($country['is_active']) ? (int)$country['is_active'] : 1,
                        ]);
                        $updated++;
                    }
                } catch (Throwable $e) {
                    error_log("Error updating margin: " . $e->getMessage());
                    $this->clearAdminState($userDbId);
                    $this->sendMessage($chatId, 'حدث خطأ أثناء تحديث نسبة الربح: ' . $e->getMessage(), []);
                    return true;
                }
                
                $this->clearAdminState($userDbId);
                $message = sprintf(
                    $strings['admin_catalog_margin_updated'] ?? 'تم تحديث نسبة الربح إلى %0.2f%% لجميع الدول (%d دولة).',
                    $newMargin,
                    $updated
                );
                $this->sendMessage($chatId, $message, []);
                return true;
            case 'await_pricing_margin':
                if (!is_numeric($trimmed)) {
                    $this->sendMessage($chatId, $strings['admin_pricing_margin_prompt'] ?? 'أرسل النسبة المئوية للأرباح (مثال 10 أو 15 أو 12.5). سيتم إضافة هذه النسبة إلى سعر الأرقام من المزود.', []);
                    return true;
                }
                $general = $this->settings->general();
                $general['pricing_margin_percent'] = (float)$trimmed;
                $this->settings->updateGeneral($general);
                $this->clearAdminState($userDbId);
                $this->sendMessage($chatId, $strings['admin_content_saved'] ?? 'تم الحفظ.', []);
                return true;
            case 'await_transfer_fee':
                if (!is_numeric($trimmed)) {
                    $this->sendMessage($chatId, $strings['admin_transfer_fee_prompt'] ?? 'Send the transfer fee percent (e.g. 2.5).', []);
                    return true;
                }
                $general = $this->settings->general();
                $general['transfer_fee_percent'] = (float)$trimmed;
                $this->settings->updateGeneral($general);
                $this->clearAdminState($userDbId);
                $this->sendMessage($chatId, $strings['admin_content_saved'] ?? 'Saved.', []);
                return true;
            case 'await_transfer_min':
                if (!is_numeric($trimmed)) {
                    $this->sendMessage($chatId, $strings['admin_transfer_min_prompt'] ?? 'Send the minimum transfer amount.', []);
                    return true;
                }
                $general = $this->settings->general();
                $general['transfer_minimum'] = (float)$trimmed;
                $this->settings->updateGeneral($general);
                $this->clearAdminState($userDbId);
                $this->sendMessage($chatId, $strings['admin_content_saved'] ?? 'Saved.', []);
                return true;
            case 'await_pricing_custom':
                $parts = preg_split('/\s+/', strtoupper($trimmed));
                if (count($parts) !== 2 || !is_numeric($parts[1])) {
                    $this->sendMessage($chatId, $strings['admin_pricing_custom_prompt'] ?? 'Send the country code and price as: US 1.75', []);
                    return true;
                }
                $raw = $this->numberCatalog->findRaw($parts[0]);
                if (!$raw) {
                    $this->sendMessage($chatId, $strings['admin_catalog_not_found'] ?? 'Country not found.', []);
                    return true;
                }
                $payload = $this->prepareCountryPayload($raw, (float)$parts[1]);
                $this->numberCatalog->upsert($payload);
                $this->clearAdminState($userDbId);
                $this->sendMessage($chatId, $strings['admin_content_saved'] ?? 'Saved.', []);
                return true;
            case 'await_country_payload':
                $segments = explode('|', $trimmed);
                if (count($segments) < 4) {
                    $this->sendMessage($chatId, $strings['admin_catalog_country_prompt'] ?? 'Send data as CODE|Name|PriceUSD|ProviderID|Margin%.', []);
                    return true;
                }
                $payload = [
                    'code' => strtoupper(trim($segments[0])),
                    'name' => trim($segments[1]),
                    'price_usd' => (float)$segments[2],
                    'provider_id' => (int)$segments[3],
                    'margin_percent' => isset($segments[4]) ? (float)$segments[4] : 0,
                ];
                if (isset($segments[5]) && trim($segments[5]) !== '') {
                    $decoded = json_decode(trim($segments[5]), true);
                    if (is_array($decoded)) {
                        $payload['name_translations'] = $decoded;
                    }
                }
                $this->numberCatalog->upsert($payload);
                $this->clearAdminState($userDbId);
                $this->sendMessage($chatId, $strings['admin_content_saved'] ?? 'Saved.', []);
                return true;
            case 'await_country_delete':
                $code = strtoupper($trimmed);
                if ($code === '') {
                    $this->sendMessage($chatId, $strings['admin_catalog_remove_prompt'] ?? 'Send the country code to remove.', []);
                    return true;
                }
                $existingOrders = $this->numberOrders->countByCountry($code);
                if ($existingOrders > 0) {
                    $this->numberCatalog->setActive($code, false);
                    $this->sendMessage($chatId, $strings['admin_catalog_deactivated'] ?? 'Country disabled because it has existing orders.', []);
                } else {
                    $this->numberCatalog->delete($code);
                    $this->sendMessage($chatId, $strings['admin_content_saved'] ?? 'Saved.', []);
                }
                $this->clearAdminState($userDbId);
                return true;
            case 'await_country_import':
                $lines = preg_split('/\R+/', $trimmed);
                $count = 0;
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }
                    $parts = preg_split('/\s+/', $line);
                    if (count($parts) !== 2 || !is_numeric($parts[1])) {
                        continue;
                    }
                    $raw = $this->numberCatalog->findRaw($parts[0]);
                    if ($raw) {
                        $payload = $this->prepareCountryPayload($raw, (float)$parts[1]);
                        $this->numberCatalog->upsert($payload);
                        $count++;
                    }
                }
                $this->clearAdminState($userDbId);
                $this->sendMessage(
                    $chatId,
                    sprintf($strings['admin_catalog_import_done'] ?? 'Updated %d countries.', $count),
                    []
                );
                return true;
            case 'await_smm_category_add':
                $parts = array_map('trim', explode('|', $trimmed));
                if (count($parts) < 2) {
                    $this->sendMessage($chatId, $strings['admin_smm_category_prompt'] ?? 'Send as CODE|Name|Caption|SortOrder.', []);
                    return true;
                }
                $sortOrder = isset($parts[3]) && is_numeric($parts[3]) ? (int)$parts[3] : 0;
                $this->smmCatalog->createCategory($parts[0], $parts[1], $parts[2] ?? null, $sortOrder);
                $this->clearAdminState($userDbId);
                $this->sendMessage($chatId, $strings['admin_content_saved'] ?? 'Saved.', []);
                return true;
            case 'await_smm_category_remove':
                $code = trim($trimmed);
                if ($code === '') {
                    $this->sendMessage($chatId, $strings['admin_smm_category_remove_prompt'] ?? 'Send the category code to remove.', []);
                    return true;
                }
                $services = array_filter($this->smmCatalog->allServices(), fn (array $service): bool => strcasecmp((string)$service['category_id'], $code) === 0);
                if ($services !== []) {
                    $this->smmCatalog->setCategoryActive($code, false);
                    $this->sendMessage($chatId, $strings['admin_smm_category_disabled'] ?? 'Category disabled because it still has services/orders.', []);
                } else {
                    $this->smmCatalog->deleteCategory($code);
                    $this->sendMessage($chatId, $strings['admin_content_saved'] ?? 'Saved.', []);
                }
                $this->clearAdminState($userDbId);
                return true;
            case 'await_smm_service_add':
                $parts = array_map('trim', explode('|', $trimmed));
                if (count($parts) < 7) {
                    $this->sendMessage($chatId, $strings['admin_smm_service_prompt'] ?? 'Send as CATEGORY_CODE|ProviderCode|Name|Rate|Min|Max|Currency.', []);
                    return true;
                }
                $targetCategory = $this->smmCatalog->categoryByCode($parts[0]);
                if (!$targetCategory) {
                    $this->sendMessage($chatId, $strings['admin_smm_category_not_found'] ?? 'Category not found.', []);
                    return true;
                }
                $this->smmCatalog->createService([
                    'category_id' => $targetCategory['id'],
                    'provider_code' => $parts[1],
                    'name' => $parts[2],
                    'rate_per_1k' => (float)$parts[3],
                    'min_quantity' => (int)$parts[4],
                    'max_quantity' => (int)$parts[5],
                    'currency' => $parts[6] ?: 'USD',
                ]);
                $this->clearAdminState($userDbId);
                $this->sendMessage($chatId, $strings['admin_content_saved'] ?? 'Saved.', []);
                return true;
            case 'await_smm_service_remove':
                if (!is_numeric($trimmed)) {
                    $this->sendMessage($chatId, $strings['admin_smm_service_remove_prompt'] ?? 'Send the service ID to remove.', []);
                    return true;
                }
                $serviceId = (int)$trimmed;
                $existingOrders = $this->smmOrders->countByService($serviceId);
                if ($existingOrders > 0) {
                    $this->smmCatalog->setServiceActive($serviceId, false);
                    $this->sendMessage($chatId, $strings['admin_smm_service_disabled'] ?? 'Service disabled because it has existing orders.', []);
                } else {
                    $this->smmCatalog->deleteService($serviceId);
                    $this->sendMessage($chatId, $strings['admin_content_saved'] ?? 'Saved.', []);
                }
                $this->clearAdminState($userDbId);
                return true;
            case 'await_referral_config':
                $parts = array_map('trim', explode('|', $trimmed));
                if (count($parts) < 3 || !is_numeric($parts[0]) || !is_numeric($parts[1]) || !is_numeric($parts[2])) {
                    $this->sendMessage($chatId, $strings['admin_referrals_config_prompt'] ?? 'Send flat reward, percent, min order (e.g. 1.5|5|2).', []);
                    return true;
                }
                $config = $this->settings->referrals();
                $config['reward_flat_usd'] = (float)$parts[0];
                $config['reward_percent'] = (float)$parts[1];
                $config['min_order_usd'] = (float)$parts[2];
                $this->settings->updateReferrals($config);
                $this->clearAdminState($userDbId);
                $this->sendMessage($chatId, $strings['admin_content_saved'] ?? 'Saved.', []);
                return true;
            case 'await_broadcast_message':
                $this->handleBroadcast($trimmed, $chatId, $strings);
                $this->clearAdminState($userDbId);
                return true;
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
        $languageCode = $strings['_lang'] ?? null;
        $countries = $this->numberCatalog->list($languageCode);

        if ($countries === []) {
            $fallback = $strings['no_numbers'] ?? 'No numbers available right now.';
            return $title . PHP_EOL . PHP_EOL . $fallback;
        }

        $preview = array_slice($countries, 0, 5);
        $lines = array_map(
            fn (array $country): string => sprintf(
                '%s (%s) • $%0.2f',
                $this->esc($this->countryDisplayName($country)),
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

        $languageCode = $strings['_lang'] ?? null;
        $country = $this->numberCatalog->find($countryCode, $languageCode) ?? [
            'code' => $countryCode,
            'name' => $meta['country_name'] ?? $countryCode,
            'display_name' => $meta['country_name'] ?? $countryCode,
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

    private function prepareCountryPayload(array $raw, ?float $priceOverride = null): array
    {
        if (isset($raw['name_translations']) && is_string($raw['name_translations'])) {
            $decoded = json_decode($raw['name_translations'], true);
            $raw['name_translations'] = is_array($decoded) ? $decoded : null;
        }
        if ($priceOverride !== null) {
            $raw['price_usd'] = $priceOverride;
        }

        return $raw;
    }

    private function buildStartMessage(array $strings, array $userRecord, array $telegramUser): string
    {
        $general = $this->settings->general();
        $template = $general['start_message'] ?? ($strings['welcome'] ?? 'Welcome');
        $userDbId = (int)($userRecord['id'] ?? 0);
        $balance = 0.0;
        if ($userDbId > 0) {
            try {
                $balance = $this->wallets->balance($userDbId, 'USD');
            } catch (Throwable $e) {
            }
        }

        $replacements = [
            '{{user_id}}' => (string)($userRecord['telegram_id'] ?? ''),
            '{{user_name}}' => (string)($telegramUser['first_name'] ?? ($telegramUser['username'] ?? '')),
            '{{balance}}' => number_format($balance, 2),
        ];

        return strtr($template, $replacements);
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
