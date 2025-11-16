<?php

declare(strict_types=1);

namespace App\Domain\Settings;

use App\Infrastructure\Repository\SettingsRepository;

class SettingsService
{
    private SettingsRepository $settings;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $cache = [];

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array<string, mixed>
     */
    public function forcedSubscription(): array
    {
        return $this->remember('forced_subscription', function (): array {
            return $this->settings->find('forced_subscription') ?? [
                'enabled' => false,
                'channels' => [],
                'fallback_link' => null,
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function notifications(): array
    {
        return $this->remember('notifications', function (): array {
            return $this->settings->find('notifications') ?? [
                'sales_channel_id' => null,
                'success_channel_id' => null,
                'support_channel_id' => null,
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function stars(): array
    {
        return $this->remember('stars', function (): array {
            return $this->settings->find('stars') ?? [
                'usd_per_star' => 0.011,
                'enabled' => true,
            ];
        });
    }

    /**
     * @return array<string, bool>
     */
    public function features(): array
    {
        return $this->remember('features', function (): array {
            return $this->settings->find('features') ?? [
                'numbers_enabled' => true,
                'smm_enabled' => true,
                'support_enabled' => true,
                'referrals_enabled' => true,
                'stars_enabled' => true,
            ];
        });
    }

    /**
     * @return array<int>
     */
    public function admins(): array
    {
        $config = $this->remember('admins', function (): array {
            return $this->settings->find('admins') ?? ['ids' => []];
        });

        return array_map('intval', $config['ids'] ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    public function referrals(): array
    {
        return $this->remember('referrals', function (): array {
            return $this->settings->find('referrals') ?? [
                'enabled' => false,
                'bot_username' => 'SP1BOT',
                'reward_flat_usd' => 0.0,
                'reward_percent' => 0.0,
                'min_order_usd' => 0.0,
                'max_per_user' => 0,
            ];
        });
    }

    public function updateStars(array $value): void
    {
        $this->update('stars', $value);
    }

    public function updateForcedSubscription(array $value): void
    {
        $this->update('forced_subscription', $value);
    }

    public function updateFeatures(array $value): void
    {
        $this->update('features', $value);
    }

    public function updateReferrals(array $value): void
    {
        $this->update('referrals', $value);
    }

    public function refresh(string $key): void
    {
        unset($this->cache[$key]);
    }

    /**
     * @param callable():array<string, mixed> $resolver
     * @return array<string, mixed>
     */
    private function remember(string $key, callable $resolver): array
    {
        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $resolver();
        }

        return $this->cache[$key];
    }

    private function update(string $key, array $value): void
    {
        $this->settings->upsert($key, $value);
        $this->cache[$key] = $value;
    }
}
