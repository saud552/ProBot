<?php

declare(strict_types=1);

namespace App\Domain\Numbers;

use App\Domain\Notifications\NotificationService;
use App\Domain\Support\TicketService;
use App\Domain\Wallet\TransactionService;
use App\Infrastructure\Repository\NumberOrderRepository;
use RuntimeException;
use Throwable;

class NumberCodeService
{
    private NumberProviderInterface $provider;
    private NumberOrderRepository $orders;
    private NotificationService $notifications;
    private TicketService $tickets;
    private TransactionService $transactions;

    public function __construct(
        NumberProviderInterface $provider,
        NumberOrderRepository $orders,
        NotificationService $notifications,
        TicketService $tickets,
        TransactionService $transactions
    ) {
        $this->provider = $provider;
        $this->orders = $orders;
        $this->notifications = $notifications;
        $this->tickets = $tickets;
        $this->transactions = $transactions;
    }

    /**
     * @return array{order: array<string, mixed>, code: array<string, mixed>}
     */
    public function retrieveCode(int $userId, int $orderId): array
    {
        $order = $this->orders->find($orderId);
        if (!$order || (int)$order['user_id'] !== $userId) {
            throw new RuntimeException('Order not found.');
        }

        if (($order['hash_code'] ?? '') === '') {
            throw new RuntimeException('Hash code missing for this order.');
        }

        $metadata = $order['metadata'] ? json_decode((string)$order['metadata'], true) : [];
        if (isset($metadata['code']) && is_array($metadata['code'])) {
            return [
                'order' => $order,
                'code' => $metadata['code'],
            ];
        }

        try {
            $codeData = $this->provider->requestCode((string)$order['hash_code']);
        } catch (Throwable $e) {
            $ticketMessage = sprintf(
                'Failed to retrieve code for order #%d (hash %s).',
                $orderId,
                $order['hash_code']
            );
            $this->tickets->open($userId, 'Code Retrieval Issue', $ticketMessage, 'pending');
            throw new RuntimeException('Code not ready yet, support notified.');
        }

        $metadata['code'] = $codeData;

        $this->orders->updateStatus($orderId, 'delivered', $metadata);
        $this->transactions->log(
            $userId,
            'credit',
            'purchase',
            0.0,
            $order['currency'] ?? 'USD',
            (string)$orderId,
            ['action' => 'code_delivered']
        );
        $this->notifications->notifyCodeDelivered($order, $codeData);

        $updatedOrder = $this->orders->find($orderId) ?? $order;

        return [
            'order' => $updatedOrder,
            'code' => $codeData,
        ];
    }
}
