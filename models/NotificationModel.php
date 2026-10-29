<?php
class NotificationModel extends BaseModel
{
    public function create(int $userId, string $message, string $channel = 'in-app'): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO notifications (user_id, message, channel, created_at) VALUES (:user_id, :message, :channel, NOW())');
        return $stmt->execute([
            'user_id' => $userId,
            'message' => $this->sanitize($message),
            'channel' => $channel,
        ]);
    }

    public function getUnreadForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM notifications WHERE user_id = :user_id AND read_at IS NULL ORDER BY created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getAllForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function markAsRead(int $notificationId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE notifications SET read_at = NOW() WHERE notification_id = :notification_id');
        return $stmt->execute(['notification_id' => $notificationId]);
    }

    /**
     * Send bulk notifications to multiple users at once
     * Used when opening a tender for evaluation to notify all evaluators and contractors
     */
    public function createBulk(array $userIds, string $message, string $channel = 'in-app'): int
    {
        $sanitizedMessage = $this->sanitize($message);
        $count = 0;

        foreach ($userIds as $userId) {
            if ($this->create((int)$userId, $message, $channel)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Send notification to all evaluators for a tender
     */
    public function notifyEvaluatorsForTender(int $tenderId, string $message, string $channel = 'in-app'): int
    {
        $stmt = $this->pdo->prepare('
            SELECT DISTINCT e.evaluator_id as user_id
            FROM evaluations e
            JOIN bids b ON e.bid_id = b.bid_id
            WHERE b.tender_id = :tender_id
        ');
        $stmt->execute(['tender_id' => $tenderId]);
        $evaluators = $stmt->fetchAll();

        $evaluatorIds = array_column($evaluators, 'user_id');
        return $this->createBulk($evaluatorIds, $message, $channel);
    }

    /**
     * Send notification to all contractors who bid on a tender
     */
    public function notifyContractorsForTender(int $tenderId, string $message, string $channel = 'in-app'): int
    {
        $stmt = $this->pdo->prepare('
            SELECT DISTINCT c.user_id
            FROM bids b
            JOIN contractors c ON b.contractor_id = c.contractor_id
            WHERE b.tender_id = :tender_id
        ');
        $stmt->execute(['tender_id' => $tenderId]);
        $contractors = $stmt->fetchAll();

        $contractorIds = array_column($contractors, 'user_id');
        return $this->createBulk($contractorIds, $message, $channel);
    }
}
