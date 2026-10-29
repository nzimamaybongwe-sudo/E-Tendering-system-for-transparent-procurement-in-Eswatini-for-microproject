<?php
/**
 * EvaluationCommentModel
 * 
 * Manages public comments and reviews posted by evaluators during evaluation sessions.
 * These comments provide transparency throughout the evaluation process.
 */
class EvaluationCommentModel extends BaseModel
{
    /**
     * Create a new evaluation comment
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO evaluation_comments 
            (tender_id, session_id, evaluator_id, bid_id, comment_text, comment_type, is_public, created_at)
            VALUES (:tender_id, :session_id, :evaluator_id, :bid_id, :comment_text, :comment_type, :is_public, NOW())
        ');
        $stmt->execute([
            'tender_id' => $data['tender_id'],
            'session_id' => $data['session_id'],
            'evaluator_id' => $data['evaluator_id'],
            'bid_id' => $data['bid_id'],
            'comment_text' => $this->sanitize($data['comment_text']),
            'comment_type' => $data['comment_type'] ?? 'general_comment',
            'is_public' => $data['is_public'] ?? 1,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Get all comments for a tender (for transparency)
     */
    public function getByTender(int $tenderId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT ec.*, u.username as evaluator_name, c.company_name
            FROM evaluation_comments ec
            JOIN users u ON ec.evaluator_id = u.user_id
            JOIN bids b ON ec.bid_id = b.bid_id
            JOIN contractors c ON b.contractor_id = c.contractor_id
            WHERE ec.tender_id = :tender_id AND ec.is_public = 1
            ORDER BY ec.created_at DESC
        ');
        $stmt->execute(['tender_id' => $tenderId]);
        return $stmt->fetchAll();
    }

    /**
     * Get comments for a specific bid during evaluation
     */
    public function getByBid(int $bidId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT ec.*, u.username as evaluator_name
            FROM evaluation_comments ec
            JOIN users u ON ec.evaluator_id = u.user_id
            WHERE ec.bid_id = :bid_id AND ec.is_public = 1
            ORDER BY ec.created_at DESC
        ');
        $stmt->execute(['bid_id' => $bidId]);
        return $stmt->fetchAll();
    }

    /**
     * Get comments for a specific evaluation session
     */
    public function getBySession(int $sessionId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT ec.*, u.username as evaluator_name, b.tender_id
            FROM evaluation_comments ec
            JOIN users u ON ec.evaluator_id = u.user_id
            JOIN bids b ON ec.bid_id = b.bid_id
            WHERE ec.session_id = :session_id AND ec.is_public = 1
            ORDER BY ec.created_at DESC
        ');
        $stmt->execute(['session_id' => $sessionId]);
        return $stmt->fetchAll();
    }

    /**
     * Get comments by evaluator for a session
     */
    public function getByEvaluatorAndSession(int $evaluatorId, int $sessionId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM evaluation_comments
            WHERE evaluator_id = :evaluator_id AND session_id = :session_id
            ORDER BY created_at DESC
        ');
        $stmt->execute([
            'evaluator_id' => $evaluatorId,
            'session_id' => $sessionId,
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Get comments by type for a tender (for filtering/analysis)
     */
    public function getByTenderAndType(int $tenderId, string $commentType): array
    {
        $stmt = $this->pdo->prepare('
            SELECT ec.*, u.username as evaluator_name
            FROM evaluation_comments ec
            JOIN users u ON ec.evaluator_id = u.user_id
            WHERE ec.tender_id = :tender_id AND ec.comment_type = :comment_type AND ec.is_public = 1
            ORDER BY ec.created_at DESC
        ');
        $stmt->execute([
            'tender_id' => $tenderId,
            'comment_type' => $commentType,
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Get flagged comments (issues raised by evaluators)
     */
    public function getFlaggedComments(int $tenderId): array
    {
        return $this->getByTenderAndType($tenderId, 'flag');
    }

    /**
     * Delete a comment (only by creator or admin)
     */
    public function delete(int $commentId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM evaluation_comments WHERE comment_id = :comment_id');
        return $stmt->execute(['comment_id' => $commentId]);
    }

    /**
     * Update comment visibility
     */
    public function updateVisibility(int $commentId, bool $isPublic): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE evaluation_comments SET is_public = :is_public WHERE comment_id = :comment_id
        ');
        return $stmt->execute([
            'comment_id' => $commentId,
            'is_public' => $isPublic ? 1 : 0,
        ]);
    }
}
