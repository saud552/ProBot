<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use PDO;

class ReferralRepository extends Repository
{
    public function findByReferred(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM referrals WHERE referred_user_id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    public function create(int $referrerId, int $referredId): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO referrals (referrer_id, referred_user_id)
             VALUES (:referrer, :referred)
             ON DUPLICATE KEY UPDATE referrer_id = referrer_id'
        );
        $stmt->execute([
            'referrer' => $referrerId,
            'referred' => $referredId,
        ]);

        return $this->findByReferred($referredId)
            ?? [
                'referrer_id' => $referrerId,
                'referred_user_id' => $referredId,
                'status' => 'pending',
                'reward_amount' => 0,
                'reward_currency' => 'USD',
            ];
    }

    public function markEligible(int $id, float $amount, string $currency, ?string $orderReference = null): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE referrals
             SET status = :status,
                 reward_amount = :amount,
                 reward_currency = :currency,
                 order_reference = :reference,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'status' => 'eligible',
            'amount' => $amount,
            'currency' => $currency,
            'reference' => $orderReference,
            'id' => $id,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function eligibleRewards(int $referrerId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM referrals WHERE referrer_id = :referrer AND status = :status'
        );
        $stmt->execute([
            'referrer' => $referrerId,
            'status' => 'eligible',
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function markRewarded(array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare(
            "UPDATE referrals
             SET status = 'rewarded', rewarded_at = NOW(), updated_at = NOW()
             WHERE id IN ({$placeholders})"
        );
        $stmt->execute(array_map('intval', $ids));
    }

    /**
     * @return array<string, float|int>
     */
    public function stats(int $referrerId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                COUNT(*) AS total,
                SUM(status = "pending") AS pending_count,
                SUM(status = "eligible") AS eligible_count,
                SUM(status = "rewarded") AS rewarded_count,
                SUM(CASE WHEN status = "eligible" THEN reward_amount ELSE 0 END) AS eligible_amount,
                SUM(CASE WHEN status = "rewarded" THEN reward_amount ELSE 0 END) AS rewarded_amount
             FROM referrals
             WHERE referrer_id = :referrer'
        );
        $stmt->execute(['referrer' => $referrerId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int)($stats['total'] ?? 0),
            'pending_count' => (int)($stats['pending_count'] ?? 0),
            'eligible_count' => (int)($stats['eligible_count'] ?? 0),
            'rewarded_count' => (int)($stats['rewarded_count'] ?? 0),
            'eligible_amount' => (float)($stats['eligible_amount'] ?? 0),
            'rewarded_amount' => (float)($stats['rewarded_amount'] ?? 0),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listByReferrer(int $referrerId, ?string $status = null, int $limit = 20): array
    {
        if ($status) {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM referrals
                 WHERE referrer_id = :referrer AND status = :status
                 ORDER BY created_at DESC
                 LIMIT :limit'
            );
            $stmt->bindValue('status', $status);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM referrals
                 WHERE referrer_id = :referrer
                 ORDER BY created_at DESC
                 LIMIT :limit'
            );
        }

        $stmt->bindValue('referrer', $referrerId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function revertByReference(string $reference): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE referrals
             SET status = "pending",
                 reward_amount = 0,
                 reward_currency = reward_currency,
                 order_reference = NULL,
                 updated_at = NOW()
             WHERE order_reference = :reference AND status IN ("pending","eligible")'
        );
        $stmt->execute(['reference' => $reference]);
    }
}
