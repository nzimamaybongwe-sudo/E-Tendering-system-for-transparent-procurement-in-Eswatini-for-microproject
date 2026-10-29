<?php
class EvaluationModel extends BaseModel
{
    public function calculateScore(float $technical, float $financial, float $localBonus, bool $responsivenessPass, array $rubric = null): float
    {
        $responsiveness = $responsivenessPass ? 100.0 : 0.0;
        $technicalValue = min(max($technical, 0), 100);
        $financialValue = min(max($financial, 0), 100);
        $localValue     = min(max($localBonus, 0), 100);

        if ($rubric) {
            $tW = (float)$rubric['technical_weight'] / 100;
            $fW = (float)$rubric['financial_weight'] / 100;
            $lW = (float)$rubric['local_weight'] / 100;
            // 5% is reserved for mandatory responsiveness pass
            return ($technicalValue * $tW * 0.95) + ($financialValue * $fW * 0.95) + ($localValue * $lW * 0.95) + ($responsiveness * 0.05);
        }

        // Fallback to defaults
        return ($technicalValue * 0.50) + ($financialValue * 0.40) + ($localValue * 0.05) + ($responsiveness * 0.05);
    }

    public function create(array $data): int
    {
        // If evaluator is recused due to COI, save zeros and flag — do NOT count in ranking
        $isRecused = !empty($data['coi_recused']);

        $stmt = $this->pdo->prepare('
            INSERT INTO evaluations
                (bid_id, evaluator_id, technical_score, financial_score, responsiveness_pass,
                 local_bonus, total_score, conflict_of_interest_declared, coi_recused, coi_reason, remarks, evaluated_at)
            VALUES
                (:bid_id, :evaluator_id, :technical_score, :financial_score, :responsiveness_pass,
                 :local_bonus, :total_score, :conflict_of_interest_declared, :coi_recused, :coi_reason, :remarks, NOW())
        ');
        $stmt->execute([
            'bid_id'                       => $data['bid_id'],
            'evaluator_id'                 => $data['evaluator_id'],
            'technical_score'              => $isRecused ? 0 : $data['technical_score'],
            'financial_score'              => $isRecused ? 0 : $data['financial_score'],
            'responsiveness_pass'          => $isRecused ? 0 : ($data['responsiveness_pass'] ? 1 : 0),
            'local_bonus'                  => $isRecused ? 0 : $data['local_bonus'],
            'total_score'                  => $isRecused ? 0 : $data['total_score'],
            'conflict_of_interest_declared'=> $data['conflict_of_interest_declared'] ? 1 : 0,
            'coi_recused'                  => $isRecused ? 1 : 0,
            'coi_reason'                   => $this->sanitize($data['coi_reason'] ?? ''),
            'remarks'                      => $this->sanitize($data['remarks'] ?? ''),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getByTender(int $tenderId): array
    {
        // Exclude recused rows from ranking — they must not affect scores
        $stmt = $this->pdo->prepare('
            SELECT e.*, c.company_name, b.tender_id
            FROM evaluations e
            JOIN bids b ON e.bid_id = b.bid_id
            JOIN contractors c ON b.contractor_id = c.contractor_id
            WHERE b.tender_id = :tender_id
              AND e.coi_recused = 0
            ORDER BY e.total_score DESC
        ');
        $stmt->execute(['tender_id' => $tenderId]);
        return $stmt->fetchAll();
    }

    /**
     * Returns one averaged row per bid across all non-recused evaluators.
     * Used by the Officer's award/consensus page for a clean leaderboard.
     */
    public function getAggregateByTender(int $tenderId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT
                b.bid_id,
                b.tender_id,
                c.company_name,
                ROUND(AVG(e.technical_score), 2)  AS avg_technical,
                ROUND(AVG(e.financial_score), 2)  AS avg_financial,
                ROUND(AVG(e.local_bonus), 2)       AS avg_local,
                ROUND(AVG(e.total_score), 2)       AS avg_total,
                COUNT(DISTINCT e.evaluator_id)     AS evaluator_count,
                SUM(e.responsiveness_pass)         AS responsive_votes,
                GROUP_CONCAT(e.remarks SEPARATOR " | ") AS all_remarks
            FROM bids b
            JOIN contractors c ON b.contractor_id = c.contractor_id
            LEFT JOIN evaluations e ON e.bid_id = b.bid_id AND e.coi_recused = 0
            WHERE b.tender_id = :tender_id
            GROUP BY b.bid_id, b.tender_id, c.company_name
            ORDER BY avg_total DESC
        ');
        $stmt->execute(['tender_id' => $tenderId]);
        return $stmt->fetchAll();
    }

    public function rankCandidates(array $evaluations): array
    {
        usort($evaluations, function ($a, $b) {
            return $b['total_score'] <=> $a['total_score'];
        });
        return $evaluations;
    }

    public function getConflictAlerts(): array
    {
        $stmt = $this->pdo->query('SELECT e.*, c.company_name, t.title, u.username AS evaluator_name FROM evaluations e JOIN bids b ON e.bid_id = b.bid_id JOIN tenders t ON b.tender_id = t.tender_id JOIN contractors c ON b.contractor_id = c.contractor_id JOIN users u ON e.evaluator_id = u.user_id WHERE e.conflict_of_interest_declared = 1 ORDER BY e.evaluated_at DESC');
        return $stmt->fetchAll();
    }
}
