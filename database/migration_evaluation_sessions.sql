-- ============================================================
-- Migration: Add Evaluation Session Management Tables
-- ============================================================
-- This migration adds tables to support the new evaluation workflow:
-- - 1 day before deadline: tender closes and notifications are sent
-- - On deadline day: tender opens for evaluation with 2-hour countdown
-- - Evaluators can submit comments publicly for transparency
-- - Zoom calls can be requested during evaluation
-- - Evaluators mark readiness to close evaluation collectively

USE e_tendering;

-- ── 1. Add columns to tenders table for evaluation session tracking ────────

ALTER TABLE tenders ADD COLUMN evaluation_session_id INT UNSIGNED DEFAULT NULL;
ALTER TABLE tenders ADD COLUMN evaluation_started_at DATETIME DEFAULT NULL;
ALTER TABLE tenders ADD COLUMN evaluation_ends_at DATETIME DEFAULT NULL;
ALTER TABLE tenders ADD COLUMN closure_notification_sent_at DATETIME DEFAULT NULL;

-- ── 2. Create evaluation_sessions table ──────────────────────────────────

CREATE TABLE IF NOT EXISTS evaluation_sessions (
    session_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id INT UNSIGNED NOT NULL UNIQUE,
    status ENUM('not_started','in_progress','completed','closed') NOT NULL DEFAULT 'not_started',
    started_at DATETIME DEFAULT NULL,
    ends_at DATETIME DEFAULT NULL,
    duration_minutes INT UNSIGNED DEFAULT 120,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tender_id) REFERENCES tenders(tender_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. Create evaluation_comments table (public reviews/transparency) ──────

CREATE TABLE IF NOT EXISTS evaluation_comments (
    comment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id INT UNSIGNED NOT NULL,
    session_id INT UNSIGNED NOT NULL,
    evaluator_id INT UNSIGNED NOT NULL,
    bid_id INT UNSIGNED NOT NULL,
    comment_text TEXT NOT NULL,
    comment_type ENUM('technical_observation','financial_observation','general_comment','flag') NOT NULL DEFAULT 'general_comment',
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tender_id) REFERENCES tenders(tender_id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES evaluation_sessions(session_id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (bid_id) REFERENCES bids(bid_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 4. Create evaluator_readiness table (track evaluator completion) ──────

CREATE TABLE IF NOT EXISTS evaluator_readiness (
    readiness_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    tender_id INT UNSIGNED NOT NULL,
    evaluator_id INT UNSIGNED NOT NULL,
    is_ready_to_close TINYINT(1) NOT NULL DEFAULT 0,
    all_evaluations_done TINYINT(1) NOT NULL DEFAULT 0,
    marked_ready_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_evaluator_per_session (session_id, evaluator_id),
    FOREIGN KEY (session_id) REFERENCES evaluation_sessions(session_id) ON DELETE CASCADE,
    FOREIGN KEY (tender_id) REFERENCES tenders(tender_id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 5. Create zoom_meeting_requests table ────────────────────────────────

CREATE TABLE IF NOT EXISTS zoom_meeting_requests (
    request_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id INT UNSIGNED NOT NULL,
    session_id INT UNSIGNED NOT NULL,
    requested_by INT UNSIGNED NOT NULL,
    reason TEXT NOT NULL,
    meeting_link VARCHAR(500) DEFAULT NULL,
    status ENUM('pending','approved','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
    approved_by INT UNSIGNED DEFAULT NULL,
    approved_at DATETIME DEFAULT NULL,
    scheduled_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tender_id) REFERENCES tenders(tender_id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES evaluation_sessions(session_id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 6. Add indexes for better query performance ────────────────────────────

CREATE INDEX idx_evaluation_sessions_tender ON evaluation_sessions(tender_id);
CREATE INDEX idx_evaluation_sessions_status ON evaluation_sessions(status);
CREATE INDEX idx_evaluation_comments_tender ON evaluation_comments(tender_id);
CREATE INDEX idx_evaluation_comments_session ON evaluation_comments(session_id);
CREATE INDEX idx_evaluation_comments_evaluator ON evaluation_comments(evaluator_id);
CREATE INDEX idx_evaluator_readiness_session ON evaluator_readiness(session_id);
CREATE INDEX idx_evaluator_readiness_tender ON evaluator_readiness(tender_id);
CREATE INDEX idx_evaluator_readiness_evaluator ON evaluator_readiness(evaluator_id);
CREATE INDEX idx_zoom_requests_tender ON zoom_meeting_requests(tender_id);
CREATE INDEX idx_zoom_requests_session ON zoom_meeting_requests(session_id);
CREATE INDEX idx_zoom_requests_status ON zoom_meeting_requests(status);

-- ============================================================
-- End of migration
-- ============================================================
