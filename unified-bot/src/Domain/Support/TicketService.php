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
}
