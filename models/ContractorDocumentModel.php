<?php
class ContractorDocumentModel extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO contractor_documents (contractor_id, document_name, document_path, expiry_date, document_phase, uploaded_at) VALUES (:contractor_id, :document_name, :document_path, :expiry_date, :document_phase, NOW())');
        $stmt->execute([
            'contractor_id' => $data['contractor_id'],
            'document_name' => $this->sanitize($data['document_name']),
            'document_path' => $this->sanitize($data['document_path']),
            'expiry_date' => $data['expiry_date'] ?? null,
            'document_phase' => $data['document_phase'] ?? 'phase1',
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getById(int $documentId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM contractor_documents WHERE document_id = :document_id');
        $stmt->execute(['document_id' => $documentId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getByContractor(int $contractorId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT cd1.* 
            FROM contractor_documents cd1
            INNER JOIN (
                SELECT MAX(document_id) as max_id
                FROM contractor_documents
                WHERE contractor_id = :contractor_id
                GROUP BY document_name
            ) cd2 ON cd1.document_id = cd2.max_id
            ORDER BY cd1.uploaded_at DESC
        ');
        $stmt->execute(['contractor_id' => $contractorId]);
        return $stmt->fetchAll();
    }

    public function getLatestByName(int $contractorId, string $documentName): ?array
    {
        // Special composite check for Form J & Form C
        if ($documentName === 'Form J & Form C') {
            $doc = $this->getLatestByNameSingle($contractorId, 'Form J & Form C');
            if ($doc) return $doc;
            
            // Return whichever of Part 1 or Part 2 was uploaded latest, if both exist
            $p1 = $this->getLatestByNameSingle($contractorId, 'Form J & Form C (Part 1)');
            $p2 = $this->getLatestByNameSingle($contractorId, 'Form J & Form C (Part 2)');
            if ($p1 && $p2) {
                return strtotime($p1['uploaded_at']) > strtotime($p2['uploaded_at']) ? $p1 : $p2;
            }
            return $p1 ?: $p2;
        }

        $synonyms = [
            'Trading Licence' => ['Trading Licence', 'Trading License'],
            'Tax Clearance Certificate' => ['Tax Clearance Certificate', 'Tax Compliance Certificate'],
            'SNPF/ENPF Compliance' => ['SNPF/ENPF Compliance', 'ENPF Compliance Certificate'],
        ];

        $namesToCheck = $synonyms[$documentName] ?? [$documentName];
        $latestDoc = null;
        foreach ($namesToCheck as $name) {
            $doc = $this->getLatestByNameSingle($contractorId, $name);
            if ($doc) {
                if (!$latestDoc || strtotime($doc['uploaded_at']) > strtotime($latestDoc['uploaded_at'])) {
                    $latestDoc = $doc;
                }
            }
        }
        return $latestDoc;
    }

    private function getLatestByNameSingle(int $contractorId, string $documentName): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM contractor_documents WHERE contractor_id = :contractor_id AND document_name = :document_name ORDER BY uploaded_at DESC LIMIT 1');
        $stmt->execute([
            'contractor_id' => $contractorId,
            'document_name' => $this->sanitize($documentName),
        ]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getByPhase(int $contractorId, string $phase): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM contractor_documents WHERE contractor_id = :contractor_id AND document_phase = :phase ORDER BY uploaded_at DESC');
        $stmt->execute([
            'contractor_id' => $contractorId,
            'phase' => $phase,
        ]);
        return $stmt->fetchAll();
    }

    public function isDocumentValid(int $contractorId, string $documentName, ?int $maxAgeDays = null): bool
    {
        // Special case for Form J & Form C (could be uploaded as Part 1 and Part 2)
        if ($documentName === 'Form J & Form C') {
            if ($this->checkSingleDocValid($contractorId, 'Form J & Form C', $maxAgeDays)) {
                return true;
            }
            if ($this->checkSingleDocValid($contractorId, 'Form J & Form C (Part 1)', $maxAgeDays) &&
                $this->checkSingleDocValid($contractorId, 'Form J & Form C (Part 2)', $maxAgeDays)) {
                return true;
            }
            return false;
        }

        $synonyms = [
            'Trading Licence' => ['Trading Licence', 'Trading License'],
            'Tax Clearance Certificate' => ['Tax Clearance Certificate', 'Tax Compliance Certificate'],
            'SNPF/ENPF Compliance' => ['SNPF/ENPF Compliance', 'ENPF Compliance Certificate'],
        ];

        $namesToCheck = $synonyms[$documentName] ?? [$documentName];
        foreach ($namesToCheck as $name) {
            if ($this->checkSingleDocValid($contractorId, $name, $maxAgeDays)) {
                return true;
            }
        }

        return false;
    }

    private function checkSingleDocValid(int $contractorId, string $name, ?int $maxAgeDays = null): bool
    {
        $doc = $this->getLatestByNameSingle($contractorId, $name);
        if (!$doc) {
            return false;
        }

        if ($doc['is_expired']) {
            return false;
        }

        if ($doc['expiry_date'] && strtotime($doc['expiry_date']) < time()) {
            $this->markExpired($doc['document_id']);
            return false;
        }

        if ($maxAgeDays && strtotime($doc['uploaded_at']) < strtotime("-{$maxAgeDays} days")) {
            return false;
        }

        return true;
    }

    public function checkPhaseCompliance(int $contractorId, string $phase): array
    {
        $requiredDocs = $this->getRequiredDocumentsForPhase($phase);
        $results = [];

        foreach ($requiredDocs as $docName => $config) {
            $isValid = $this->isDocumentValid($contractorId, $docName, $config['max_age_days'] ?? null);
            $doc = $this->getLatestByName($contractorId, $docName);

            $results[$docName] = [
                'required' => true,
                'valid' => $isValid,
                'uploaded_at' => $doc['uploaded_at'] ?? null,
                'expiry_date' => $doc['expiry_date'] ?? null,
                'max_age_days' => $config['max_age_days'] ?? null,
            ];
        }

        return $results;
    }

    public function getRequiredDocumentsForPhase(string $phase): array
    {
        $phaseRequirements = [
            'phase1' => [
                'Certificate of Incorporation' => ['max_age_days' => null],
                'Form J & Form C' => ['max_age_days' => null],
                'CIC Registration Certificate' => ['max_age_days' => null],
                'VAT Registration Certificate' => ['max_age_days' => null],
                'Trading Licence' => ['max_age_days' => null],
            ],
            'phase2' => [
                'Tax Clearance Certificate' => ['max_age_days' => 30], // Monthly
                'SNPF/ENPF Compliance' => ['max_age_days' => 90], // 3 months
                'Police Clearance' => ['max_age_days' => 90], // 3 months per director
                'Labour Compliance Certificate' => ['max_age_days' => null], // No specific expiry mentioned
            ],
            'phase3' => [
                'Tender Security' => ['max_age_days' => null], // Per tender
                'Site Visit Declaration' => ['max_age_days' => null], // Per tender
            ],
        ];

        return $phaseRequirements[$phase] ?? [];
    }

    public function markExpired(int $documentId): void
    {
        $stmt = $this->pdo->prepare('UPDATE contractor_documents SET is_expired = 1 WHERE document_id = :document_id');
        $stmt->execute(['document_id' => $documentId]);
    }

    public function updateExpiryDate(int $documentId, string $expiryDate): void
    {
        $stmt = $this->pdo->prepare('UPDATE contractor_documents SET expiry_date = :expiry_date, is_expired = 0 WHERE document_id = :document_id');
        $stmt->execute([
            'document_id' => $documentId,
            'expiry_date' => $expiryDate,
        ]);
    }

    public function flagDocument(int $docId, int $isFraudulent, int $isIncorrect, ?string $reason): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE contractor_documents 
            SET is_fraudulent = :is_fraudulent, is_incorrect = :is_incorrect, flag_reason = :reason 
            WHERE document_id = :doc_id
        ');
        return $stmt->execute([
            'is_fraudulent' => $isFraudulent,
            'is_incorrect'  => $isIncorrect,
            'reason'        => $reason,
            'doc_id'        => $docId,
        ]);
    }
}
