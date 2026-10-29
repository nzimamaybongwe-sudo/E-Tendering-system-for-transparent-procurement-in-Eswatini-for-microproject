<?php
class RubricProposalModel extends BaseModel
{
    public function createProposal(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO tender_rubric_proposals (tender_id, evaluator_id, technical_weight, financial_weight, local_weight, notes, status, created_at)
             VALUES (:tender_id, :evaluator_id, :technical_weight, :financial_weight, :local_weight, :notes, :status, NOW())'
        );

        $stmt->execute([
            'tender_id' => $data['tender_id'],
            'evaluator_id' => $data['evaluator_id'],
            'technical_weight' => $data['technical_weight'],
            'financial_weight' => $data['financial_weight'],
            'local_weight' => $data['local_weight'],
            'notes' => $this->sanitize($data['notes'] ?? ''),
            'status' => $data['status'] ?? 'submitted',
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function getByEvaluator(int $evaluatorId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, t.title AS tender_title, u.username AS evaluator_username
             FROM tender_rubric_proposals p
             JOIN tenders t ON p.tender_id = t.tender_id
             JOIN users u ON p.evaluator_id = u.user_id
             WHERE p.evaluator_id = :evaluator_id
             ORDER BY p.created_at DESC'
        );
        $stmt->execute(['evaluator_id' => $evaluatorId]);
        return $stmt->fetchAll();
    }

    public function getById(int $proposalId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, t.title AS tender_title, u.username AS evaluator_username
             FROM tender_rubric_proposals p
             JOIN tenders t ON p.tender_id = t.tender_id
             JOIN users u ON p.evaluator_id = u.user_id
             WHERE p.proposal_id = :proposal_id LIMIT 1'
        );
        $stmt->execute(['proposal_id' => $proposalId]);
        return $stmt->fetch();
    }

    public function getPending(): array
    {
        $stmt = $this->pdo->query(
            'SELECT p.*, t.title AS tender_title, u.username AS evaluator_username
             FROM tender_rubric_proposals p
             JOIN tenders t ON p.tender_id = t.tender_id
             JOIN users u ON p.evaluator_id = u.user_id
             WHERE p.status = "submitted"
             ORDER BY p.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function updateStatus(int $proposalId, string $status, ?string $notes = null): bool
    {
        $sql = 'UPDATE tender_rubric_proposals SET status = :status';
        $params = ['status' => $status, 'proposal_id' => $proposalId];

        if ($notes !== null) {
            $sql .= ', notes = :notes';
            $params['notes'] = $this->sanitize($notes);
        }

        $sql .= ', updated_at = NOW() WHERE proposal_id = :proposal_id';

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function getByTender(int $tenderId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, u.username AS evaluator_username
             FROM tender_rubric_proposals p
             JOIN users u ON p.evaluator_id = u.user_id
             WHERE p.tender_id = :tender_id
             ORDER BY p.created_at DESC'
        );
        $stmt->execute(['tender_id' => $tenderId]);
        return $stmt->fetchAll();
    }
}
