<?php

declare(strict_types=1);

use Numbers\Database\Connection;
use Numbers\Language\LanguageManager;
use Numbers\Service\ActionLocker;
use Numbers\Storage\DatabaseStorage;
use Numbers\Support\Conversation;
use Numbers\Telegram\TelegramClient;

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/vals.php';
require_once __DIR__ . '/contries.php';
require_once __DIR__ . '/api.php';

$languageManager = new LanguageManager(require BASE_PATH . '/lang/translations.php');
$connection = new Connection(BASE_PATH . '/storage/database.sqlite');
$pdo = $connection->getPdo();
$storage = new DatabaseStorage($pdo, [
    'points' => BASE_PATH . '/points.json',
    'stats' => BASE_PATH . '/stats.json',
    'operations' => BASE_PATH . '/operations.json',
    'invites' => BASE_PATH . '/invites.json',
    'bans' => BASE_PATH . '/bans.json',
    'info' => BASE_PATH . '/info.json',
    'contries' => BASE_PATH . '/contries.json',
    'langs' => BASE_PATH . '/langs.json',
    'settings' => BASE_PATH . '/settings.json',
]);
$actionLocker = new ActionLocker($pdo);

$points = $storage->load('points', []);
$stats = $storage->load('stats', []);
$op = $storage->load('operations', []);
$invite = $storage->load('invites', []);
$bans = $storage->load('bans', []);
$info = $storage->load('info', []);
$contries = $storage->load('contries', []);
$langs = $storage->load('langs', []);
$settings = $storage->load('settings', [
    'forced_subscription' => [
        'enabled' => true,
        'channel_id' => $ch5,
        'channel_link' => $ch6,
    ],
    'pricing' => [
        'margin_percent' => 0,
    ],
    'maintenance' => [
        'enabled' => false,
        'message' => null,
    ],
    'stars' => [
        'usd_per_star' => 0.011,
    ],
]);

$telegramClient = new TelegramClient($token);
define('API_KEY', $token);
const VERIFY_SUBSCRIPTION_CALLBACK = 'verify_subscription';
const STAR_OPERATIONS_KEY = 'stars';

function bot(string $method, array $datas = [])
{
    global $telegramClient;

    try {
        $response = $telegramClient->call($method, $datas);
        if ($response === null) {
            return null;
        }
        return json_decode(json_encode($response), false);
    } catch (Throwable $e) {
        return null;
    }
}

