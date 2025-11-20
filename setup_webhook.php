<?php

declare(strict_types=1);

$token = '7174411191:AAHhRIakJPu0B_9bxMsIlkGfMvRNYsYge7A';

// الحصول على webhook URL من السطر الأول من الأوامر
$webhookUrl = $argv[1] ?? '';

if (empty($webhookUrl)) {
    echo "Usage: php setup_webhook.php <webhook_url>\n";
    echo "Example: php setup_webhook.php https://your-domain.com/index.php\n";
    exit(1);
}

$apiUrl = "https://api.telegram.org/bot{$token}/setWebhook";
$data = ['url' => $webhookUrl];

$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($apiUrl, false, $context);

if ($result === false) {
    echo "Error: Failed to set webhook\n";
    exit(1);
}

$response = json_decode($result, true);
if ($response['ok'] ?? false) {
    echo "✓ Webhook set successfully!\n";
    echo "URL: {$webhookUrl}\n";
    echo "Response: {$result}\n";
} else {
    echo "✗ Failed to set webhook\n";
    echo "Response: {$result}\n";
    exit(1);
}
