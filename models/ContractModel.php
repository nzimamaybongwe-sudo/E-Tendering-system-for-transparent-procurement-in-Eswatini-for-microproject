<?php
class ContractModel extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO contracts (tender_id, contractor_id, contract_amount, start_date, end_date, status, created_at)
            VALUES (:tender_id, :contractor_id, :contract_amount, :start_date, :end_date, "draft", NOW())
        ');
        $stmt->execute([
            'tender_id'       => $data['tender_id'],
            'contractor_id'   => $data['contractor_id'],
            'contract_amount' => $data['contract_amount'],
            'start_date'      => $data['start_date'] ?? null,
            'end_date'        => $data['end_date'] ?? null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getByTender(int $tenderId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT ct.*, t.title AS tender_title, c.company_name, u.email AS contractor_email
            FROM contracts ct
            JOIN tenders t ON ct.tender_id = t.tender_id
            JOIN contractors c ON ct.contractor_id = c.contractor_id
            JOIN users u ON c.user_id = u.user_id
            WHERE ct.tender_id = :tender_id
            LIMIT 1
        ');
        $stmt->execute(['tender_id' => $tenderId]);
        return $stmt->fetch() ?: null;
    }

    public function getById(int $contractId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT ct.*, t.title AS tender_title, c.company_name, u.email AS contractor_email, u.user_id AS contractor_user_id
            FROM contracts ct
            JOIN tenders t ON ct.tender_id = t.tender_id
            JOIN contractors c ON ct.contractor_id = c.contractor_id
            JOIN users u ON c.user_id = u.user_id
            WHERE ct.contract_id = :contract_id
            LIMIT 1
        ');
        $stmt->execute(['contract_id' => $contractId]);
        return $stmt->fetch() ?: null;
    }

    public function getByContractor(int $contractorId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT ct.*, t.title AS tender_title
            FROM contracts ct
            JOIN tenders t ON ct.tender_id = t.tender_id
            WHERE ct.contractor_id = :contractor_id
            ORDER BY ct.created_at DESC
        ');
        $stmt->execute(['contractor_id' => $contractorId]);
        return $stmt->fetchAll();
    }

    public function signByContractor(int $contractId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE contracts SET signed_by_contractor = 1, updated_at = NOW() WHERE contract_id = :contract_id');
        return $stmt->execute(['contract_id' => $contractId]);
    }

    public function signByAdmin(int $contractId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE contracts SET signed_by_admin = 1, status = "signed", updated_at = NOW() WHERE contract_id = :contract_id');
        return $stmt->execute(['contract_id' => $contractId]);
    }

    public function activate(int $contractId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE contracts SET status = "active", updated_at = NOW() WHERE contract_id = :contract_id');
        return $stmt->execute(['contract_id' => $contractId]);
    }

    public function complete(int $contractId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE contracts SET status = "completed", updated_at = NOW() WHERE contract_id = :contract_id');
        return $stmt->execute(['contract_id' => $contractId]);
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('
            SELECT ct.*, t.title AS tender_title, c.company_name
            FROM contracts ct
            JOIN tenders t ON ct.tender_id = t.tender_id
            JOIN contractors c ON ct.contractor_id = c.contractor_id
            ORDER BY ct.created_at DESC
        ');
        return $stmt->fetchAll();
    }
}
