<?php
/**
 * EvaluatorReadinessModel
 * 
 * Tracks the readiness of evaluators during evaluation sessions.
 * Evaluators can mark themselves as "ready to close" once they've completed
 * all evaluations and reviews. Once all evaluators are ready, the admin can
 * close the evaluation and publish the winner.
 */
class EvaluatorReadinessModel extends BaseModel
{
    /**
     * Create readiness tracking for an evaluator in a session
     */
    public function create(int $sessionId, int $tenderId, int $evaluatorId): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO evaluator_readiness 
            (session_id, tender_id, evaluator_id, is_ready_to_close, all_evaluations_done, created_at)
            VALUES (:session_id, :tender_id, :evaluator_id, 0, 0, NOW())
        ');
        $stmt->execute([
            'session_id' => $sessionId,
            'tender_id' => $tenderId,
            'evaluator_id' => $evaluatorId,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Get readiness record for an evaluator in a session
     */
    public function getByEvaluatorAndSession(int $evaluatorId, int $sessionId)
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM evaluator_readiness 
            WHERE evaluator_id = :evaluator_id AND session_id = :session_id
            LIMIT 1
        ');
        $stmt->execute([
            'evaluator_id' => $evaluatorId,
            'session_id' => $sessionId,
        ]);
        return $stmt->fetch();
    }

    /**
     * Mark all evaluations as done for an evaluator
     */
    public function markAllEvaluationsDone(int $evaluatorId, int $sessionId): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE evaluator_readiness 
            SET all_evaluations_done = 1
            WHERE evaluator_id = :evaluator_id AND session_id = :session_id
        ');
        return $stmt->execute([
            'evaluator_id' => $evaluatorId,
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Mark evaluator as ready to close evaluation
     */
    public function markReadyToClose(int $evaluatorId, int $sessionId): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE evaluator_readiness 
            SET is_ready_to_close = 1, marked_ready_at = NOW()
            WHERE evaluator_id = :evaluator_id AND session_id = :session_id
        ');
        return $stmt->execute([
            'evaluator_id' => $evaluatorId,
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Mark evaluator as not ready
     */
    public function markNotReady(int $evaluatorId, int $sessionId): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE evaluator_readiness 
            SET is_ready_to_close = 0, marked_ready_at = NULL
            WHERE evaluator_id = :evaluator_id AND session_id = :session_id
        ');
        return $stmt->execute([
            'evaluator_id' => $evaluatorId,
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Get all evaluators for a session
     */
    public function getBySession(int $sessionId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT er.*, u.username, u.email
            FROM evaluator_readiness er
            JOIN users u ON er.evaluator_id = u.user_id
            WHERE er.session_id = :session_id
            ORDER BY er.marked_ready_at DESC
        ');
        $stmt->execute(['session_id' => $sessionId]);
        return $stmt->fetchAll();
    }

    /**
     * Check if all evaluators are ready to close
     */
    public function areAllReady(int $sessionId): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as total FROM evaluator_readiness WHERE session_id = :session_id
        ');
        $stmt->execute(['session_id' => $sessionId]);
        $total = (int)$stmt->fetch()['total'];

        if ($total === 0) {
            return false;
        }

        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as ready FROM evaluator_readiness 
            WHERE session_id = :session_id AND is_ready_to_close = 1
        ');
        $stmt->execute(['session_id' => $sessionId]);
        $ready = (int)$stmt->fetch()['ready'];

        return $ready === $total;
    }

    /**
     * Get count of evaluators ready to close
     */
    public function getReadyCount(int $sessionId): int
    {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as ready FROM evaluator_readiness 
            WHERE session_id = :session_id AND is_ready_to_close = 1
        ');
        $stmt->execute(['session_id' => $sessionId]);
        return (int)$stmt->fetch()['ready'];
    }

    /**
     * Get total count of evaluators for a session
     */
    public function getTotalCount(int $sessionId): int
    {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as total FROM evaluator_readiness WHERE session_id = :session_id
        ');
        $stmt->execute(['session_id' => $sessionId]);
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Get evaluators who are not yet ready
     */
    public function getNotReady(int $sessionId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT er.*, u.username, u.email
            FROM evaluator_readiness er
            JOIN users u ON er.evaluator_id = u.user_id
            WHERE er.session_id = :session_id AND er.is_ready_to_close = 0
            ORDER BY u.username ASC
        ');
        $stmt->execute(['session_id' => $sessionId]);
        return $stmt->fetchAll();
    }
}
