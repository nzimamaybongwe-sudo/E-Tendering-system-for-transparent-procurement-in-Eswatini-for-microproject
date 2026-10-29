-- ============================================================
-- E-Tendering System: Meeting-Based Evaluation Migration
-- Adds evaluation_sessions and zoom_meeting_requests tables
-- Modifies tenders table for meeting link integration
-- ============================================================
USE e_tendering;
-- ── 1. Evaluation Sessions Table ─────────────────────────────────────────────
-- Manages the 2-hour evaluation period for each tender
-- Each tender gets one evaluation session when opened for evaluation

CREATE TABLE IF NOT EXISTS evaluation_sessions (
    session_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id INT UNSIGNED NOT NULL UNIQUE,
    meeting_link VARCHAR(500) DEFAULT NULL,
    meeting_password VARCHAR(50) DEFAULT NULL,
    status ENUM('not_started', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'not_started',
    duration_minutes INT UNSIGNED NOT NULL DEFAULT 120,
    started_at DATETIME DEFAULT NULL,
    ends_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tender_id) REFERENCES tenders(tender_id) ON DELETE CASCADE,
    INDEX idx_tender (tender_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 2. Zoom Meeting Requests Table ──────────────────────────────────────────
-- Tracks zoom meeting requests made by evaluators or admins during evaluation
-- NEW WORKFLOW: Meeting link is auto-generated and sent to all participants

CREATE TABLE IF NOT EXISTS zoom_meeting_requests (
    request_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id INT UNSIGNED NOT NULL,
    session_id INT UNSIGNED NOT NULL,
    requested_by INT UNSIGNED NOT NULL,
    approved_by INT UNSIGNED DEFAULT NULL,
    reason TEXT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    meeting_url VARCHAR(500) DEFAULT NULL,
    password VARCHAR(50) DEFAULT NULL,
    start_time DATETIME DEFAULT NULL,
    end_time DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    FOREIGN KEY (tender_id) REFERENCES tenders(tender_id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES evaluation_sessions(session_id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_tender (tender_id),
    INDEX idx_session (session_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. Evaluator Attendance Tracking ────────────────────────────────────────
-- Track which evaluators attended the meeting and when they joined/left

CREATE TABLE IF NOT EXISTS evaluation_attendance (
    attendance_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    evaluator_id INT UNSIGNED NOT NULL,
    joined_at DATETIME NOT NULL,
    left_at DATETIME DEFAULT NULL,
    status ENUM('late', 'on_time', 'absent') NOT NULL DEFAULT 'on_time',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES evaluation_sessions(session_id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_evaluator (evaluator_id),
    UNIQUE KEY unique_session_evaluator (session_id, evaluator_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 4. Modify Tenders Table to Add Meeting Link Fields ─────────────────────
-- These fields will be populated when admin opens tender for meeting-based evaluation

ALTER TABLE tenders 
ADD COLUMN meeting_link VARCHAR(500) DEFAULT NULL AFTER consensus_meeting_scheduled_at;

ALTER TABLE tenders 
ADD COLUMN meeting_start_time DATETIME DEFAULT NULL AFTER meeting_link;

ALTER TABLE tenders 
ADD COLUMN meeting_password VARCHAR(50) DEFAULT NULL AFTER meeting_start_time;

ALTER TABLE tenders 
ADD COLUMN tender_opened_for_evaluation_at DATETIME DEFAULT NULL AFTER awarded_at;

ALTER TABLE tenders 
ADD COLUMN opened_for_evaluation_by INT UNSIGNED DEFAULT NULL AFTER tender_opened_for_evaluation_at;

ALTER TABLE tenders 
ADD FOREIGN KEY fk_opened_by (opened_for_evaluation_by) REFERENCES users(user_id) ON DELETE SET NULL;

-- ── 5. Add Columns to Track Evaluation Session Status ──────────────────────

ALTER TABLE evaluations 
ADD COLUMN attended_meeting TINYINT(1) NOT NULL DEFAULT 0 AFTER evaluated_at;

ALTER TABLE evaluations 
ADD COLUMN evaluation_during_meeting TINYINT(1) NOT NULL DEFAULT 1 AFTER attended_meeting;

ALTER TABLE evaluations 
ADD COLUMN revised_technical_score DECIMAL(5,2) DEFAULT NULL AFTER technical_score;

ALTER TABLE evaluations 
ADD COLUMN revised_financial_score DECIMAL(5,2) DEFAULT NULL AFTER financial_score;

ALTER TABLE evaluations 
ADD COLUMN revised_local_bonus DECIMAL(5,2) DEFAULT NULL AFTER local_bonus;

ALTER TABLE evaluations 
ADD COLUMN revised_total_score DECIMAL(6,2) DEFAULT NULL AFTER total_score;

-- ── 6. Index for Performance ────────────────────────────────────────────────

CREATE INDEX idx_tenders_status ON tenders(status);
CREATE INDEX idx_tenders_opened_for_evaluation ON tenders(tender_opened_for_evaluation_at);
CREATE INDEX idx_evaluations_tender ON evaluations(bid_id);

-- ============================================================
-- Migration Complete
-- ============================================================
