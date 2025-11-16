<?php

declare(strict_types=1);

namespace App\Domain\Referrals;

use App\Domain\Settings\SettingsService;
use App\Domain\Users\UserManager;
use App\Domain\Wallet\TransactionService;
use App\Domain\Wallet\WalletService;
use App\Infrastructure\Repository\ReferralRepository;
use RuntimeException;

class ReferralService
{
    private ReferralRepository $referrals;
    private WalletService $wallets;
    private TransactionService $transactions;
    private SettingsService $settings;
    private UserManager $users;
    private string $fallbackBotUsername;

    public function __construct(
        ReferralRepository $referrals,
        WalletService $wallets,
        TransactionService $transactions,
        SettingsService $settings,
        UserManager $users,
        string $fallbackBotUsername = 'SP1BOT'
    ) {
        $this->referrals = $referrals;
        $this->wallets = $wallets;
        $this->transactions = $transactions;
        $this->settings = $settings;
        $this->users = $users;
        $this->fallbackBotUsername = $fallbackBotUsername;
    }

    public function captureFromPayload(int $currentUserId, string $payload): bool
    {
        $config = $this->settings->referrals();
        if (!($config['enabled'] ?? false)) {
            return false;
        }

        $payload = trim($payload);
        if ($payload === '') {
            return false;
        }

        if (stripos($payload, 'ref_') === 0) {
            $code = substr($payload, 4);
        } elseif (stripos($payload, 'ref') === 0) {
            $code = substr($payload, 3);
        } else {
            return false;
        }

        $code = ltrim($code, '_');
        $referrerId = $this->decodeCode($code);
        if ($referrerId === null) {
            return false;
        }

        return $this->attachReferral($referrerId, $currentUserId);
    }

    public function attachReferral(int $referrerId, int $referredId): bool
    {
        $config = $this->settings->referrals();
        if (!($config['enabled'] ?? false)) {
            return false;
        }

        if ($referrerId <= 0 || $referredId <= 0 || $referrerId === $referredId) {
            return false;
        }

        if ($this->referrals->findByReferred($referredId)) {
            return false;
        }

        $maxPerUser = (int)($config['max_per_user'] ?? 0);
        if ($maxPerUser > 0) {
            $stats = $this->referrals->stats($referrerId);
            if ($stats['total'] >= $maxPerUser) {
                return false;
            }
        }

        if (!$this->users->findById($referrerId) || !$this->users->findById($referredId)) {
            return false;
        }

        $this->referrals->create($referrerId, $referredId);
        $this->users->assignReferrerIfEmpty($referredId, $referrerId);

        return true;
    }

    public function handleSuccessfulOrder(int $referredUserId, float $orderAmount, string $orderReference): void
    {
        $config = $this->settings->referrals();
        if (!($config['enabled'] ?? false)) {
            return;
        }

        $record = $this->referrals->findByReferred($referredUserId);
        if (!$record || ($record['status'] ?? 'pending') !== 'pending') {
            return;
        }

        $minOrder = (float)($config['min_order_usd'] ?? 0);
        if ($orderAmount < $minOrder) {
            return;
        }

        $flat = max(0.0, (float)($config['reward_flat_usd'] ?? 0));
        $percent = max(0.0, (float)($config['reward_percent'] ?? 0));
        $percentReward = $percent > 0 ? ($orderAmount * $percent / 100) : 0.0;
        $reward = round($flat + $percentReward, 4);

        if ($reward <= 0) {
            return;
        }

        $this->referrals->markEligible(
            (int)$record['id'],
            $reward,
            $record['reward_currency'] ?? 'USD',
            $orderReference
        );
    }

    public function withdraw(int $referrerId): float
    {
        $config = $this->settings->referrals();
        if (!($config['enabled'] ?? false)) {
            return 0.0;
        }

        $eligible = $this->referrals->eligibleRewards($referrerId);
        if ($eligible === []) {
            return 0.0;
        }

        $total = 0.0;
        $ids = [];
        foreach ($eligible as $reward) {
            $total += (float)$reward['reward_amount'];
            $ids[] = (int)$reward['id'];
        }

        if ($total <= 0) {
            return 0.0;
        }

        $this->wallets->credit($referrerId, $total, 'USD');
        $this->transactions->log(
            $referrerId,
            'credit',
            'referral',
            $total,
            'USD',
            null,
            ['referrals' => $ids]
        );

        $this->referrals->markRewarded($ids);

        return $total;
    }

    /**
     * @return array<string, mixed>
     */
    public function stats(int $referrerId): array
    {
        $stats = $this->referrals->stats($referrerId);
        $stats['code'] = $this->generateCode($referrerId);
        $stats['link'] = $this->generateShareLink($referrerId);

        return $stats;
    }

    public function generateShareLink(int $userId): string
    {
        $username = $this->settings->referrals()['bot_username'] ?? $this->fallbackBotUsername;
        $username = ltrim($username, '@');

        return sprintf(
            'https://t.me/%s?start=ref_%s',
            $username,
            $this->generateCode($userId)
        );
    }

    public function generateCode(int $userId): string
    {
        if ($userId <= 0) {
            throw new RuntimeException('Invalid user id for referral code.');
        }

        return strtoupper(base_convert((string)$userId, 10, 36));
    }

    private function decodeCode(string $code): ?int
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return null;
        }

        if (!preg_match('/^[0-9A-Z]+$/', $code)) {
            return null;
        }

        $decoded = base_convert($code, 36, 10);
        if ($decoded === false || $decoded === '') {
            return null;
        }

        $value = (int)$decoded;
        return $value > 0 ? $value : null;
    }
}
