<?php

declare(strict_types=1);

namespace Numbers\Support;

use Numbers\Language\LanguageManager;

class Conversation
{
    public const LANGUAGE_PROMPT = "
قم باختيار اللغة.
Please choose a language.
Пожалуйста, выберите язык.
 لطفاً زبان را انتخاب کنید.
 請選擇語言。
 请选擇语言。
";

    public static function ensureLanguageCode(
        LanguageManager $languageManager,
        ?string $code,
        string $fallback = 'ar'
    ): string {
        if ($code && $languageManager->has($code)) {
            return $code;
        }

        return $fallback;
    }

    /**
     * @param mixed $balance
     * @param array<string, mixed> $settings
     * @return array<string, string>
     */
    public static function buildReplacements(
        int $userId,
        $balance,
        string $refLink,
        float $invitePoint,
        string $chargeLink,
        string $supportLink,
        array $settings,
        string $defaultChannelLink
    ): array {
        $channelLink = $settings['forced_subscription']['channel_link'] ?? $defaultChannelLink;

        return [
            '{{channel_link}}' => $channelLink,
            '{{invite_point}}' => (string)$invitePoint,
            '{{ref_link}}' => $refLink,
            '{{charge_link}}' => $chargeLink,
            '{{support_link}}' => $supportLink,
            '{{user_id}}' => (string)$userId,
            '{{balance}}' => (string)$balance,
        ];
    }

    /**
     * @param array<string, string> $replacements
     * @return array<string, mixed>
     */
    public static function prepareStrings(
        LanguageManager $languageManager,
        string $code,
        array $replacements
    ): array {
        $strings = $languageManager->strings($code);
        foreach ($strings as $key => $value) {
            if (is_string($value)) {
                $strings[$key] = str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    $value
                );
            }
        }

        return $strings;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function languageButtons(LanguageManager $languageManager): array
    {
        $buttons = [];
        foreach ($languageManager->options() as $code => $label) {
            $buttons[] = [$label => "lang#{$code}"];
        }

        return $buttons;
    }

    /**
     * @return array{text: string, buttons: array<int, array<string, string>>}
     */
    public static function languagePrompt(
        LanguageManager $languageManager,
        ?string $currentLangCode = null,
        string $fallbackBackLabel = 'Back'
    ): array {
        $buttons = self::languageButtons($languageManager);

        if ($currentLangCode !== null) {
            $label = $languageManager->label($currentLangCode, 'back', $fallbackBackLabel);
            $buttons[] = [$label => 'back'];
        }

        return [
            'text' => self::LANGUAGE_PROMPT,
            'buttons' => $buttons,
        ];
    }
}
