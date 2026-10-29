<?php
class AwardRecommendationModel extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO award_recommendations (tender_id, bid_id, officer_user_id, justification, status, created_at)
            VALUES (:tender_id, :bid_id, :officer_user_id, :justification, "pending_publish", NOW())
            ON DUPLICATE KEY UPDATE
                bid_id = VALUES(bid_id),
                officer_user_id = VALUES(officer_user_id),
                justification = VALUES(justification),
                status = "pending_publish",
                created_at = NOW()
        ');
        $stmt->execute([
            'tender_id'       => $data['tender_id'],
            'bid_id'          => $data['bid_id'],
            'officer_user_id' => $data['officer_user_id'],
            'justification'   => $data['justification'],
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getByTender(int $tenderId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT ar.*, u.username AS officer_username,
                   b.contractor_id, c.company_name
            FROM award_recommendations ar
            JOIN users u ON ar.officer_user_id = u.user_id
            JOIN bids b ON ar.bid_id = b.bid_id
            JOIN contractors c ON b.contractor_id = c.contractor_id
            WHERE ar.tender_id = :tender_id
            LIMIT 1
        ');
        $stmt->execute(['tender_id' => $tenderId]);
        return $stmt->fetch() ?: null;
    }

    public function updateStatus(int $tenderId, string $status): bool
    {
        $stmt = $this->pdo->prepare('UPDATE award_recommendations SET status = :status WHERE tender_id = :tender_id');
        return $stmt->execute(['status' => $status, 'tender_id' => $tenderId]);
    }

    public function getPendingForAdmin(): array
    {
        $stmt = $this->pdo->query('
            SELECT ar.*, t.title AS tender_title, u.username AS officer_username,
                   c.company_name
            FROM award_recommendations ar
            JOIN tenders t ON ar.tender_id = t.tender_id
            JOIN users u ON ar.officer_user_id = u.user_id
            JOIN bids b ON ar.bid_id = b.bid_id
            JOIN contractors c ON b.contractor_id = c.contractor_id
            WHERE ar.status = "pending_publish"
            ORDER BY ar.created_at DESC
        ');
        return $stmt->fetchAll();
    }
}
