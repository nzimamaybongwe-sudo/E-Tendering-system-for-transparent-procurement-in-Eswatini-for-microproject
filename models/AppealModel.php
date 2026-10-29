<?php
class AppealModel extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO appeals (tender_id, user_id, bid_id, subject, appeal_reason, status, created_at) VALUES (:tender_id, :user_id, :bid_id, :subject, :appeal_reason, :status, NOW())');
        $stmt->execute([
            'tender_id' => $data['tender_id'],
            'user_id' => $data['user_id'],
            'bid_id' => $data['bid_id'],
            'subject' => $this->sanitize($data['subject']),
            'appeal_reason' => $this->sanitize($data['appeal_reason']),
            'status' => $this->sanitize($data['status']),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getByTender(int $tenderId): array
    {
        $stmt = $this->pdo->prepare('SELECT a.*, u.username, u.email, c.company_name FROM appeals a JOIN users u ON a.user_id = u.user_id JOIN contractors c ON u.user_id = c.user_id WHERE a.tender_id = :tender_id ORDER BY a.created_at DESC');
        $stmt->execute(['tender_id' => $tenderId]);
        return $stmt->fetchAll();
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT a.*, t.title FROM appeals a JOIN tenders t ON a.tender_id = t.tender_id WHERE a.user_id = :user_id ORDER BY a.created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT a.*, t.title, u.username AS contractor_name, u.email AS contractor_email FROM appeals a JOIN tenders t ON a.tender_id = t.tender_id JOIN users u ON a.user_id = u.user_id ORDER BY a.created_at DESC');
        return $stmt->fetchAll();
    }

    public function updateStatus(int $appealId, string $status, string $adminResponse, int $respondedBy): bool
    {
        $stmt = $this->pdo->prepare('UPDATE appeals SET status = :status, admin_response = :admin_response, responded_by = :responded_by, responded_at = NOW() WHERE appeal_id = :appeal_id');
        return $stmt->execute([
            'status' => $this->sanitize($status),
            'admin_response' => $this->sanitize($admin_response),
            'responded_by' => $respondedBy,
            'appeal_id' => $appealId,
        ]);
    }

    public function getById(int $appealId)
    {
        $stmt = $this->pdo->prepare('SELECT a.*, u.username, u.email, t.title FROM appeals a JOIN users u ON a.user_id = u.user_id JOIN tenders t ON a.tender_id = t.tender_id WHERE a.appeal_id = :appeal_id LIMIT 1');
        $stmt->execute(['appeal_id' => $appealId]);
        return $stmt->fetch();
    }

    public function getPending(): array
    {
        $stmt = $this->pdo->query('SELECT a.*, t.title FROM appeals a JOIN tenders t ON a.tender_id = t.tender_id WHERE a.status = "submitted" ORDER BY a.created_at ASC');
        return $stmt->fetchAll();
    }
}
