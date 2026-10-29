<?php
/**
 * ConsensusModel — manages the final decision justification from the evaluator team.
 * Fulfills the "Evaluator Meeting" and "Transparency" objectives.
 */
class ConsensusModel extends BaseModel
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        // Auto-create table if missing (prevents crash on first run)
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS evaluation_consensus (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tender_id INT NOT NULL,
            winner_bid_id INT NOT NULL,
            justification TEXT NOT NULL,
            evaluator_ids VARCHAR(500) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_tender_id (tender_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO evaluation_consensus (tender_id, winner_bid_id, justification, evaluator_ids) VALUES (:tender_id, :winner_bid_id, :justification, :evaluator_ids)');
        $stmt->execute([
            'tender_id'     => $data['tender_id'],
            'winner_bid_id' => $data['winner_bid_id'],
            'justification' => $this->sanitize($data['justification']),
            'evaluator_ids' => $data['evaluator_ids'],
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getByTender(int $tenderId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM evaluation_consensus WHERE tender_id = :id LIMIT 1');
        $stmt->execute(['id' => $tenderId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
