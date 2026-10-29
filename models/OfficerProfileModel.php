<?php
class OfficerProfileModel extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO officer_profiles (user_id, full_name, employee_id, department, title, approval_status, created_at) VALUES (:user_id, :full_name, :employee_id, :department, :title, :approval_status, NOW())');
        $stmt->execute([
            'user_id'         => $data['user_id'],
            'full_name'       => $data['full_name'],
            'employee_id'     => $data['employee_id'],
            'department'      => $data['department'],
            'title'           => $data['title'],
            'approval_status' => $data['approval_status'] ?? 'pending',
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getByUserId(int $userId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM officer_profiles WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }

    public function updateStatus(int $profileId, string $status, int $reviewedBy, ?string $reason = null): bool
    {
        $stmt = $this->pdo->prepare('UPDATE officer_profiles SET approval_status = :status, rejection_reason = :reason, reviewed_by = :reviewed_by, reviewed_at = NOW() WHERE officer_profile_id = :profile_id');
        return $stmt->execute([
            'status'      => $status,
            'reason'      => $reason,
            'reviewed_by' => $reviewedBy,
            'profile_id'  => $profileId,
        ]);
    }

    /** Alias for updateStatus to match AdminController expected signature */
    public function updateApprovalStatus(int $profileId, string $status, int $reviewedBy, ?string $reason = null): bool
    {
        return $this->updateStatus($profileId, $status, $reviewedBy, $reason);
    }

    public function getPendingProfiles(): array
    {
        $stmt = $this->pdo->query('
            SELECT op.*, u.email, u.username, u.created_at as registered_at
            FROM officer_profiles op
            JOIN users u ON op.user_id = u.user_id
            WHERE op.approval_status = "pending"
        ');
        return $stmt->fetchAll();
    }

    public function getById(int $profileId)
    {
        $stmt = $this->pdo->prepare('
            SELECT op.*, u.email, u.username
            FROM officer_profiles op
            JOIN users u ON op.user_id = u.user_id
            WHERE op.officer_profile_id = :profile_id
            LIMIT 1
        ');
        $stmt->execute(['profile_id' => $profileId]);
        return $stmt->fetch();
    }

    public function getApproved(): array
    {
        $stmt = $this->pdo->query('
            SELECT op.*, u.email, u.username, u.created_at as registered_at
            FROM officer_profiles op
            JOIN users u ON op.user_id = u.user_id
            WHERE op.approval_status = "approved"
        ');
        return $stmt->fetchAll();
    }

    public function getApprovedCount(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM officer_profiles WHERE approval_status = "approved"');
        return (int)$stmt->fetchColumn();
    }

    public function getRejected(): array
    {
        $stmt = $this->pdo->query('
            SELECT op.*, u.email, u.username, u.created_at as registered_at
            FROM officer_profiles op
            JOIN users u ON op.user_id = u.user_id
            WHERE op.approval_status = "rejected"
        ');
        return $stmt->fetchAll();
    }
}

