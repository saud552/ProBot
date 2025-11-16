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
    public function mainMenu(array $strings, string $changeLanguageLabel, array $options = []): array
    {
        $features = $options['features'] ?? [];
        $isAdmin = (bool)($options['is_admin'] ?? false);

        $rows = [];

        $numbersEnabled = $features['numbers_enabled'] ?? true;
        $smmEnabled = $features['smm_enabled'] ?? true;
        if ($numbersEnabled || $smmEnabled) {
            $row = [];
            if ($numbersEnabled) {
                $row[] = ['text' => $strings['main_numbers_button'] ?? 'Numbers', 'callback_data' => 'numbers:root'];
            }
            if ($smmEnabled) {
                $row[] = ['text' => $strings['main_smm_button'] ?? 'Boosting', 'callback_data' => 'smm:root'];
            }
            if ($row !== []) {
                $rows[] = $row;
            }
        }

        $row = [];
        $row[] = ['text' => $strings['menu_recharge'] ?? 'Recharge', 'callback_data' => 'requestPoint'];
        if ($features['support_enabled'] ?? true) {
            $row[] = ['text' => $strings['menu_support'] ?? 'Support', 'callback_data' => 'support:root'];
        }
        $rows[] = $row;

        $rows[] = [
            ['text' => $strings['menu_agents'] ?? 'Agents', 'callback_data' => 'agents'],
            ['text' => $strings['menu_bot_activations'] ?? 'Activations', 'callback_data' => 'activations'],
        ];

        $referralsEnabled = $features['referrals_enabled'] ?? true;
        $row = [];
        if ($referralsEnabled) {
            $row[] = ['text' => $strings['menu_free_balance'] ?? 'Free Balance', 'callback_data' => 'inviteLink'];
        }
        $row[] = ['text' => $changeLanguageLabel, 'callback_data' => 'changeLanguage'];
        $rows[] = $row;

        if ($isAdmin) {
            $rows[] = [
                ['text' => $strings['admin_panel_button'] ?? 'Admin Panel', 'callback_data' => 'admin:root'],
            ];
        }

        return $rows;
    }

    /**
     * @param array<string, string> $strings
     * @param string $backLabel
     * @return array<int, array<int, array<string, string>>>
     */
    public function numbersMenu(array $strings, string $backLabel, bool $starsEnabled = true): array
    {
        $row = [
            ['text' => $strings['numbers_usd_button'] ?? 'Buy (USD)', 'callback_data' => 'numbers:usd'],
        ];

        if ($starsEnabled) {
            $row[] = ['text' => $strings['numbers_stars_button'] ?? 'Buy (Stars)', 'callback_data' => 'numbers:stars'];
        }

        return [
            $row,
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
    public function smmMenu(array $strings, string $backLabel, bool $starsEnabled = true): array
    {
        $row = [
            ['text' => $strings['smm_usd_button'] ?? 'Boost (USD)', 'callback_data' => 'smm:usd'],
        ];

        if ($starsEnabled) {
            $row[] = ['text' => $strings['smm_stars_button'] ?? 'Boost (Stars)', 'callback_data' => 'smm:stars'];
        }

        return [
            $row,
            [
                ['text' => $backLabel, 'callback_data' => 'back'],
            ],
        ];
    }
}
