<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use PDO;

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

    public function find(int $ticketId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tickets WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $ticketId]);
        $ticket = $stmt->fetch();

        return $ticket ?: null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listByUser(int $userId, int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM tickets WHERE user_id = :user ORDER BY last_message_at DESC LIMIT :limit'
        );
        $stmt->bindValue('user', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAll(?string $status, int $limit = 10): array
    {
        if ($status) {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM tickets WHERE status = :status ORDER BY last_message_at DESC LIMIT :limit'
            );
            $stmt->bindValue('status', $status);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM tickets ORDER BY last_message_at DESC LIMIT :limit'
            );
        }

        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function messages(int $ticketId, int $limit = 15): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM ticket_messages
             WHERE ticket_id = :ticket
             ORDER BY id DESC
             LIMIT :limit'
        );
        $stmt->bindValue('ticket', $ticketId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_reverse($messages);
    }

    public function updateStatus(int $ticketId, string $status): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE tickets SET status = :status, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([
            'status' => $status,
            'id' => $ticketId,
        ]);
    }
}
