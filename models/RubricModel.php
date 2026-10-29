<?php
/**
 * RubricModel — manages scoring weights for each tender.
 * Fulfills the "Public Scoring Rubrics" transparency objective.
 */
class RubricModel extends BaseModel
{
    public function getByTender(int $tenderId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tender_rubrics WHERE tender_id = :id LIMIT 1');
        $stmt->execute(['id' => $tenderId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function upsert(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO tender_rubrics (tender_id, technical_weight, financial_weight, local_weight)
             VALUES (:tender_id, :technical, :financial, :local)
             ON DUPLICATE KEY UPDATE 
             technical_weight = VALUES(technical_weight),
             financial_weight = VALUES(financial_weight),
             local_weight     = VALUES(local_weight)'
        );
        return $stmt->execute([
            'tender_id' => $data['tender_id'],
            'technical' => $data['technical_weight'],
            'financial' => $data['financial_weight'],
            'local'     => $data['local_weight'],
        ]);
    }
}
