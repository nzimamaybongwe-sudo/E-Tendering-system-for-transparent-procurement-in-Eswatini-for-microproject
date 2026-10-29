<?php
class EvaluatorProfileModel extends BaseModel
{
    public function create(array $data): int
    {
        $code = $this->generateUniqueCode();
        $stmt = $this->pdo->prepare('INSERT INTO evaluator_profiles (user_id, evaluator_code, full_name, accreditation_number, specialization, institution, experience_summary, accreditation_document, created_at) VALUES (:user_id, :evaluator_code, :full_name, :accreditation_number, :specialization, :institution, :experience_summary, :accreditation_document, NOW())');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'evaluator_code' => $code,
            'full_name' => $this->sanitize($data['full_name']),
            'accreditation_number' => $this->sanitize($data['accreditation_number']),
            'specialization' => $this->sanitize($data['specialization']),
            'institution' => $this->sanitize($data['institution']),
            'experience_summary' => $this->sanitize($data['experience_summary'] ?? ''),
            'accreditation_document' => $this->sanitize($data['accreditation_document'] ?? null),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function generateUniqueCode(): string
    {
        do {
            $code = 'EVAL-' . strtoupper(bin2hex(random_bytes(3))); 
            $exists = $this->codeExists($code);
        } while ($exists);
        return $code;
    }

    public function codeExists(string $code): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS cnt FROM evaluator_profiles WHERE evaluator_code = :code');
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch();
        return (int)$row['cnt'] > 0;
    }

    public function getPending(): array
    {
        $stmt = $this->pdo->query('SELECT ep.*, u.email, u.username, u.created_at AS applied_at FROM evaluator_profiles ep JOIN users u ON ep.user_id = u.user_id WHERE ep.approval_status = "pending" ORDER BY ep.created_at DESC');
        return $stmt->fetchAll();
    }

    public function getApproved(): array
    {
        $stmt = $this->pdo->query('SELECT ep.evaluator_profile_id, ep.user_id, ep.evaluator_code, ep.accreditation_number, ep.specialization, ep.institution, ep.created_at FROM evaluator_profiles ep WHERE ep.approval_status = "approved" ORDER BY ep.created_at DESC');
        return $stmt->fetchAll();
    }

    public function getApprovedCount(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS total FROM evaluator_profiles WHERE approval_status = "approved"');
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    public function updateApprovalStatus(int $profileId, string $status, int $reviewedBy, ?string $reason = null): bool
    {
        $stmt = $this->pdo->prepare('UPDATE evaluator_profiles SET approval_status = :status, rejection_reason = :reason, reviewed_by = :reviewed_by, reviewed_at = NOW() WHERE evaluator_profile_id = :profile_id');
        return $stmt->execute(['status' => $status, 'reason' => $reason, 'reviewed_by' => $reviewedBy, 'profile_id' => $profileId]);
    }

    public function getRejected(): array
    {
        $stmt = $this->pdo->query('SELECT ep.*, u.email, u.username FROM evaluator_profiles ep JOIN users u ON ep.user_id = u.user_id WHERE ep.approval_status = "rejected" ORDER BY ep.reviewed_at DESC');
        return $stmt->fetchAll();
    }

    public function getRecentReviewed(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare('SELECT ep.*, u.email, u.username FROM evaluator_profiles ep JOIN users u ON ep.user_id = u.user_id WHERE ep.approval_status IN ("approved", "rejected") ORDER BY ep.reviewed_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $profileId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM evaluator_profiles WHERE evaluator_profile_id = :profile_id LIMIT 1');
        $stmt->execute(['profile_id' => $profileId]);
        return $stmt->fetch();
    }

    public function getByUserId(int $userId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM evaluator_profiles WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }
}
