<?php
class TenderAmendmentModel extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO tender_amendments (tender_id, changed_by, previous_title, previous_description, previous_budget, previous_required_grade, previous_deadline, previous_category, version_number, notes, created_at) VALUES (:tender_id, :changed_by, :previous_title, :previous_description, :previous_budget, :previous_required_grade, :previous_deadline, :previous_category, :version_number, :notes, NOW())');
        $stmt->execute([
            'tender_id' => $data['tender_id'],
            'changed_by' => $data['changed_by'],
            'previous_title' => $this->sanitize($data['previous_title']),
            'previous_description' => $this->sanitize($data['previous_description']),
            'previous_budget' => $data['previous_budget'],
            'previous_required_grade' => $this->sanitize($data['previous_required_grade']),
            'previous_deadline' => $data['previous_deadline'],
            'previous_category' => $this->sanitize($data['previous_category']),
            'version_number' => $data['version_number'],
            'notes' => $this->sanitize($data['notes']),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getByTender(int $tenderId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tender_amendments WHERE tender_id = :tender_id ORDER BY created_at DESC');
        $stmt->execute(['tender_id' => $tenderId]);
        return $stmt->fetchAll();
    }
}
