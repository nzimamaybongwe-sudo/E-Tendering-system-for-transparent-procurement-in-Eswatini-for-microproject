<?php
/**
 * TenderDocumentModel — documents attached to a tender by admin (specs, drawings, BOQ, etc.)
 */
class TenderDocumentModel extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO tender_documents (tender_id, document_name, document_path, uploaded_by, uploaded_at)
             VALUES (:tender_id, :document_name, :document_path, :uploaded_by, NOW())'
        );
        $stmt->execute([
            'tender_id'     => $data['tender_id'],
            'document_name' => $this->sanitize($data['document_name']),
            'document_path' => $data['document_path'],
            'uploaded_by'   => $data['uploaded_by'],
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getByTender(int $tenderId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM tender_documents WHERE tender_id = :tender_id ORDER BY uploaded_at ASC'
        );
        $stmt->execute(['tender_id' => $tenderId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tender_documents WHERE document_id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM tender_documents WHERE document_id = :id');
        $stmt->execute(['id' => $id]);
    }
}