$webhookLink = "https://" . $_SERVER["SERVER_NAME"] . $_SERVER["PHP_SELF"];
if (isset($_GET['setup_webhook'])) {
    $response = bot('setWebhook', ['url' => $webhookLink]);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function check_member($id, $chat)
{
    $response = bot('getChatMember', ["chat_id" => $chat, "user_id" => $id]);
    if (!$response || ($response->ok ?? false) !== true || !isset($response->result)) {
        return false;
    }

    $status = $response->result->status ?? null;
    if ($status === 'left' || $status === 'kicked' || $status === null) {
        return false;
    }
    return true;
}

function mkBtn($btn)
{
    $res = array();
    foreach ($btn as $d) {
        $r = array();
        foreach ($d as $k => $v) {
            $r[] = ['text' => $k, 'callback_data' => $v];
        }
        $res[] = $r;
    }
    return $res;
}

function send($text, $btn = null, $idValue = null)
{
    if ($idValue == null) {
        global $id;
        $idValue = $id;
    }
    $data = array();
    $data['chat_id'] = $idValue;
    $data['text'] = $text;
    $data['parse_mode'] = 'html';
    if ($btn != null) {
        $data['reply_markup'] = json_encode([
            'inline_keyboard' => $btn
        ]);
    }
    return bot('sendMessage', $data);
}

function edit($text, $btn = null)
{
    $data = array();
    global $id;
    global $message_id;
    $data['chat_id'] = $id;
    $data['text'] = $text;
    $data['parse_mode'] = 'html';
    $data['message_id'] = $message_id;
    if ($btn != null) {
        $data['reply_markup'] = json_encode([
            'inline_keyboard' => $btn
        ]);
    }
    return bot('editMessageText', $data);
}

function savePoint()
{
    global $points, $storage;
    $storage->persist('points', $points);
}

function saveStats()
{
    global $stats, $storage;
    $storage->persist('stats', $stats);
}

function saveOp()
{
    global $op, $storage;
    $storage->persist('operations', $op);
}

function saveInvite()
{
    global $invite, $storage;
    $storage->persist('invites', $invite);
}

function saveBans()
{
    global $bans, $storage;
    $storage->persist('bans', $bans);
}

function saveInfo()
{
    global $info, $storage;
    $storage->persist('info', $info);
}

function saveContries()
{
    global $contries, $storage;
    $storage->persist('contries', $contries);
}

function saveSettings()
{
    global $settings, $storage;
    $storage->persist('settings', $settings);
}

function saveLangs()
{
    global $langs, $storage;
    $storage->persist('langs', $langs);
}

function respondWithPurchaseDetails(
    string $countryCode,
    string $number,
    float $price,
    string $hashCode,
    array $txt,
    bool $asEdit = true
): void {
    global $names, $ch1, $point, $id;

    $countryName = $names[$countryCode] ?? $countryCode;

    $channelMessage = "
✅- تم شراء رقم من البوت بنجاح -✅

☎️ - الرقم: <code>{$number}</code>
🌎 - الدولة: {$countryName}
💢 - رمز الدولة: {$countryCode}
💵- السعر :  {$price}$
💰 - الرصيد: {$point}
🆔 - الايدي: <code>{$id}</code>
";
    send($channelMessage, null, $ch1);

    $userMessage = str_replace(
        ["__c__", "__num__", "__p__"],
        [$countryName, $number, $price],
        $txt['purchase_success']
    );

    $buttons = mkBtn([
        [
            $txt['request_code'] => "getCode#{$hashCode}#{$countryCode}#{$number}"
        ]
    ]);

    if ($asEdit) {
        edit($userMessage, $buttons);
    } else {
        send($userMessage, $buttons);
    }
}

function buildSubscriptionKeyboard(array $txt, string $subscriptionLink): array
{
    $subscribeLabel = $txt['subscribe_button'] ?? 'اشترك في القناة';
    $verifyLabel = $txt['verify_button'] ?? 'تحقق';

    return [
        [
            ['text' => $subscribeLabel, 'url' => $subscriptionLink],
        ],
        [
            ['text' => $verifyLabel, 'callback_data' => VERIFY_SUBSCRIPTION_CALLBACK],
        ],
    ];
}

function handlePreCheckoutQuery($query): void
{
    $payload = $query->invoice_payload ?? '';
    if (!$payload) {
        bot('answerPreCheckoutQuery', [
            'pre_checkout_query_id' => $query->id,
            'ok' => false,
            'error_message' => 'Invoice payload is missing.',
        ]);
        return;
    }

    global $op;
    if (!isset($op[STAR_OPERATIONS_KEY][$payload])) {
        bot('answerPreCheckoutQuery', [
            'pre_checkout_query_id' => $query->id,
            'ok' => false,
            'error_message' => 'Invoice is no longer valid.',
        ]);
        return;
    }

    bot('answerPreCheckoutQuery', [
        'pre_checkout_query_id' => $query->id,
        'ok' => true,
    ]);
}

function finalizeStarPurchase(array $operation): void
{
    global $api, $stats, $points, $point, $langs, $languageManager, $invitePoint;
    global $requestLink, $supportLink, $settings, $ch6, $botUser, $op, $admin;

    $userId = (int)$operation['user_id'];
    $countryCode = $operation['country'];
    $price = (float)($operation['price'] ?? 0);
    $payload = $operation['payload'] ?? '';

    $currentLang = Conversation::ensureLanguageCode($languageManager, $langs[$userId] ?? null);
    if (($langs[$userId] ?? null) !== $currentLang) {
        $langs[$userId] = $currentLang;
        saveLangs();
    }

    $userBalance = $points[$userId] ?? 0;
    $refLink = "https://t.me/{$botUser}?start={$userId}";
    $replacements = Conversation::buildReplacements(
        $userId,
        $userBalance,
        $refLink,
        $invitePoint,
        $requestLink,
        $supportLink,
        $settings,
        $ch6
    );
    $txt = Conversation::prepareStrings($languageManager, $currentLang, $replacements);

    $numberData = $api->getNumber($countryCode);
    if (!is_array($numberData)) {
        $errorMessage = $txt['purchase_failed'] ?? 'تعذر إتمام عملية الشراء، يرجى التواصل مع الدعم.';
        send($errorMessage, null, $userId);
        send(
            "⚠️ فشل إتمام عملية شراء بالنجوم للمستخدم {$userId}، الدولة {$countryCode}، المرجع {$payload}.",
            null,
            $admin
        );
        unset($op[STAR_OPERATIONS_KEY][$payload]);
        saveOp();
        return;
    }

    $number = $numberData['number'];
    $hashCode = $numberData['hash_code'];

    $stats['all']['trybuy'] = ($stats['all']['trybuy'] ?? 0) + 1;
    saveStats();

    $GLOBALS['id'] = $userId;
    $point = $userBalance;
    respondWithPurchaseDetails($countryCode, $number, $price, $hashCode, $txt, false);

    unset($op[STAR_OPERATIONS_KEY][$payload]);
    saveOp();
}

function handleSuccessfulPayment($message): void
{
    $payment = $message->successful_payment ?? null;
    if (!$payment) {
        return;
    }

    $payload = $payment->invoice_payload ?? '';
    $userId = $message->from->id ?? 0;

    global $op;
    if (!$payload || !isset($op[STAR_OPERATIONS_KEY][$payload])) {
        return;
    }

    $operation = $op[STAR_OPERATIONS_KEY][$payload];
    if ((int)$operation['user_id'] !== (int)$userId) {
        return;
    }

    $operation['payload'] = $payload;
    finalizeStarPurchase($operation);
    exit;
}

$back = mkBtn(array(
    array(
        "رجوع" => "back"
    )
));

$payload = file_get_contents('php://input');
if (!$payload) {
    exit;
}
$update = json_decode($payload);
if (!$update) {
    exit;
}

if (isset($update->pre_checkout_query)) {
    handlePreCheckoutQuery($update->pre_checkout_query);
    exit;
}

if (isset($update->message->successful_payment)) {
    handleSuccessfulPayment($update->message);
}

if (isset($update->message)) {
    $message = $update->message;
    $chat_id = $message->chat->id ?? null;
    $text = $message->text ?? '';
    $ex = explode(" ", $text ?? '');
    $first_name = $message->from->first_name ?? '';
    $username = $message->from->username ?? '';
    $id = $message->from->id ?? 0;
    $message_id = $message->message_id ?? 0;
    $entities = $message->entities ?? [];
    $language_code = $message->from->language_code ?? 'ar';
    $tc = $message->chat->type ?? 'private';
    $re_message = $message->reply_to_message ?? null;
    $re_text = $re_message->text ?? null;
    $data = null;
    $exData = [];
    $callbackId = null;
} elseif (isset($update->callback_query)) {
    $chat_id = $update->callback_query->message->chat->id ?? null;
    $id = $update->callback_query->from->id ?? 0;
    $first_name = $update->callback_query->from->first_name ?? '';
    $message_id = $update->callback_query->message->message_id ?? 0;
    $data = $update->callback_query->data ?? '';
    $exData = explode("#", $data);
    $text = null;
    $ex = [];
    $username = $update->callback_query->from->username ?? '';
    $entities = [];
    $language_code = $update->callback_query->from->language_code ?? 'ar';
    $tc = $update->callback_query->message->chat->type ?? 'private';
    $re_message = null;
    $re_text = null;
    $callbackId = $update->callback_query->id ?? null;
} else {
    exit;
}

$point = $points[$id] ?? 0;
$refLink = "https://t.me/{$botUser}?start={$id}";
$link = $refLink;

if (!empty($exData) && $exData[0] === 'lang') {
    $selectedLang = Conversation::ensureLanguageCode($languageManager, $exData[1] ?? null);
    $langs[$id] = $selectedLang;
    saveLangs();
    $mainMenuLabel = $languageManager->label($selectedLang, 'main_menu', 'Main Menu');
    edit("⬇️⬇️⬇️⬇️⬇️⬇️⬇️⬇️⬇️⬇️", [[['text' => $mainMenuLabel, 'callback_data' => 'back']]]);
    exit;
}

if (!isset($langs[$id])) {
    $prompt = Conversation::languagePrompt($languageManager);
    $keyboard = mkBtn($prompt['buttons']);
    if (!empty($text)) {
        send($prompt['text'], $keyboard);
    } else {
        edit($prompt['text'], $keyboard);
    }
    exit;
}

$currentLang = Conversation::ensureLanguageCode($languageManager, $langs[$id] ?? null);
if (($langs[$id] ?? null) !== $currentLang) {
    $langs[$id] = $currentLang;
    saveLangs();
}

$replacements = Conversation::buildReplacements(
    $id,
    $point,
    $refLink,
    $invitePoint,
    $requestLink,
    $supportLink,
    $settings,
    $ch6
);
$txt = Conversation::prepareStrings($languageManager, $currentLang, $replacements);

$forcedSubscription = $settings['forced_subscription'] ?? [];
$subscriptionEnabled = $forcedSubscription['enabled'] ?? true;
$subscriptionChannelId = $forcedSubscription['channel_id'] ?? $ch5;
$subscriptionLink = $forcedSubscription['channel_link'] ?? $ch6;

if ($subscriptionEnabled) {
    $isMember = check_member($id, $subscriptionChannelId);
    if (($data ?? null) === VERIFY_SUBSCRIPTION_CALLBACK && $callbackId) {
        if ($isMember) {
            bot('answerCallbackQuery', [
                'callback_query_id' => $callbackId,
                'text' => $txt['subscription_verified'] ?? 'تم التحقق من الاشتراك.',
                'show_alert' => true,
            ]);
            $data = 'back';
            $exData = [$data];
        } else {
            bot('answerCallbackQuery', [
                'callback_query_id' => $callbackId,
                'text' => $txt['subscription_not_verified'] ?? 'لم يتم التحقق بعد.',
                'show_alert' => true,
            ]);
        }
    }

    if (!$isMember) {
        $keyboard = buildSubscriptionKeyboard($txt, $subscriptionLink);
        if (!empty($text)) {
            send($txt['verify_text'], $keyboard);
        } else {
            edit($txt['verify_text'], $keyboard);
        }
        exit;
    }
}

$api = new Api($api_key);
if ($id == $admin) {
    $balance = $api->getBalance() ?? 0;
    require "admin.php";
} else {
    require "member.php";
}