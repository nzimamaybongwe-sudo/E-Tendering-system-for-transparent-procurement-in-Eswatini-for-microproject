<?php
class BidDocumentModel extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO bid_documents (bid_id, document_name, document_path, uploaded_at) VALUES (:bid_id, :document_name, :document_path, NOW())');
        $stmt->execute([
            'bid_id' => $data['bid_id'],
            'document_name' => $this->sanitize($data['document_name']),
            'document_path' => $this->sanitize($data['document_path']),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getByBid(int $bidId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM bid_documents WHERE bid_id = :bid_id ORDER BY uploaded_at DESC');
        $stmt->execute(['bid_id' => $bidId]);
        return $stmt->fetchAll();
    }

    public function getById(int $documentId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM bid_documents WHERE bid_document_id = :document_id LIMIT 1');
        $stmt->execute(['document_id' => $documentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getLatestByName(int $bidId, string $documentName): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM bid_documents WHERE bid_id = :bid_id AND document_name = :document_name ORDER BY uploaded_at DESC LIMIT 1');
        $stmt->execute([
            'bid_id' => $bidId,
            'document_name' => $this->sanitize($documentName),
        ]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function hasRequiredDocuments(int $bidId): bool
    {
        $requiredDocs = ['Tender Security'];
        foreach ($requiredDocs as $docName) {
            if (!$this->getLatestByName($bidId, $docName)) {
                return false;
            }
        }
        return true;
    }

    public function flagDocument(int $docId, int $isFraudulent, int $isIncorrect, ?string $reason): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE bid_documents 
            SET is_fraudulent = :is_fraudulent, is_incorrect = :is_incorrect, flag_reason = :reason 
            WHERE bid_document_id = :doc_id
        ');
        return $stmt->execute([
            'is_fraudulent' => $isFraudulent,
            'is_incorrect'  => $isIncorrect,
            'reason'        => $reason,
            'doc_id'        => $docId,
        ]);
    }
}