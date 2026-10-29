<?php
class AdminProfileModel extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO admin_profiles (user_id, full_name, government_id, authorization_reference, office_phone, created_at) VALUES (:user_id, :full_name, :government_id, :authorization_reference, :office_phone, NOW())');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'full_name' => $this->sanitize($data['full_name']),
            'government_id' => $this->sanitize($data['government_id']),
            'authorization_reference' => $this->sanitize($data['authorization_reference']),
            'office_phone' => $this->sanitize($data['office_phone'] ?? ''),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getPending(): array
    {
        $stmt = $this->pdo->query('SELECT ap.*, u.email, u.username, u.created_at AS applied_at FROM admin_profiles ap JOIN users u ON ap.user_id = u.user_id WHERE ap.approval_status = "pending" ORDER BY ap.created_at DESC');
        return $stmt->fetchAll();
    }

    public function getApproved(): array
    {
        $stmt = $this->pdo->query('SELECT ap.*, u.email, u.username, u.created_at AS applied_at FROM admin_profiles ap JOIN users u ON ap.user_id = u.user_id WHERE ap.approval_status = "approved" ORDER BY ap.created_at DESC');
        return $stmt->fetchAll();
    }

    public function updateApprovalStatus(int $profileId, string $status, int $reviewedBy): bool
    {
        $stmt = $this->pdo->prepare('UPDATE admin_profiles SET approval_status = :status, reviewed_by = :reviewed_by, reviewed_at = NOW() WHERE admin_profile_id = :profile_id');
        return $stmt->execute(['status' => $status, 'reviewed_by' => $reviewedBy, 'profile_id' => $profileId]);
    }

    public function getById(int $profileId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admin_profiles WHERE admin_profile_id = :profile_id LIMIT 1');
        $stmt->execute(['profile_id' => $profileId]);
        return $stmt->fetch();
    }

    public function getByUserId(int $userId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admin_profiles WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }
}
