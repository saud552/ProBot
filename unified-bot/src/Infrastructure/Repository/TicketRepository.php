<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

class TicketRepository extends Repository
{
    public function create(int $userId, string $subject, string $message, string $status = 'open'): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO tickets (user_id, status, subject, last_message_at)
             VALUES (:user_id, :status, :subject, NOW())'
        );
        $stmt->execute([
            'user_id' => $userId,
            'status' => $status,
            'subject' => $subject,
        ]);

        $ticketId = (int)$this->pdo->lastInsertId();
        $this->appendMessage($ticketId, 'user', $message);

        return $ticketId;
    }

    public function appendMessage(int $ticketId, string $senderType, string $message): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO ticket_messages (ticket_id, sender_type, message)
             VALUES (:ticket_id, :sender_type, :message)'
        );
        $stmt->execute([
            'ticket_id' => $ticketId,
            'sender_type' => $senderType,
            'message' => $message,
        ]);

        $this->pdo
            ->prepare('UPDATE tickets SET last_message_at = NOW() WHERE id = :id')
            ->execute(['id' => $ticketId]);
    }
}
