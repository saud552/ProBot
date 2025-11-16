<?php

declare(strict_types=1);

namespace App\Domain\Support;

use App\Infrastructure\Repository\TicketRepository;

class TicketService
{
    private TicketRepository $tickets;

    public function __construct(TicketRepository $tickets)
    {
        $this->tickets = $tickets;
    }

    public function open(
        int $userId,
        string $subject,
        string $message,
        string $status = 'open'
    ): int {
        return $this->tickets->create($userId, $subject, $message, $status);
    }

    public function addMessage(int $ticketId, string $senderType, string $message): void
    {
        $this->tickets->appendMessage($ticketId, $senderType, $message);
    }

    public function find(int $ticketId): ?array
    {
        return $this->tickets->find($ticketId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function userTickets(int $userId, int $limit = 5): array
    {
        return $this->tickets->listByUser($userId, $limit);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function adminTickets(?string $status, int $limit = 10): array
    {
        return $this->tickets->listAll($status, $limit);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function messages(int $ticketId, int $limit = 15): array
    {
        return $this->tickets->messages($ticketId, $limit);
    }

    public function updateStatus(int $ticketId, string $status): void
    {
        $this->tickets->updateStatus($ticketId, $status);
    }
}
