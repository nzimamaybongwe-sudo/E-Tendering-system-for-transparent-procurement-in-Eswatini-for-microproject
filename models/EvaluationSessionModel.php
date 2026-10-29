<?php
/**
 * EvaluationSessionModel
 * 
 * Manages evaluation sessions for tenders. An evaluation session represents
 * the 2-hour evaluation period after the tender deadline, during which evaluators
 * can review bids, submit evaluations, and post public comments.
 */
class EvaluationSessionModel extends BaseModel
{
    /**
     * Create a new evaluation session for a tender
     */
    public function create(int $tenderId, int $durationMinutes = 120): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO evaluation_sessions (tender_id, status, duration_minutes, created_at)
            VALUES (:tender_id, :status, :duration_minutes, NOW())
        ');
        $stmt->execute([
            'tender_id' => $tenderId,
            'status' => 'not_started',
            'duration_minutes' => $durationMinutes,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Start an evaluation session
     */
    public function start(int $sessionId): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE evaluation_sessions 
            SET status = :status, started_at = NOW(), ends_at = DATE_ADD(NOW(), INTERVAL duration_minutes MINUTE)
            WHERE session_id = :session_id
        ');
        return $stmt->execute([
            'session_id' => $sessionId,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Get session by tender ID
     */
    public function getByTender(int $tenderId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM evaluation_sessions WHERE tender_id = :tender_id LIMIT 1');
        $stmt->execute(['tender_id' => $tenderId]);
        return $stmt->fetch();
    }

    /**
     * Get session by session ID
     */
    public function getById(int $sessionId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM evaluation_sessions WHERE session_id = :session_id LIMIT 1');
        $stmt->execute(['session_id' => $sessionId]);
        return $stmt->fetch();
    }

    /**
     * Check if session is still active (countdown not expired)
     */
    public function isActive(int $sessionId): bool
    {
        $session = $this->getById($sessionId);
        if (!$session) {
            return false;
        }
        return $session['status'] === 'in_progress' && 
               (strtotime($session['ends_at']) > time());
    }

    /**
     * Get remaining time in minutes
     */
    public function getRemainingMinutes(int $sessionId): int
    {
        $session = $this->getById($sessionId);
        if (!$session || !$session['ends_at']) {
            return 0;
        }
        $remaining = (strtotime($session['ends_at']) - time()) / 60;
        return max(0, (int)$remaining);
    }

    /**
     * Check if all evaluators are ready to close evaluation
     */
    public function areAllEvaluatorsReady(int $sessionId): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as total FROM evaluator_readiness 
            WHERE session_id = :session_id
        ');
        $stmt->execute(['session_id' => $sessionId]);
        $totalEvaluators = (int)$stmt->fetch()['total'];

        if ($totalEvaluators === 0) {
            return false;
        }

        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as ready FROM evaluator_readiness 
            WHERE session_id = :session_id AND is_ready_to_close = 1
        ');
        $stmt->execute(['session_id' => $sessionId]);
        $readyEvaluators = (int)$stmt->fetch()['ready'];

        return $readyEvaluators === $totalEvaluators;
    }

    /**
     * Close evaluation session
     */
    public function close(int $sessionId): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE evaluation_sessions 
            SET status = :status
            WHERE session_id = :session_id
        ');
        return $stmt->execute([
            'session_id' => $sessionId,
            'status' => 'closed',
        ]);
    }

    /**
     * Get all active sessions
     */
    public function getActive(): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM evaluation_sessions 
            WHERE status = "in_progress" AND ends_at > NOW()
            ORDER BY ends_at ASC
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get sessions that have expired (time exceeded but not closed)
     */
    public function getExpired(): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM evaluation_sessions 
            WHERE status = "in_progress" AND ends_at <= NOW()
            ORDER BY ends_at DESC
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Add meeting link and password to session (for Zoom)
     */
    public function setMeetingLink(int $sessionId, string $meetingLink, string $meetingPassword = ''): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE evaluation_sessions 
            SET meeting_link = :meeting_link, meeting_password = :meeting_password
            WHERE session_id = :session_id
        ');
        return $stmt->execute([
            'session_id' => $sessionId,
            'meeting_link' => $meetingLink,
            'meeting_password' => $meetingPassword,
        ]);
    }

    /**
     * Record evaluator attendance
     */
    public function recordAttendance(int $sessionId, int $evaluatorId, string $status = 'on_time'): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO evaluation_attendance (session_id, evaluator_id, joined_at, status, created_at)
            VALUES (:session_id, :evaluator_id, NOW(), :status, NOW())
            ON DUPLICATE KEY UPDATE 
                joined_at = NOW(),
                status = :status
        ');
        $stmt->execute([
            'session_id' => $sessionId,
            'evaluator_id' => $evaluatorId,
            'status' => $status,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Get attendance records for a session
     */
    public function getAttendance(int $sessionId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT ea.*, u.username, ep.full_name, ep.evaluator_code
            FROM evaluation_attendance ea
            JOIN users u ON ea.evaluator_id = u.user_id
            JOIN evaluator_profiles ep ON u.user_id = ep.user_id
            WHERE ea.session_id = :session_id
            ORDER BY ea.joined_at ASC
        ');
        $stmt->execute(['session_id' => $sessionId]);
        return $stmt->fetchAll();
    }

    /**
     * Check if evaluator attended
     */
    public function didEvaluatorAttend(int $sessionId, int $evaluatorId): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT attendance_id FROM evaluation_attendance 
            WHERE session_id = :session_id AND evaluator_id = :evaluator_id
            LIMIT 1
        ');
        $stmt->execute([
            'session_id' => $sessionId,
            'evaluator_id' => $evaluatorId,
        ]);
        return $stmt->fetch() !== false;
    }

    /**
     * Get evaluator attendance status
     */
    public function getEvaluatorAttendanceStatus(int $sessionId, int $evaluatorId)
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM evaluation_attendance 
            WHERE session_id = :session_id AND evaluator_id = :evaluator_id
            LIMIT 1
        ');
        $stmt->execute([
            'session_id' => $sessionId,
            'evaluator_id' => $evaluatorId,
        ]);
        return $stmt->fetch();
    }
}
