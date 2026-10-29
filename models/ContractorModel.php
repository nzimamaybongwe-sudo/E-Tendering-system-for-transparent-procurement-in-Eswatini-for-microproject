<?php
class ContractorModel extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO contractors (user_id, company_name, category, cic_grade, verification_status, tax_pin, form_j_number, local_ownership, contact_person, phone, address, created_at) VALUES (:user_id, :company_name, :category, :cic_grade, :verification_status, :tax_pin, :form_j_number, :local_ownership, :contact_person, :phone, :address, NOW())');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'company_name' => $this->sanitize($data['company_name']),
            'category' => substr($this->sanitize($data['category'] ?? ''), 0, 255),
            'cic_grade' => $this->sanitize($data['cic_grade']),
            'verification_status' => $this->sanitize($data['verification_status']),
            'tax_pin' => $this->sanitize($data['tax_pin']),
            'form_j_number' => $this->sanitize($data['form_j_number']),
            'local_ownership' => $data['local_ownership'],
            'contact_person' => $this->sanitize($data['contact_person'] ?? ''),
            'phone' => $this->sanitize($data['phone'] ?? ''),
            'address' => $this->sanitize($data['address'] ?? ''),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getByUserId(int $userId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM contractors WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }

    public function getById(int $contractorId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM contractors WHERE contractor_id = :contractor_id LIMIT 1');
        $stmt->execute(['contractor_id' => $contractorId]);
        return $stmt->fetch();
    }

    public function getPending(): array
    {
        $stmt = $this->pdo->query('SELECT c.*, u.email, u.username FROM contractors c JOIN users u ON c.user_id = u.user_id WHERE c.verification_status = "pending" ORDER BY c.created_at DESC');
        return $stmt->fetchAll();
    }

    public function updateVerificationStatus(int $contractorId, string $status, ?string $reason = null): bool
    {
        $stmt = $this->pdo->prepare('UPDATE contractors SET verification_status = :status, rejection_reason = :reason, verified_at = NOW() WHERE contractor_id = :contractor_id');
        return $stmt->execute(['status' => $status, 'reason' => $reason, 'contractor_id' => $contractorId]);
    }

    public function getRejected(): array
    {
        $stmt = $this->pdo->query('SELECT c.*, u.email, u.username FROM contractors c JOIN users u ON c.user_id = u.user_id WHERE c.verification_status = "rejected" ORDER BY c.verified_at DESC');
        return $stmt->fetchAll();
    }

    public function getRecentReviewed(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare('SELECT c.*, u.email, u.username FROM contractors c JOIN users u ON c.user_id = u.user_id WHERE c.verification_status IN ("approved", "rejected") ORDER BY c.verified_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT c.*, u.email, u.username FROM contractors c JOIN users u ON c.user_id = u.user_id ORDER BY c.created_at DESC');
        return $stmt->fetchAll();
    }

    public function getApproved(): array
    {
        $stmt = $this->pdo->query('SELECT c.*, u.email, u.username FROM contractors c JOIN users u ON c.user_id = u.user_id WHERE c.verification_status = "approved" AND c.blacklisted = 0 ORDER BY c.company_name ASC');
        return $stmt->fetchAll();
    }

    public function getBlacklisted(): array
    {
        $stmt = $this->pdo->query('SELECT c.*, u.email, u.username FROM contractors c JOIN users u ON c.user_id = u.user_id WHERE c.blacklisted = 1 ORDER BY c.blacklisted_at DESC');
        return $stmt->fetchAll();
    }

    public function blacklist(int $contractorId, string $reason): bool
    {
        $stmt = $this->pdo->prepare('UPDATE contractors SET blacklisted = 1, blacklist_reason = :reason, blacklisted_at = NOW() WHERE contractor_id = :contractor_id');
        return $stmt->execute(['reason' => $this->sanitize($reason), 'contractor_id' => $contractorId]);
    }

    public function isBlacklisted(int $contractorId): bool
    {
        $stmt = $this->pdo->prepare('SELECT blacklisted FROM contractors WHERE contractor_id = :contractor_id LIMIT 1');
        $stmt->execute(['contractor_id' => $contractorId]);
        $row = $stmt->fetch();
        return !empty($row['blacklisted']);
    }

    public function getProfile(int $contractorId): array
    {
        $stmt = $this->pdo->prepare('SELECT c.*, u.email, u.username FROM contractors c JOIN users u ON c.user_id = u.user_id WHERE c.contractor_id = :contractor_id LIMIT 1');
        $stmt->execute(['contractor_id' => $contractorId]);
        return $stmt->fetch();
    }

    public function getBlacklistedCount(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM contractors WHERE blacklisted = 1');
        return (int)$stmt->fetchColumn();
    }

    public function updateProfile(int $contractorId, array $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE contractors SET company_name = :company_name, cic_grade = :cic_grade, local_ownership = :local_ownership, profile_summary = :profile_summary WHERE contractor_id = :contractor_id');
        return $stmt->execute([
            'company_name' => $this->sanitize($data['company_name']),
            'cic_grade' => $this->sanitize($data['cic_grade']),
            'local_ownership' => $data['local_ownership'] ?? 0,
            'profile_summary' => $this->sanitize($data['profile_summary'] ?? ''),
            'contractor_id' => $contractorId,
        ]);
    }

    public function checkAndUpdateAccountStatus(int $contractorId): void
    {
        $documentModel = new ContractorDocumentModel($this->pdo);
        $phase1Compliance = $documentModel->checkPhaseCompliance($contractorId, 'phase1');
        $phase1Valid = !in_array(false, array_column($phase1Compliance, 'valid'));

        $contractor = $this->getById($contractorId);
        if (!$contractor) {
            return;
        }

        // If Phase 1 documents are invalid and account is active, suspend it
        if (!$phase1Valid && $contractor['verification_status'] === 'approved' && !$contractor['blacklisted']) {
            $this->pdo->prepare('UPDATE contractors SET verification_status = "suspended", rejection_reason = "Phase 1 documents expired - account suspended" WHERE contractor_id = :contractor_id')
                ->execute(['contractor_id' => $contractorId]);

            // Also suspend the user account
            $this->pdo->prepare('UPDATE users SET status = "suspended" WHERE user_id = :user_id')
                ->execute(['user_id' => $contractor['user_id']]);
        }
        // If Phase 1 documents become valid again and account was suspended for this reason, reactivate
        elseif ($phase1Valid && $contractor['verification_status'] === 'suspended' && $contractor['rejection_reason'] === 'Phase 1 documents expired - account suspended') {
            $this->pdo->prepare('UPDATE contractors SET verification_status = "approved", rejection_reason = NULL WHERE contractor_id = :contractor_id')
                ->execute(['contractor_id' => $contractorId]);

            $this->pdo->prepare('UPDATE users SET status = "active" WHERE user_id = :user_id')
                ->execute(['user_id' => $contractor['user_id']]);
        }
    }
}
