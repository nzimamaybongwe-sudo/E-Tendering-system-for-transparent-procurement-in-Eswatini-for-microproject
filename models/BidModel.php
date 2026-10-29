<?php
class BidModel extends BaseModel
{
    private string $key;

    public function __construct(PDO $pdo, string $key)
    {
        parent::__construct($pdo);
        $this->key = $key;
    }

    public function create(array $data): int
    {
        $encrypted = $this->encryptAmount($data['amount']);
        
        // Generate a digital signature (HMAC) of the bid data for non-repudiation
        // We now include the digital form fields in the signature
        $signatureData = $data['tender_id'] . '|' . 
                        $data['contractor_id'] . '|' . 
                        $encrypted . '|' . 
                        $data['submitted_at'] . '|' .
                        ($data['eligibility_confirmed'] ?? 0) . '|' .
                        ($data['conflict_declared'] ?? 0) . '|' .
                        md5($data['method_statement'] ?? '') . '|' .
                        ($data['form_of_tender_accepted'] ?? 0);
        
        $signature = hash_hmac('sha256', $signatureData, $this->key);

        $stmt = $this->pdo->prepare('
            INSERT INTO bids (
                tender_id, contractor_id, encrypted_amount, submitted_at, digital_signature, 
                eligibility_confirmed, conflict_declared, conflict_details, 
                method_statement, form_of_tender_accepted, site_visit_date, site_visit_officer, locked
            ) VALUES (
                :tender_id, :contractor_id, :encrypted_amount, :submitted_at, :signature, 
                :eligibility, :conflict, :conflict_details, 
                :method_statement, :form_accepted, :site_date, :site_officer, 0
            )
        ');

        $stmt->execute([
            'tender_id' => $data['tender_id'],
            'contractor_id' => $data['contractor_id'],
            'encrypted_amount' => $encrypted,
            'submitted_at' => $data['submitted_at'],
            'signature' => $signature,
            'eligibility' => $data['eligibility_confirmed'] ?? 0,
            'conflict' => $data['conflict_declared'] ?? 0,
            'conflict_details' => $data['conflict_details'] ?? null,
            'method_statement' => $data['method_statement'] ?? null,
            'form_accepted' => $data['form_of_tender_accepted'] ?? 0,
            'site_date' => $data['site_visit_date'] ?? null,
            'site_officer' => $data['site_visit_officer'] ?? null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getByContractor(int $contractorId): array
    {
        $stmt = $this->pdo->prepare('SELECT b.*, t.title, t.status, t.meeting_link, t.meeting_password, t.meeting_start_time, t.consensus_meeting_link FROM bids b JOIN tenders t ON b.tender_id = t.tender_id WHERE b.contractor_id = :contractor_id ORDER BY b.submitted_at DESC');
        $stmt->execute(['contractor_id' => $contractorId]);
        return $stmt->fetchAll();
    }

    public function getByTender(int $tenderId): array
    {
        $stmt = $this->pdo->prepare('SELECT b.*, c.company_name FROM bids b JOIN contractors c ON b.contractor_id = c.contractor_id WHERE b.tender_id = :tender_id ORDER BY b.submitted_at ASC');
        $stmt->execute(['tender_id' => $tenderId]);
        return $stmt->fetchAll();
    }

    public function getRecentWithTender(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT b.*, t.title AS tender_title, t.status AS tender_status, c.company_name
             FROM bids b
             JOIN tenders t ON b.tender_id = t.tender_id
             JOIN contractors c ON b.contractor_id = c.contractor_id
             ORDER BY b.submitted_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        // Normalize keys expected by views: `title` and `decrypted_amount`
        foreach ($rows as &$r) {
            if (isset($r['tender_title'])) {
                $r['title'] = $r['tender_title'];
            }
            if (!empty($r['encrypted_amount'])) {
                try {
                    $r['decrypted_amount'] = $this->decryptAmount($r['encrypted_amount']);
                } catch (Exception $ex) {
                    $r['decrypted_amount'] = '0.00';
                }
            } else {
                $r['decrypted_amount'] = '0.00';
            }
        }
        unset($r);
        return $rows;
    }

    public function existsForTender(int $tenderId, int $contractorId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS total FROM bids WHERE tender_id = :tender_id AND contractor_id = :contractor_id');
        $stmt->execute(['tender_id' => $tenderId, 'contractor_id' => $contractorId]);
        $row = $stmt->fetch();
        return (int)$row['total'] > 0;
    }

    public function getById(int $bidId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM bids WHERE bid_id = :bid_id LIMIT 1');
        $stmt->execute(['bid_id' => $bidId]);
        return $stmt->fetch();
    }

    private function encryptAmount(float $amount): string
    {
        $iv = random_bytes(16);
        $cipherText = openssl_encrypt((string)$amount, 'AES-256-CBC', hash('sha256', $this->key, true), OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $cipherText);
    }

    public function decryptAmount(string $payload): string
    {
        $data = base64_decode($payload);
        $iv = substr($data, 0, 16);
        $cipherText = substr($data, 16);
        return openssl_decrypt($cipherText, 'AES-256-CBC', hash('sha256', $this->key, true), OPENSSL_RAW_DATA, $iv);
    }

    public function delete(int $bidId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM bids WHERE bid_id = :bid_id');
        return $stmt->execute(['bid_id' => $bidId]);
    }
}
