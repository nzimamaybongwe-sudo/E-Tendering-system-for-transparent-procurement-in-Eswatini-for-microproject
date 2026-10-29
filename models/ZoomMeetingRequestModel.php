<?php
/**
 * ZoomMeetingRequestModel
 * 
 * Manages zoom meeting requests during evaluation sessions.
 * Evaluators or admins can request zoom calls for live discussions
 * if needed during the 2-hour evaluation period.
 */
class ZoomMeetingRequestModel extends BaseModel
{
    /**
     * Create a zoom meeting request
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO zoom_meeting_requests 
            (tender_id, session_id, requested_by, reason, status, created_at)
            VALUES (:tender_id, :session_id, :requested_by, :reason, :status, NOW())
        ');
        $stmt->execute([
            'tender_id' => $data['tender_id'],
            'session_id' => $data['session_id'],
            'requested_by' => $data['requested_by'],
            'reason' => $this->sanitize($data['reason']),
            'status' => $data['status'] ?? 'pending',
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Get all zoom requests for a tender
     */
    public function getByTender(int $tenderId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT zmr.*, u.username as requested_by_name, a.username as approved_by_name
            FROM zoom_meeting_requests zmr
            JOIN users u ON zmr.requested_by = u.user_id
            LEFT JOIN users a ON zmr.approved_by = a.user_id
            WHERE zmr.tender_id = :tender_id
            ORDER BY zmr.created_at DESC
        ');
        $stmt->execute(['tender_id' => $tenderId]);
        return $stmt->fetchAll();
    }

    /**
     * Get zoom requests for a specific session
     */
    public function getBySession(int $sessionId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT zmr.*, u.username as requested_by_name, a.username as approved_by_name
            FROM zoom_meeting_requests zmr
            JOIN users u ON zmr.requested_by = u.user_id
            LEFT JOIN users a ON zmr.approved_by = a.user_id
            WHERE zmr.session_id = :session_id
            ORDER BY zmr.created_at DESC
        ');
        $stmt->execute(['session_id' => $sessionId]);
        return $stmt->fetchAll();
    }

    /**
     * Get pending zoom requests
     */
    public function getPending(): array
    {
        $stmt = $this->pdo->prepare('
            SELECT zmr.*, u.username as requested_by_name, t.title as tender_title
            FROM zoom_meeting_requests zmr
            JOIN users u ON zmr.requested_by = u.user_id
            JOIN tenders t ON zmr.tender_id = t.tender_id
            WHERE zmr.status = "pending"
            ORDER BY zmr.created_at ASC
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get approved zoom requests for a session
     */
    public function getApprovedBySession(int $sessionId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT zmr.*, u.username as requested_by_name
            FROM zoom_meeting_requests zmr
            JOIN users u ON zmr.requested_by = u.user_id
            WHERE zmr.session_id = :session_id AND zmr.status = "approved"
            ORDER BY zmr.approved_at DESC
        ');
        $stmt->execute(['session_id' => $sessionId]);
        return $stmt->fetchAll();
    }

    /**
     * Approve a zoom meeting request
     */
    public function approve(int $requestId, int $adminId, string $meetingLink): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE zoom_meeting_requests 
            SET status = "approved", approved_by = :admin_id, approved_at = NOW(), 
                meeting_link = :meeting_link
            WHERE request_id = :request_id
        ');
        return $stmt->execute([
            'request_id' => $requestId,
            'admin_id' => $adminId,
            'meeting_link' => $this->sanitize($meetingLink),
        ]);
    }

    /**
     * Reject a zoom meeting request
     */
    public function reject(int $requestId, int $adminId): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE zoom_meeting_requests 
            SET status = "cancelled", approved_by = :admin_id, approved_at = NOW()
            WHERE request_id = :request_id
        ');
        return $stmt->execute([
            'request_id' => $requestId,
            'admin_id' => $adminId,
        ]);
    }

    /**
     * Mark a zoom meeting as in progress
     */
    public function markInProgress(int $requestId): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE zoom_meeting_requests 
            SET status = "in_progress", scheduled_at = NOW()
            WHERE request_id = :request_id
        ');
        return $stmt->execute(['request_id' => $requestId]);
    }

    /**
     * Mark a zoom meeting as completed
     */
    public function markCompleted(int $requestId): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE zoom_meeting_requests 
            SET status = "completed"
            WHERE request_id = :request_id
        ');
        return $stmt->execute(['request_id' => $requestId]);
    }

    /**
     * Get request by ID
     */
    public function getById(int $requestId)
    {
        $stmt = $this->pdo->prepare('
            SELECT zmr.*, u.username as requested_by_name, a.username as approved_by_name
            FROM zoom_meeting_requests zmr
            JOIN users u ON zmr.requested_by = u.user_id
            LEFT JOIN users a ON zmr.approved_by = a.user_id
            WHERE zmr.request_id = :request_id
            LIMIT 1
        ');
        $stmt->execute(['request_id' => $requestId]);
        return $stmt->fetch();
    }

    /**
     * Check if there's an active zoom meeting for a session
     */
    public function hasActiveZoomSession(int $sessionId): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as count FROM zoom_meeting_requests
            WHERE session_id = :session_id AND status IN ("in_progress", "approved")
        ');
        $stmt->execute(['session_id' => $sessionId]);
        $result = $stmt->fetch();
        return (int)$result['count'] > 0;
    }

    /**
     * Get active zoom link for a session
     */
    public function getActiveZoomLink(int $sessionId): ?string
    {
        $stmt = $this->pdo->prepare('
            SELECT meeting_link FROM zoom_meeting_requests
            WHERE session_id = :session_id AND status = "in_progress" AND meeting_link IS NOT NULL
            LIMIT 1
        ');
        $stmt->execute(['session_id' => $sessionId]);
        $result = $stmt->fetch();
        return $result['meeting_link'] ?? null;
    }
}
