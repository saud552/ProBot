<?php

declare(strict_types=1);

namespace App\Presentation\Keyboard;

class KeyboardFactory
{
    /**
     * @param array<string, string> $strings
     * @param string $changeLanguageLabel
     * @return array<int, array<int, array<string, string>>>
     */
    public function mainMenu(array $strings, string $changeLanguageLabel): array
    {
        return [
            [
                ['text' => $strings['main_numbers_button'] ?? 'Numbers', 'callback_data' => 'numbers:root'],
                ['text' => $strings['main_smm_button'] ?? 'Boosting', 'callback_data' => 'smm:root'],
            ],
            [
                ['text' => $strings['menu_recharge'] ?? 'Recharge', 'callback_data' => 'requestPoint'],
                ['text' => $strings['menu_support'] ?? 'Support', 'callback_data' => 'support'],
            ],
            [
                ['text' => $strings['menu_agents'] ?? 'Agents', 'callback_data' => 'agents'],
                ['text' => $strings['menu_bot_activations'] ?? 'Activations', 'callback_data' => 'activations'],
            ],
            [
                ['text' => $strings['menu_free_balance'] ?? 'Free Balance', 'callback_data' => 'inviteLink'],
                ['text' => $changeLanguageLabel, 'callback_data' => 'changeLanguage'],
            ],
        ];
    }

    /**
     * @param array<string, string> $strings
     * @param string $backLabel
     * @return array<int, array<int, array<string, string>>>
     */
    public function numbersMenu(array $strings, string $backLabel): array
    {
        return [
            [
                ['text' => $strings['numbers_usd_button'] ?? 'Buy (USD)', 'callback_data' => 'numbers:usd'],
                ['text' => $strings['numbers_stars_button'] ?? 'Buy (Stars)', 'callback_data' => 'numbers:stars'],
            ],
            [
                ['text' => $backLabel, 'callback_data' => 'back'],
            ],
        ];
    }

    /**
     * @param array<string, string> $strings
     * @param string $backLabel
     * @return array<int, array<int, array<string, string>>>
     */
    public function smmMenu(array $strings, string $backLabel): array
    {
        return [
            [
                ['text' => $strings['smm_usd_button'] ?? 'Boost (USD)', 'callback_data' => 'smm:usd'],
                ['text' => $strings['smm_stars_button'] ?? 'Boost (Stars)', 'callback_data' => 'smm:stars'],
            ],
            [
                ['text' => $backLabel, 'callback_data' => 'back'],
            ],
        ];
    }
}
