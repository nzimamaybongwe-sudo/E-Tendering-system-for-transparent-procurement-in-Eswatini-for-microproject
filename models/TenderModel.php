<?php
class TenderModel extends BaseModel
{
    private static bool $schemaChecked = false;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);

        if (!self::$schemaChecked) {
            $this->ensureTenderColumns();
            self::$schemaChecked = true;
        }
    }

    public function create(array $data): int
    {
        // Allow tenders to opt into a short-close/demo behavior via data or env var
        $shortCloseDefault = getenv('DEMO_SHORT_CLOSE') ? 1 : 0;
        $shortClose = isset($data['short_close']) ? ($data['short_close'] ? 1 : 0) : $shortCloseDefault;

        $stmt = $this->pdo->prepare('INSERT INTO tenders (title, tender_reference, description, budget, tender_fee, tender_security, required_grade, category, region, deadline, clarification_deadline, evaluation_deadline, site_visit_date, status, created_by, short_close, published_at, created_at, version) VALUES (:title, :tender_reference, :description, :budget, :tender_fee, :tender_security, :required_grade, :category, :region, :deadline, :clarification_deadline, :evaluation_deadline, :site_visit_date, :status, :created_by, :short_close, NOW(), NOW(), 1)');
        $stmt->execute([
            'title' => $this->sanitize($data['title']),
            'tender_reference' => isset($data['tender_reference']) ? $this->sanitize($data['tender_reference']) : null,
            'description' => $this->sanitize($data['description']),
            'budget' => $data['budget'],
            'tender_fee' => $data['tender_fee'] ?? 0.00,
            'tender_security' => !empty($data['tender_security']) ? floatval($data['tender_security']) : null,
            'required_grade' => $this->sanitize($data['required_grade']),
            'category' => $this->sanitize($data['category']),
            'region' => $this->sanitize($data['region'] ?? 'Hhohho'),
            'deadline' => $data['deadline'],
            'clarification_deadline' => !empty($data['clarification_deadline']) ? $data['clarification_deadline'] : null,
            'evaluation_deadline' => !empty($data['evaluation_deadline']) ? $data['evaluation_deadline'] : null,
            'site_visit_date' => !empty($data['site_visit_date']) ? $data['site_visit_date'] : null,
            'status' => $this->sanitize($data['status']),
            'created_by' => $data['created_by'],
            'short_close' => $shortClose,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    private function ensureTenderColumns(): void
    {
        $stmt = $this->pdo->prepare("SHOW COLUMNS FROM tenders LIKE 'tender_fee'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $this->pdo->exec('ALTER TABLE tenders ADD COLUMN tender_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER budget');
        }
        $stmt = $this->pdo->prepare("SHOW COLUMNS FROM tenders LIKE 'awarded_at'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $this->pdo->exec('ALTER TABLE tenders ADD COLUMN awarded_at DATETIME DEFAULT NULL AFTER consensus_meeting_scheduled_at');
        }
        $stmt = $this->pdo->prepare("SHOW COLUMNS FROM tenders LIKE 'evaluation_release_at'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $this->pdo->exec('ALTER TABLE tenders ADD COLUMN evaluation_release_at DATETIME DEFAULT NULL AFTER closure_notification_sent_at');
        }
        $stmt = $this->pdo->prepare("SHOW COLUMNS FROM tenders LIKE 'short_close'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $this->pdo->exec('ALTER TABLE tenders ADD COLUMN short_close TINYINT(1) NOT NULL DEFAULT 0 AFTER evaluation_release_at');
        }
        $stmt = $this->pdo->prepare("SHOW COLUMNS FROM tenders LIKE 'tender_reference'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $this->pdo->exec('ALTER TABLE tenders ADD COLUMN tender_reference VARCHAR(100) DEFAULT NULL AFTER title');
        }
        $stmt = $this->pdo->prepare("SHOW COLUMNS FROM tenders LIKE 'clarification_deadline'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $this->pdo->exec('ALTER TABLE tenders ADD COLUMN clarification_deadline DATETIME DEFAULT NULL AFTER deadline');
        }
        $stmt = $this->pdo->prepare("SHOW COLUMNS FROM tenders LIKE 'tender_security'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $this->pdo->exec('ALTER TABLE tenders ADD COLUMN tender_security DECIMAL(12,2) DEFAULT NULL AFTER tender_fee');
        }
        $stmt = $this->pdo->prepare("SHOW COLUMNS FROM tenders LIKE 'site_visit_date'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $this->pdo->exec('ALTER TABLE tenders ADD COLUMN site_visit_date DATETIME DEFAULT NULL AFTER evaluation_deadline');
        }
    }

    public function getPublishedFiltered(?string $region = null, ?string $category = null): array
    {
        $sql = 'SELECT * FROM tenders WHERE status = "published" AND deadline >= NOW()';
        $params = [];

        if ($region) {
            $sql .= ' AND region = :region';
            $params['region'] = $region;
        }
        if ($category) {
            $sql .= ' AND category = :category';
            $params['category'] = $category;
        }

        $sql .= ' ORDER BY deadline ASC, published_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->translateRows($stmt->fetchAll());
    }
    
    public function getOpen(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM tenders WHERE status = "published" AND deadline >= NOW() ORDER BY deadline ASC');
        return $this->translateRows($stmt->fetchAll());
    }

    public function getPublished(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM tenders WHERE status = "published" AND deadline >= NOW() ORDER BY deadline ASC, published_at DESC');
        return $this->translateRows($stmt->fetchAll());
    }

    public function getByStatus(string $status): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tenders WHERE status = :status ORDER BY deadline ASC');
        $stmt->execute(['status' => $status]);
        return $this->translateRows($stmt->fetchAll());
    }

    public function getById(int $tenderId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tenders WHERE tender_id = :tender_id LIMIT 1');
        $stmt->execute(['tender_id' => $tenderId]);
        return $this->translateRow($stmt->fetch());
    }

    public function updateTender(int $tenderId, array $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE tenders SET title = :title, description = :description, budget = :budget, tender_fee = :tender_fee, required_grade = :required_grade, category = :category, region = :region, deadline = :deadline, evaluation_deadline = :evaluation_deadline, version = version + 1, status = :status WHERE tender_id = :tender_id');
        return $stmt->execute([
            'title' => $this->sanitize($data['title']),
            'description' => $this->sanitize($data['description']),
            'budget' => $data['budget'],
            'tender_fee' => $data['tender_fee'] ?? 0.00,
            'required_grade' => $this->sanitize($data['required_grade']),
            'category' => $this->sanitize($data['category']),
            'region' => $this->sanitize($data['region'] ?? 'Hhohho'),
            'deadline' => $data['deadline'],
            'evaluation_deadline' => $data['evaluation_deadline'] ?? null,
            'status' => $this->sanitize($data['status']),
            'tender_id' => $tenderId,
        ]);
    }

    public function updateConsensusMeetingLink(int $tenderId, string $link): bool
    {
        $stmt = $this->pdo->prepare('UPDATE tenders SET consensus_meeting_link = :link, consensus_meeting_scheduled_at = NOW() WHERE tender_id = :tender_id');
        return $stmt->execute([
            'link' => $this->sanitize($link),
            'tender_id' => $tenderId,
        ]);
    }

    public function updateStatus(int $tenderId, string $status): bool
    {
        if ($status === 'awarded') {
            $stmt = $this->pdo->prepare('UPDATE tenders SET status = :status, awarded_at = NOW() WHERE tender_id = :tender_id');
            return $stmt->execute(['status' => $status, 'tender_id' => $tenderId]);
        }

        $stmt = $this->pdo->prepare('UPDATE tenders SET status = :status WHERE tender_id = :tender_id');
        return $stmt->execute(['status' => $status, 'tender_id' => $tenderId]);
    }

    public function markReleasedForEvaluation(int $tenderId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE tenders SET evaluation_release_at = NOW() WHERE tender_id = :tender_id');
        return $stmt->execute(['tender_id' => $tenderId]);
    }

    /**
     * Close tenders that are 30 minutes before their deadline
     * Tenders should close 30 minutes BEFORE the stated deadline to allow all contractors
     * enough time to bid before the tender closes
     */
    public function closeExpiredTenders(): void
    {
        // Close normal tenders that are within 30 minutes of deadline (short_close = 0 or NULL)
        $stmt = $this->pdo->prepare('
            UPDATE tenders 
            SET status = "closed" 
            WHERE status = "published" 
            AND DATE_SUB(deadline, INTERVAL 30 MINUTE) <= NOW()
            AND (short_close IS NULL OR short_close = 0)
            AND closure_notification_sent_at IS NULL
        ');
        $stmt->execute();

        // Close short-close tenders (demo) 30 minutes before deadline
        $stmt2 = $this->pdo->prepare('
            UPDATE tenders 
            SET status = "closed" 
            WHERE status = "published" 
            AND DATE_SUB(deadline, INTERVAL 30 MINUTE) <= NOW()
            AND short_close = 1
            AND closure_notification_sent_at IS NULL
        ');
        $stmt2->execute();

        // Auto-close any tenders that have passed their actual deadline (safety)
        $stmt3 = $this->pdo->prepare('
            UPDATE tenders 
            SET status = "closed" 
            WHERE status = "published" 
            AND deadline < NOW()
        ');
        $stmt3->execute();

        // --- AUTOMATIC EVALUATION RELEASE AND NOTIFICATION ---
        // Find all closed tenders that have NOT been released for evaluation yet
        $stmt4 = $this->pdo->prepare('
            SELECT tender_id, title 
            FROM tenders 
            WHERE status = "closed" 
            AND evaluation_release_at IS NULL
        ');
        $stmt4->execute();
        $unreleased = $stmt4->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($unreleased)) {
            require_once __DIR__ . '/NotificationModel.php';
            require_once __DIR__ . '/EvaluatorProfileModel.php';
            $notificationModel = new NotificationModel($this->pdo);
            $evaluatorModel = new EvaluatorProfileModel($this->pdo);
            $approvedEvaluators = $evaluatorModel->getApproved();

            foreach ($unreleased as $tender) {
                // 1. Mark as released for evaluation
                $this->markReleasedForEvaluation((int)$tender['tender_id']);

                // 2. Notify all approved evaluators
                foreach ($approvedEvaluators as $evaluator) {
                    $notificationModel->create(
                        (int)$evaluator['user_id'],
                        'Tender "' . $tender['title'] . '" has automatically passed its bidding deadline and is now open for evaluation. Please review and grade the bids.'
                    );
                }
            }
        }
    }

    public function cleanupExpiredClosedTenders(): void
    {
        // Only delete tenders that are closed but NEVER evaluated, after a long time, so we don't delete active ones.
        $stmt = $this->pdo->prepare('DELETE FROM tenders WHERE status = "closed" AND deadline < DATE_SUB(NOW(), INTERVAL 30 DAY)');
        $stmt->execute();
    }

    public function cleanupOldAwardedTenders(): void
    {
        // Redundant due to above cleanup, but kept as placeholder
        $stmt = $this->pdo->prepare('DELETE FROM tenders WHERE status = "awarded" AND awarded_at < DATE_SUB(NOW(), INTERVAL 2 DAY)');
        $stmt->execute();
    }

    public function cleanupTenderLifecycle(): void
    {
        $this->closeExpiredTenders();
        $this->cleanupExpiredClosedTenders();
        $this->cleanupOldAwardedTenders();
    }

    /**
     * Get tenders that should be notified of imminent closure (30 minutes before deadline)
     * Returns tenders where closure notification hasn't been sent yet
     */
    public function getTendersNeedingClosureNotification(): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM tenders 
            WHERE status = "published" 
            AND DATE_SUB(deadline, INTERVAL 30 MINUTE) <= NOW()
            AND DATE_SUB(deadline, INTERVAL 3 HOUR) > NOW()
            AND closure_notification_sent_at IS NULL
            ORDER BY deadline ASC
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Mark closure notification as sent for a tender
     */
    public function markClosureNotificationSent(int $tenderId): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE tenders 
            SET closure_notification_sent_at = NOW() 
            WHERE tender_id = :tender_id
        ');
        return $stmt->execute(['tender_id' => $tenderId]);
    }

    public function countPublished(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS total FROM tenders WHERE status = "published"');
        $row = $stmt->fetch();
        return (int)$row['total'];
    }

    public function countAll(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS total FROM tenders');
        $row = $stmt->fetch();
        return (int)$row['total'];
    }

    public function countOpen(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS total FROM tenders WHERE status = "published" AND deadline >= NOW()');
        $row = $stmt->fetch();
        return (int)$row['total'];
    }

    public function countAwarded(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS total FROM tenders WHERE status = "awarded" OR status = "completed"');
        $row = $stmt->fetch();
        return (int)$row['total'];
    }

    public function totalAwardedValue(): float
    {
        $stmt = $this->pdo->query('SELECT SUM(budget) AS total_value FROM tenders WHERE status IN ("awarded", "completed")');
        $row = $stmt->fetch();
        return (float)($row['total_value'] ?? 0.0);
    }

    /**
     * Get tenders ready for evaluation opening (deadline has passed, status is "closed")
     */
    public function getReadyForEvaluationOpening(): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM tenders 
            WHERE status = "closed" 
            AND deadline <= NOW()
            AND evaluation_session_id IS NULL
            ORDER BY deadline ASC
        ');
        $stmt->execute();
        return $this->translateRows($stmt->fetchAll());
    }

    /**
     * Start evaluation session for a tender
     */
    public function startEvaluationSession(int $tenderId, int $sessionId): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE tenders 
            SET evaluation_session_id = :session_id,
                evaluation_started_at = NOW(),
                evaluation_ends_at = DATE_ADD(NOW(), INTERVAL 120 MINUTE),
                status = "evaluated"
            WHERE tender_id = :tender_id
        ');
        return $stmt->execute([
            'tender_id' => $tenderId,
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Get tender with evaluation session details
     */
    public function getWithEvaluationSession(int $tenderId)
    {
        $stmt = $this->pdo->prepare('
            SELECT t.*, 
                   es.session_id, es.status as session_status, 
                   es.started_at as session_started_at, es.ends_at as session_ends_at,
                   es.duration_minutes
            FROM tenders t
            LEFT JOIN evaluation_sessions es ON t.evaluation_session_id = es.session_id
            WHERE t.tender_id = :tender_id
            LIMIT 1
        ');
        $stmt->execute(['tender_id' => $tenderId]);
        return $this->translateRow($stmt->fetch());
    }

    /**
     * Notify all contractors that a tender has closed
     * Called automatically when tender closes (1 day before deadline)
     * This should be integrated with a notification/email service
     */
    public function notifyContractorsOfClosure(int $tenderId): void
    {
        $tender = $this->getById($tenderId);
        if (!$tender) {
            return;
        }

        // Get all approved contractors
        $contractorModel = new ContractorModel($this->pdo);
        $contractors = $contractorModel->getApproved();

        // Create notifications for each contractor
        $notificationModel = new NotificationModel($this->pdo);
        $message = 'TENDER CLOSING NOTIFICATION: The tender "' . $tender['title'] . '" is now closing. ' .
                   'Bidding deadline is ' . $tender['deadline'] . '. ' .
                   'If you haven\'t submitted your bid yet, please do so immediately.';

        foreach ($contractors as $contractor) {
            $notificationModel->create($contractor['user_id'], $message);
        }

        // Mark that notifications have been sent
        $this->markClosureNotificationSent($tenderId);
    }

    public function getCategoryBreakdown(): array
    {
        $stmt = $this->pdo->query('SELECT category, COUNT(*) AS tender_count FROM tenders GROUP BY category ORDER BY tender_count DESC');
        return $stmt->fetchAll();
    }

    public function getRecentWinners(): array
    {
        $stmt = $this->pdo->query('
            SELECT t.title, t.title_si, c.company_name, ROUND(AVG(e.total_score), 2) as total_score, t.deadline, t.status 
            FROM evaluation_consensus ec 
            JOIN tenders t ON ec.tender_id = t.tender_id 
            JOIN bids b ON ec.winner_bid_id = b.bid_id 
            JOIN contractors c ON b.contractor_id = c.contractor_id 
            LEFT JOIN evaluations e ON b.bid_id = e.bid_id 
            WHERE t.status IN ("awarded", "completed") AND t.awarded_at >= DATE_SUB(NOW(), INTERVAL 2 DAY) 
            GROUP BY t.tender_id, t.title, t.title_si, c.company_name, t.deadline, t.status 
            ORDER BY t.awarded_at DESC LIMIT 5
        ');
        return $this->translateRows($stmt->fetchAll());
    }

    /**
     * Open a tender for meeting-based evaluation
     * Admin provides a meeting link (Zoom) and this opens the evaluation session
     */
    public function openForMeetingBasedEvaluation(int $tenderId, string $meetingLink, string $meetingPassword, int $adminUserId): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE tenders 
            SET status = "evaluated",
                meeting_link = :meeting_link,
                meeting_password = :meeting_password,
                meeting_start_time = NOW(),
                tender_opened_for_evaluation_at = NOW(),
                evaluation_deadline = DATE_ADD(NOW(), INTERVAL 2 HOUR),
                opened_for_evaluation_by = :admin_user_id
            WHERE tender_id = :tender_id
        ');
        return $stmt->execute([
            'tender_id' => $tenderId,
            'meeting_link' => $meetingLink,
            'meeting_password' => $meetingPassword,
            'admin_user_id' => $adminUserId,
        ]);
    }

    /**
     * Update meeting link for a tender
     */
    public function updateMeetingLink(int $tenderId, string $meetingLink, string $meetingPassword = ''): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE tenders 
            SET meeting_link = :meeting_link,
                meeting_password = :meeting_password
            WHERE tender_id = :tender_id
        ');
        return $stmt->execute([
            'tender_id' => $tenderId,
            'meeting_link' => $meetingLink,
            'meeting_password' => $meetingPassword,
        ]);
    }

    /**
     * Get tenders opened for evaluation (with meeting link)
     */
    public function getOpenedForEvaluation(): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM tenders 
            WHERE status = "evaluated" 
            AND meeting_link IS NOT NULL
            ORDER BY tender_opened_for_evaluation_at DESC
        ');
        $stmt->execute();
        return $this->translateRows($stmt->fetchAll());
    }

    /**
     * Get tender with assigned evaluators
     */
    public function getTenderWithEvaluators(int $tenderId): array
    {
        $tender = $this->getById($tenderId);
        if (!$tender) {
            return [];
        }

        // Get all bids and their evaluators
        $stmt = $this->pdo->prepare('
            SELECT DISTINCT u.user_id, u.username, ep.full_name
            FROM bids b
            JOIN evaluations e ON b.bid_id = e.bid_id
            JOIN users u ON e.evaluator_id = u.user_id
            JOIN evaluator_profiles ep ON u.user_id = ep.user_id
            WHERE b.tender_id = :tender_id
        ');
        $stmt->execute(['tender_id' => $tenderId]);
        $evaluators = $stmt->fetchAll();

        $tender['evaluators'] = $evaluators;
        return $tender;
    }

    /**
     * Get all contractors who bid on a tender
     */
    public function getContractorsWhoBid(int $tenderId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT DISTINCT u.user_id, u.username, c.company_name, c.contractor_id
            FROM bids b
            JOIN contractors c ON b.contractor_id = c.contractor_id
            JOIN users u ON c.user_id = u.user_id
            WHERE b.tender_id = :tender_id
        ');
        $stmt->execute(['tender_id' => $tenderId]);
        return $stmt->fetchAll();
    }

    private function translateRow($row)
    {
        if (!$row) return $row;
        $lang = $_SESSION['lang'] ?? 'en';
        if ($lang === 'ss') {
            if (!empty($row['title_si'])) {
                $row['title'] = $row['title_si'];
            }
            if (!empty($row['description_si'])) {
                $row['description'] = $row['description_si'];
            }
        }
        return $row;
    }

    private function translateRows(array $rows): array
    {
        foreach ($rows as &$row) {
            $row = $this->translateRow($row);
        }
        return $rows;
    }
}
