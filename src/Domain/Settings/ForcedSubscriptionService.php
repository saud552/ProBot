<?php

declare(strict_types=1);

namespace App\Domain\Settings;

use App\Infrastructure\Telegram\TelegramClient;
use Throwable;

class ForcedSubscriptionService
{
    private SettingsService $settings;
    private TelegramClient $telegram;

    public function __construct(SettingsService $settings, TelegramClient $telegram)
    {
        $this->settings = $settings;
        $this->telegram = $telegram;
    }

    /**
     * @param array<int|string, mixed> $strings
     * @return array{allowed: bool, message?: string, keyboard?: array}
     */
    public function validate(int $userId, array $strings): array
    {
        $config = $this->settings->forcedSubscription();
        if (($config['enabled'] ?? false) !== true) {
            return ['allowed' => true];
        }

        $channels = $config['channels'] ?? [];
        if ($channels === []) {
            return ['allowed' => true];
        }

        foreach ($channels as $channel) {
            $chatId = $channel['id'] ?? null;
            if (!$chatId) {
                continue;
            }

            try {
                $response = $this->telegram->call('getChatMember', [
                    'chat_id' => $chatId,
                    'user_id' => $userId,
                ]);

                $status = $response['result']['status'] ?? null;
                if (!in_array($status, ['creator', 'administrator', 'member'], true)) {
                $link = $channel['link'] ?? $config['fallback_link'] ?? '';
                $keyboard = [
                    [
                        [
                            'text' => $strings['subscribe_button'] ?? 'Join Channel',
                            'url' => $link,
                        ],
                    ],
                    [
                        [
                            'text' => $strings['verify_button'] ?? 'Verify',
                            'callback_data' => 'numbers:root',
                        ],
                    ],
                ];

                $messageTemplate = $strings['verify_text'] ?? 'Please join the channel to continue.';
                $message = str_replace('{{channel_link}}', $link, $messageTemplate);

                return [
                    'allowed' => false,
                    'message' => $message,
                    'keyboard' => $keyboard,
                ];
                }
            } catch (Throwable $e) {
                // في حالة فشل التحقق (مثلاً PARTICIPANT_ID_INVALID)، نعتبره غير مشترك
                error_log("ForcedSubscription validation error for user {$userId}: " . $e->getMessage());
                $link = $channel['link'] ?? $config['fallback_link'] ?? '';
                $keyboard = [
                    [
                        [
                            'text' => $strings['subscribe_button'] ?? 'Join Channel',
                            'url' => $link,
                        ],
                    ],
                    [
                        [
                            'text' => $strings['verify_button'] ?? 'Verify',
                            'callback_data' => 'numbers:root',
                        ],
                    ],
                ];

                $messageTemplate = $strings['verify_text'] ?? 'Please join the channel to continue.';
                $message = str_replace('{{channel_link}}', $link, $messageTemplate);

                return [
                    'allowed' => false,
                    'message' => $message,
                    'keyboard' => $keyboard,
                ];
            }
        }

        return ['allowed' => true];
    }
}
