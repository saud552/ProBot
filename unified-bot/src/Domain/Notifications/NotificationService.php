<?php

declare(strict_types=1);

namespace App\Domain\Notifications;

use App\Domain\Settings\SettingsService;
use App\Infrastructure\Telegram\TelegramClient;

class NotificationService
{
    private SettingsService $settings;
    private TelegramClient $telegram;

    public function __construct(SettingsService $settings, TelegramClient $telegram)
    {
        $this->settings = $settings;
        $this->telegram = $telegram;
    }

    /**
     * @param array<string, mixed> $order
     * @param array<string, mixed> $country
     */
    public function notifyPurchase(array $order, array $country, int $telegramUserId): void
    {
        $config = $this->settings->notifications();
        $channelId = $config['sales_channel_id'] ?? null;
        if (!$channelId) {
            return;
        }

        $text = sprintf(
            "✅ New purchase\nCountry: %s (%s)\nNumber: <code>%s</code>\nPrice: $%0.2f\nHash: %s\nUser: <code>%d</code>",
            $country['name'],
            $country['code'],
            htmlspecialchars((string)$order['number'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            (float)$order['price_usd'],
            htmlspecialchars((string)$order['hash_code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $telegramUserId
        );

        $this->telegram->call('sendMessage', [
            'chat_id' => $channelId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    /**
     * @param array<string, mixed> $order
     * @param array<string, mixed> $codeData
     */
    public function notifyCodeDelivered(array $order, array $codeData): void
    {
        $config = $this->settings->notifications();
        $channelId = $config['success_channel_id'] ?? null;
        if (!$channelId) {
            return;
        }

        $text = sprintf(
            "🔐 Code delivered\nNumber: <code>%s</code>\nCode: <code>%s</code>\nPassword: <code>%s</code>",
            htmlspecialchars((string)$order['number'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars((string)$codeData['code'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars((string)$codeData['password'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );

        $this->telegram->call('sendMessage', [
            'chat_id' => $channelId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    /**
     * @param array<string, mixed> $ticket
     */
    public function notifyTicketUpdate(array $ticket, string $message, string $senderType): void
    {
        $config = $this->settings->notifications();
        $channelId = $config['support_channel_id'] ?? null;
        $admins = $this->settings->admins();

        $title = $senderType === 'admin' ? '👨‍💼 Admin Reply' : '📩 User Message';
        $text = sprintf(
            "%s\nTicket #%d • %s\nStatus: %s\n\n%s",
            $title,
            $ticket['id'],
            $ticket['subject'] ?? '-',
            strtoupper((string)($ticket['status'] ?? 'open')),
            $message
        );

        if ($channelId) {
            $this->telegram->call('sendMessage', [
                'chat_id' => $channelId,
                'text' => $text,
            ]);
        }

        foreach ($admins as $adminId) {
            $this->telegram->call('sendMessage', [
                'chat_id' => $adminId,
                'text' => $text,
            ]);
        }
    }

    public function notifyAdminAction(string $message): void
    {
        $config = $this->settings->notifications();
        $channelId = $config['support_channel_id'] ?? null;
        if ($channelId) {
            $this->telegram->call('sendMessage', [
                'chat_id' => $channelId,
                'text' => "🛠️ Admin Action\n" . $message,
            ]);
        }

        foreach ($this->settings->admins() as $adminId) {
            $this->telegram->call('sendMessage', [
                'chat_id' => $adminId,
                'text' => "🛠️ Admin Action\n" . $message,
            ]);
        }
    }
}
