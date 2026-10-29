-- ============================================================
-- E-Tendering System for MicroProjects Eswatini
-- Consolidated Master Schema (Structure + Seed Data)
-- ============================================================

CREATE DATABASE IF NOT EXISTS e_tendering CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE e_tendering;

-- ── 1. User Management (Structure) ───────────────────────────────────────────

CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','evaluator','contractor') NOT NULL DEFAULT 'contractor',
    status ENUM('pending','active','suspended') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contractors (
    contractor_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    category VARCHAR(255) NOT NULL DEFAULT 'General',
    cic_grade VARCHAR(20) NOT NULL,
    verification_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    rejection_reason VARCHAR(255) DEFAULT NULL,
    tax_pin VARCHAR(80) NOT NULL,
    form_j_number VARCHAR(80) NOT NULL,
    local_ownership TINYINT(1) NOT NULL DEFAULT 0,
    contact_person VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(80) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    profile_summary TEXT DEFAULT NULL,
    blacklisted TINYINT(1) NOT NULL DEFAULT 0,
    blacklist_reason VARCHAR(255) DEFAULT NULL,
    blacklisted_at DATETIME DEFAULT NULL,
    verified_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_profiles (
    admin_profile_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    government_id VARCHAR(100) NOT NULL,
    authorization_reference VARCHAR(150) NOT NULL,
    office_phone VARCHAR(80) DEFAULT NULL,
    approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    reviewed_by INT UNSIGNED DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evaluator_profiles (
    evaluator_profile_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    evaluator_code VARCHAR(40) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    accreditation_number VARCHAR(120) NOT NULL,
    specialization VARCHAR(120) NOT NULL,
    institution VARCHAR(150) NOT NULL,
    experience_summary TEXT DEFAULT NULL,
    approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    rejection_reason VARCHAR(255) DEFAULT NULL,
    reviewed_by INT UNSIGNED DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 2. Tendering & Procurement (Structure) ──────────────────────────────────

CREATE TABLE IF NOT EXISTS tenders (
    tender_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    title_si VARCHAR(200) DEFAULT NULL,
    description TEXT NOT NULL,
    description_si TEXT DEFAULT NULL,
    budget DECIMAL(14,2) NOT NULL,
    required_grade VARCHAR(20) NOT NULL,
    category VARCHAR(80) NOT NULL DEFAULT 'Construction',
    region ENUM('Hhohho', 'Manzini', 'Shiselweni', 'Lubombo') NOT NULL DEFAULT 'Hhohho',
    deadline DATETIME NOT NULL,
    evaluation_deadline DATETIME DEFAULT NULL,
    consensus_meeting_link VARCHAR(400) DEFAULT NULL,
    consensus_meeting_scheduled_at DATETIME DEFAULT NULL,
    awarded_at DATETIME DEFAULT NULL,
    status ENUM('draft','published','closed','evaluated','awarded','completed') NOT NULL DEFAULT 'draft',
    version INT UNSIGNED NOT NULL DEFAULT 1,
    created_by INT UNSIGNED DEFAULT NULL,
    published_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tender_rubrics (
    rubric_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id       INT UNSIGNED NOT NULL UNIQUE,
    technical_weight DECIMAL(5,2) NOT NULL DEFAULT 50.00,
    financial_weight DECIMAL(5,2) NOT NULL DEFAULT 40.00,
    local_weight     DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    is_public       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tender_id) REFERENCES tenders(tender_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tender_rubric_proposals (
    proposal_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id       INT UNSIGNED NOT NULL,
    evaluator_id    INT UNSIGNED NOT NULL,
    technical_weight DECIMAL(5,2) NOT NULL DEFAULT 50.00,
    financial_weight DECIMAL(5,2) NOT NULL DEFAULT 40.00,
    local_weight     DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    status          ENUM('submitted','approved','rejected') NOT NULL DEFAULT 'submitted',
    notes           TEXT DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT NULL,
    FOREIGN KEY (tender_id) REFERENCES tenders(tender_id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tender_documents (
    document_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id     INT UNSIGNED NOT NULL,
    document_name VARCHAR(200) NOT NULL,
    document_path VARCHAR(400) NOT NULL,
    uploaded_by   INT UNSIGNED DEFAULT NULL,
    uploaded_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tender_id)   REFERENCES tenders(tender_id)  ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(user_id)      ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tender_amendments (
    amendment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id INT UNSIGNED NOT NULL,
    changed_by INT UNSIGNED DEFAULT NULL,
    previous_title VARCHAR(200) DEFAULT NULL,
    previous_description TEXT DEFAULT NULL,
    previous_budget DECIMAL(14,2) DEFAULT NULL,
    previous_required_grade VARCHAR(20) DEFAULT NULL,
    previous_deadline DATETIME DEFAULT NULL,
    previous_category VARCHAR(80) DEFAULT NULL,
    version_number INT UNSIGNED NOT NULL,
    notes TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tender_id) REFERENCES tenders(tender_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. Bidding & Evaluation (Structure) ──────────────────────────────────────

CREATE TABLE IF NOT EXISTS bids (
    bid_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id INT UNSIGNED NOT NULL,
    contractor_id INT UNSIGNED NOT NULL,
    encrypted_amount TEXT NOT NULL,
    submitted_at DATETIME NOT NULL,
    locked TINYINT(1) NOT NULL DEFAULT 0,
    digital_signature VARCHAR(255) DEFAULT NULL,
    eligibility_confirmed TINYINT(1) NOT NULL DEFAULT 0,
    conflict_declared TINYINT(1) NOT NULL DEFAULT 0,
    conflict_details TEXT DEFAULT NULL,
    method_statement TEXT DEFAULT NULL,
    form_of_tender_accepted TINYINT(1) NOT NULL DEFAULT 0,
    site_visit_date DATE DEFAULT NULL,
    site_visit_officer VARCHAR(150) DEFAULT NULL,
    FOREIGN KEY (tender_id) REFERENCES tenders(tender_id) ON DELETE CASCADE,
    FOREIGN KEY (contractor_id) REFERENCES contractors(contractor_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evaluations (
    evaluation_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bid_id INT UNSIGNED NOT NULL,
    evaluator_id INT UNSIGNED DEFAULT NULL,
    technical_score DECIMAL(5,2) NOT NULL,
    financial_score DECIMAL(5,2) NOT NULL,
    responsiveness_pass TINYINT(1) NOT NULL,
    local_bonus DECIMAL(5,2) NOT NULL,
    total_score DECIMAL(6,2) NOT NULL,
    conflict_of_interest_declared TINYINT(1) NOT NULL DEFAULT 0,
    coi_reason TEXT DEFAULT NULL,
    evaluated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bid_id) REFERENCES bids(bid_id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evaluation_consensus (
    consensus_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id       INT UNSIGNED NOT NULL UNIQUE,
    winner_bid_id   INT UNSIGNED NOT NULL,
    justification   TEXT NOT NULL,
    evaluator_ids   TEXT NOT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tender_id)     REFERENCES tenders(tender_id) ON DELETE CASCADE,
    FOREIGN KEY (winner_bid_id) REFERENCES bids(bid_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 4. Compliance & Showcase (Structure) ────────────────────────────────────

CREATE TABLE IF NOT EXISTS contractor_documents (
    document_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contractor_id INT UNSIGNED NOT NULL,
    document_name VARCHAR(200) NOT NULL,
    document_path VARCHAR(400) NOT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATE DEFAULT NULL,
    document_phase ENUM('phase1','phase2','phase3') NOT NULL DEFAULT 'phase1',
    is_expired TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (contractor_id) REFERENCES contractors(contractor_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contractor_portfolio (
    project_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contractor_id  INT UNSIGNED NOT NULL,
    project_name   VARCHAR(255) NOT NULL,
    client_name    VARCHAR(255) NOT NULL,
    project_value  DECIMAL(15,2) NOT NULL,
    completion_year YEAR NOT NULL,
    description    TEXT,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contractor_id) REFERENCES contractors(contractor_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS portfolio_media (
    media_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    file_path  VARCHAR(400) NOT NULL,
    file_type  VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES contractor_portfolio(project_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Appeals table stores contractor grievance cases against tender evaluations.
-- Required fields for display and dashboard counts:
--   tender_id      => existing tender reference
--   user_id        => submitting contractor user reference
--   bid_id         => contractor bid reference for the tender
--   subject        => shown in appeal lists and dashboard cards
--   appeal_reason  => full narrative shown on appeal details pages
--   status         => pending dashboards only count `submitted`
--   admin_response => optional message after admin review
--   responded_by   => admin user that responded
--   responded_at   => time of response
CREATE TABLE IF NOT EXISTS appeals (
    appeal_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    bid_id INT UNSIGNED NOT NULL,
    subject VARCHAR(200) NOT NULL,
    appeal_reason TEXT NOT NULL,
    status ENUM('submitted','under_review','resolved','rejected') NOT NULL DEFAULT 'submitted',
    admin_response TEXT DEFAULT NULL,
    responded_by INT UNSIGNED DEFAULT NULL,
    responded_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tender_id) REFERENCES tenders(tender_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (bid_id) REFERENCES bids(bid_id) ON DELETE CASCADE,
    FOREIGN KEY (responded_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bid_documents (
    bid_document_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bid_id INT UNSIGNED NOT NULL,
    document_name VARCHAR(200) NOT NULL,
    document_path VARCHAR(400) NOT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bid_id) REFERENCES bids(bid_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 5. System Utilities & Security (Structure) ───────────────────────────────

CREATE TABLE IF NOT EXISTS audit_logs (
    audit_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_user_id INT UNSIGNED NOT NULL,
    action_type VARCHAR(80) NOT NULL,
    target_table VARCHAR(80) NOT NULL,
    target_id INT UNSIGNED NOT NULL,
    details TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    immutable_flag TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (actor_user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    channel ENUM('email','sms','in-app') NOT NULL DEFAULT 'in-app',
    sent_at DATETIME DEFAULT NULL,
    read_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS password_resets (
    reset_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token      VARCHAR(100) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used       TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ── 6. Seed & Operational Data (Initial Data Setup) ─────────────────────────

SET FOREIGN_KEY_CHECKS = 0;

-- Data for table `users` --
INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `status`, `created_at`) VALUES
(1, 'Praise', 'p_admin@gmail', '$2y$10$lVwOXNrIciinnbnqU6GmleGol6H8eK64hEW8bN0iSnLogEBTC5j4G', 'admin', 'active', '2026-05-03 22:52:00'),
(2, 'Tom Gupta', 'gupta_construct@gmail.com', '$2y$10$8xB45tjBlYIDt6X4Ng6Uguq3hDNKMDVyDM7Zcu2VOGxq3ufE5zXCy', 'contractor', 'suspended', '2026-05-03 23:01:19'),
(3, 'John Dube', 'inyatsi_construct@gmail.com', '$2y$10$BzmSeJSgWFOJN.zFihfJfOhLoZMgWTEqUByxIdHzlcXHZSK9omvxm', 'contractor', 'suspended', '2026-05-03 23:12:54'),
(4, 'shle_eval', 'shle_eval@gmail.com', '$2y$10$h5UIjd9kXlAaPe85NBj8VukZD.z3.Adpx03PNwtMiQOI7EUBiuW3y', 'evaluator', 'active', '2026-05-04 08:11:12'),
(5, 'bob', 'B@gmail.com', '$2y$10$KBEGFLKKWpLPcXXYVLKMTenuwYMh9IDMqYzx/Md6wLwDJF8XFwXv6', 'contractor', 'active', '2026-05-05 15:45:09'),
(6, 'zak', 'zak_eval@gmail.com', '$2y$10$aA1/3TYxQAKgxQ5IhTT0dedu1vI.E4WGOKusTy.D3VLTEpGxPEpmu', 'evaluator', 'active', '2026-05-08 10:53:15'),
(7, 'tesh', 'tesh@gmail.com', '$2y$10$fJm8DKS2H4oTcPk7jGTbrORXdrfLJnueSioExfhhum.EGsVGMPdLq', 'contractor', 'active', '2026-05-17 23:21:40');

-- Data for table `admin_profiles` --
INSERT INTO `admin_profiles` (`admin_profile_id`, `user_id`, `full_name`, `government_id`, `authorization_reference`, `office_phone`, `approval_status`, `reviewed_by`, `reviewed_at`, `created_at`) VALUES
(1, 1, 'Praise nzima', 'gov_001', 'MICROPROJECTS ESWATINI', 34021010, 'pending', NULL, NULL, '2026-05-03 22:52:00');

-- Data for table `evaluator_profiles` --
INSERT INTO `evaluator_profiles` (`evaluator_profile_id`, `user_id`, `evaluator_code`, `full_name`, `accreditation_number`, `specialization`, `institution`, `experience_summary`, `approval_status`, `rejection_reason`, `reviewed_by`, `reviewed_at`, `created_at`) VALUES
(1, 4, 'EVAL-09DB7B', 'Shle Nzima', 4422, 'construction', 'SPPRA', '* 15 years of experience in the tender evaluation for the Government of Eswatini\r\n* 4 years as a evaluator for SPPRA', 'approved', NULL, 1, '2026-05-04 08:15:52', '2026-05-04 08:11:12'),
(2, 6, 'EVAL-987219', 'Zak Jones', 'ACC-001', 'CONSTRUCTION', 'SPPRA', '15 YEARS EXPERIENCE IN THE EVALUATION FIELD', 'approved', NULL, 1, '2026-05-08 10:54:32', '2026-05-08 10:53:15');

-- Data for table `contractors` --
INSERT INTO `contractors` (`contractor_id`, `user_id`, `company_name`, `cic_grade`, `category`, `verification_status`, `rejection_reason`, `tax_pin`, `form_j_number`, `local_ownership`, `profile_summary`, `blacklisted`, `blacklist_reason`, `blacklisted_at`, `verified_at`, `created_at`) VALUES
(1, 2, 'GuptaFamily construction', 'C3', NULL, '', 'Phase 1 documents expired - account suspended', 'tax_001', 'J_001', 1, NULL, 0, NULL, NULL, '2026-05-03 23:05:05', '2026-05-03 23:01:19'),
(2, 3, 'Inyatsi construction', 'A1', NULL, '', 'Phase 1 documents expired - account suspended', 'tax_002', 'j_002', 1, NULL, 0, NULL, NULL, '2026-05-03 23:14:13', '2026-05-03 23:12:54'),
(3, 5, 'bob', 'B4', NULL, 'approved', NULL, 'tax 03', 'J 03', 1, NULL, 0, NULL, NULL, '2026-05-05 15:45:44', '2026-05-05 15:45:09'),
(4, 7, 'Tesh construction', 'C3', 'Health Facilities', 'approved', NULL, 'TAX_004', 'J_004', 1, NULL, 0, NULL, NULL, '2026-05-17 23:22:29', '2026-05-17 23:21:40');

-- Data for table `tenders` --
INSERT INTO `tenders` (`tender_id`, `title`, `title_si`, `description`, `description_si`, `budget`, `required_grade`, `category`, `region`, `deadline`, `evaluation_deadline`, `consensus_meeting_link`, `consensus_meeting_scheduled_at`, `status`, `version`, `created_by`, `published_at`, `created_at`) VALUES
(1, 'Construction of community hall', 'Kwakha likhaya lemphakatsi', 'SCOPE:\r\n* This tender is eligible for small and medium enterprises.\r\n* The tenderpreneurs are expected to provide cite visitation/inspection certificate.\r\n*No late submission will be entertained.\r\n* All required documents are expected to be submitted on time.', 'Lonkhe letilungelo Tihlangano temcimbili. Luhlelo lolusho lusabekelwa eMphakatsini lusayina emacondzisweni weCatalogue of Requirements.', '10000.00', 'Grade 1', 'Construction', 'Hhohho', '2026-06-02 23:40:39', '2026-05-04 00:00:00', NULL, NULL, 'published', 1, 1, '2026-05-03 23:35:30', '2026-05-03 23:35:30'),
(2, 'Construction of classrooms at Mhlume primary', 'kwakhiwa kwemagumbi ekufundzela eskolweni lesincane sase Mhlume', 'Scope: \r\n&gt;The project is expected to take a period of 3 months\r\n&gt; the constructor is expected to take pictures as proof for progress report throughout the whole project', '&gt; kugadzeke kwekutsi lokwakhiwa kwalamagumbi kutsi kutsatse 3 wetinyanga\r\n&gt; kugadzeke kwekutsi umakhi kutsi atfumele titfombe lokutawba sifakazelo sekutsi sebasebente kanganani', '400000.00', 'Grade 2', 'Construction', 'Hhohho', '2026-05-08 09:45:00', '2026-05-22 00:00:00', NULL, NULL, 'closed', 1, 1, '2026-05-08 09:47:17', '2026-05-08 09:47:17'),
(3, 'construction of community hall at Madulini', 'kwakhiwa kwelohhola lemmango lase Madulini', '&gt;construction is expected to take about 2 months', '&gt;kwakhiwa kwalelihhola kugadzeke kutsi kutsatse 2 wetinyanya', '500000.00', 'Grade 3', 'Construction', 'Hhohho', '2026-05-08 09:52:00', '2026-05-28 00:00:00', NULL, NULL, 'closed', 1, 1, '2026-05-08 09:52:51', '2026-05-08 09:52:51'),
(4, 'construction of Ndunazithini clinic', 'kwakhiwa kweClinic yakaNdunazithini', '* Creation of clinic at the area of Ndunazitthini\r\n* It should take about 3-5 months\r\n* They should provide videos and pictures as proof of work', '*kwakhiwa kweClinic yakandunazithini\r\n* kugadzakele kutsi lomsebenti utsatse 3-5 wetintanya\r\n* tinkampani tigadzakele kutsi titfumele emaVideo netintfombe lokutoba sifakazelo semsebenti', '1200000.00', 'Grade 3', 'Construction', 'Hhohho', '2026-05-23 11:00:00', '2026-05-25 00:00:00', NULL, NULL, 'published', 1, 1, '2026-05-08 11:52:04', '2026-05-08 11:52:04');

-- Data for table `tender_rubrics` --
INSERT INTO `tender_rubrics` (`rubric_id`, `tender_id`, `technical_weight`, `financial_weight`, `local_weight`, `is_public`, `created_at`) VALUES
(1, 1, '50.00', '40.00', '10.00', 1, '2026-05-03 23:35:30'),
(2, 2, '60.00', '28.00', '12.00', 1, '2026-05-08 09:47:17'),
(3, 3, '50.00', '40.00', '10.00', 1, '2026-05-08 09:52:51'),
(4, 4, '50.00', '40.00', '10.00', 1, '2026-05-08 11:52:04');

-- Data for table `contractor_documents` --
INSERT INTO `contractor_documents` (`document_id`, `contractor_id`, `document_name`, `document_path`, `uploaded_at`, `expiry_date`, `document_phase`, `is_expired`) VALUES
(1, 1, 'Company Profile', 'documents/contractor_1_company_profile_0_1777842079.pdf', '2026-05-03 23:01:19', NULL, 'phase1', 0),
(2, 1, 'Tax Compliance Certificate', 'documents/contractor_1_tax_compliance_0_1777842079.pdf', '2026-05-03 23:01:19', '2026-06-02', 'phase2', 0),
(3, 1, 'Trading License', 'documents/contractor_1_trading_license_0_1777842079.pdf', '2026-05-03 23:01:19', NULL, 'phase1', 0),
(4, 1, 'ENPF Compliance Certificate', 'documents/contractor_1_enpf_certificate_0_1777842079.pdf', '2026-05-03 23:01:19', '2026-06-02', 'phase2', 0),
(5, 1, 'Certificate of Incorporation', 'documents/contractor_1_cert_incorporation_0_1777842079.pdf', '2026-05-03 23:01:19', NULL, 'phase1', 0),
(6, 1, 'Form J &amp; Form C (Part 1)', 'documents/contractor_1_form_j_c_0_1777842079.pdf', '2026-05-03 23:01:19', NULL, 'phase1', 0),
(7, 1, 'Form J &amp; Form C (Part 2)', 'documents/contractor_1_form_j_c_1_1777842079.pdf', '2026-05-03 23:01:19', NULL, 'phase1', 0),
(8, 1, 'Labour Compliance Certificate', 'documents/contractor_1_labour_compliance_0_1777842079.pdf', '2026-05-03 23:01:19', '2026-06-02', 'phase2', 0),
(9, 2, 'Company Profile', 'documents/contractor_2_company_profile_0_1777842774.pdf', '2026-05-03 23:12:54', NULL, 'phase1', 0),
(10, 2, 'Tax Compliance Certificate', 'documents/contractor_2_tax_compliance_0_1777842774.pdf', '2026-05-03 23:12:54', '2026-06-02', 'phase2', 0),
(11, 2, 'Trading License', 'documents/contractor_2_trading_license_0_1777842774.pdf', '2026-05-03 23:12:54', NULL, 'phase1', 0),
(12, 2, 'ENPF Compliance Certificate', 'documents/contractor_2_enpf_certificate_0_1777842774.pdf', '2026-05-03 23:12:54', '2026-06-02', 'phase2', 0),
(13, 2, 'Certificate of Incorporation', 'documents/contractor_2_cert_incorporation_0_1777842774.pdf', '2026-05-03 23:12:54', NULL, 'phase1', 0),
(14, 2, 'Form J &amp; Form C (Part 1)', 'documents/contractor_2_form_j_c_0_1777842774.pdf', '2026-05-03 23:12:54', NULL, 'phase1', 0),
(15, 2, 'Form J &amp; Form C (Part 2)', 'documents/contractor_2_form_j_c_1_1777842774.pdf', '2026-05-03 23:12:54', NULL, 'phase1', 0),
(16, 2, 'Labour Compliance Certificate', 'documents/contractor_2_labour_compliance_0_1777842774.pdf', '2026-05-03 23:12:54', '2026-06-02', 'phase2', 0),
(17, 3, 'Certificate of Incorporation', 'documents/contractor_3_cert_incorporation_0_1777988709.pdf', '2026-05-05 15:45:09', NULL, 'phase1', 0),
(18, 3, 'Form J &amp; Form C', 'documents/contractor_3_form_j_c_0_1777988709.pdf', '2026-05-05 15:45:09', NULL, 'phase1', 0),
(19, 3, 'CIC Registration Certificate', 'documents/contractor_3_cic_registration_0_1777988709.pdf', '2026-05-05 15:45:09', NULL, 'phase1', 0),
(20, 3, 'VAT Registration Certificate', 'documents/contractor_3_vat_registration_0_1777988709.pdf', '2026-05-05 15:45:09', NULL, 'phase1', 0),
(21, 3, 'Trading Licence', 'documents/contractor_3_trading_licence_0_1777988709.pdf', '2026-05-05 15:45:09', NULL, 'phase1', 0),
(22, 3, 'Tax Clearance Certificate', 'contractor_3_tax_clearance_certificate_1778228566.pdf', '2026-05-08 10:22:46', '2026-06-07', 'phase2', 0),
(23, 3, 'SNPF/ENPF Compliance', 'contractor_3_snpf_enpf_compliance_1778228597.pdf', '2026-05-08 10:23:17', '2026-08-06', 'phase2', 0),
(24, 3, 'Police Clearance', 'contractor_3_police_clearance_1778228635.pdf', '2026-05-08 10:23:55', '2026-08-06', 'phase2', 0),
(25, 3, 'Labour Compliance Certificate', 'contractor_3_labour_compliance_certificate_1778228672.pdf', '2026-05-08 10:24:32', NULL, 'phase2', 0),
(26, 4, 'Certificate of Incorporation', 'documents/contractor_4_cert_incorporation_0_1779052900.pdf', '2026-05-17 23:21:40', NULL, 'phase1', 0),
(27, 4, 'Form J &amp; Form C (Part 1)', 'documents/contractor_4_form_j_c_0_1779052900.pdf', '2026-05-17 23:21:40', NULL, 'phase1', 0),
(28, 4, 'Form J &amp; Form C (Part 2)', 'documents/contractor_4_form_j_c_1_1779052900.pdf', '2026-05-17 23:21:40', NULL, 'phase1', 0),
(29, 4, 'CIC Registration Certificate', 'documents/contractor_4_cic_registration_0_1779052900.pdf', '2026-05-17 23:21:40', NULL, 'phase1', 0),
(30, 4, 'VAT Registration Certificate', 'documents/contractor_4_vat_registration_0_1779052900.pdf', '2026-05-17 23:21:40', NULL, 'phase1', 0),
(31, 4, 'Trading Licence', 'documents/contractor_4_trading_licence_0_1779052900.pdf', '2026-05-17 23:21:40', NULL, 'phase1', 0),
(32, 4, 'Tax Clearance Certificate', 'documents/contractor_4_tax_clearance_0_1779052900.pdf', '2026-05-17 23:21:40', '2026-06-16', 'phase2', 0),
(33, 4, 'SNPF/ENPF Compliance', 'documents/contractor_4_enpf_compliance_0_1779052900.pdf', '2026-05-17 23:21:40', '2026-08-15', 'phase2', 0),
(34, 4, 'Police Clearance', 'documents/contractor_4_police_clearance_0_1779052900.pdf', '2026-05-17 23:21:40', '2026-08-15', 'phase2', 0),
(35, 4, 'Labour Compliance Certificate', 'documents/contractor_4_labour_compliance_0_1779052900.pdf', '2026-05-17 23:21:40', NULL, 'phase2', 0),
(36, 4, 'Tax Clearance Certificate', 'contractor_4_tax_clearance_certificate_1779053895.pdf', '2026-05-17 23:38:15', '2026-06-16', 'phase2', 0),
(37, 4, 'Form J &amp; Form C', 'contractor_4_form_j___form_c_0_1779054499.pdf', '2026-05-17 23:48:19', NULL, 'phase1', 0),
(38, 4, 'Form J &amp; Form C', 'contractor_4_form_j___form_c_1_1779054499.pdf', '2026-05-17 23:48:19', NULL, 'phase1', 0),
(39, 3, 'Certificate of Incorporation', 'contractor_3_certificate_of_incorporation_0_1779219699.pdf', '2026-05-19 21:41:39', NULL, 'phase1', 0),
(40, 3, 'Form J &amp; Form C', 'contractor_3_form_j___form_c_0_1779219762.pdf', '2026-05-19 21:42:43', NULL, 'phase1', 0);

-- Data for table `contractor_portfolio` --
INSERT INTO `contractor_portfolio` (`project_id`, `contractor_id`, `project_name`, `client_name`, `project_value`, `completion_year`, `description`, `created_at`) VALUES
(1, 2, 'Construction of classrooms as Sihhoye Primarry school', 'Microprojects', '80000.00', 2025, '', '2026-05-03 23:59:29'),
(2, 3, 'Mbhadlana Road', 'Government of Eswatini', '10000000.00', 2024, 'Construction of Mbhadlana road', '2026-05-08 11:33:09'),
(3, 3, 'water pump construction', 'Microprojects', '2000000.00', 2022, 'water pump for the community of Ezindwendweni who have been struggling with a clean water  supply for the past 25 years', '2026-05-08 12:21:28');

-- Data for table `portfolio_media` --
INSERT INTO `portfolio_media` (`media_id`, `project_id`, `file_path`, `file_type`, `created_at`) VALUES
(1, 1, 'uploads/portfolio/portfolio_1_0_a147931f.jpg', 'image/jpeg', '2026-05-03 23:59:29'),
(2, 2, 'uploads/portfolio/portfolio_2_0_39b1a2b5.jpg', 'image/jpeg', '2026-05-08 11:33:09'),
(3, 3, 'uploads/portfolio/portfolio_3_0_655b1110.jpg', 'image/jpeg', '2026-05-08 12:21:28');

-- Sample data: blacklisted contractor, active bids, and pending appeals.
-- Ensure the following are valid for the sample records to display:
--   1) `contractors.blacklisted = 1` for blacklist count
--   2) `appeals.status = 'submitted'` for pending appeal count
--   3) `tender_id`, `user_id`, and `bid_id` reference existing rows
INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `status`, `created_at`) VALUES
(8, 'Malicious Co', 'malicious@example.com', '$2y$10$8xB45tjBlYIDt6X4Ng6Uguq3hDNKMDVyDM7Zcu2VOGxq3ufE5zXCy', 'contractor', 'active', '2026-06-11 08:00:00');

INSERT INTO `contractors` (`contractor_id`, `user_id`, `company_name`, `cic_grade`, `category`, `verification_status`, `rejection_reason`, `tax_pin`, `form_j_number`, `local_ownership`, `profile_summary`, `blacklisted`, `blacklist_reason`, `blacklisted_at`, `verified_at`, `created_at`) VALUES
(5, 8, 'Malicious Contractors Ltd', 'D2', 'Construction, Electrical', 'approved', NULL, 'TAX-999', '', 0, 'Blacklisted due to repeated contract breaches and failure to provide required progress reports.', 1, 'Multiple contract breaches and failure to submit progress reports.', '2026-06-11 08:10:00', '2026-06-11 08:10:00', '2026-06-11 08:00:00');

INSERT INTO `bids` (`bid_id`, `tender_id`, `contractor_id`, `encrypted_amount`, `submitted_at`, `locked`, `eligibility_confirmed`, `conflict_declared`, `form_of_tender_accepted`) VALUES
(1, 1, 3, 'ENC(150000.00)', '2026-06-10 14:00:00', 0, 1, 0, 1),
(2, 4, 4, 'ENC(1150000.00)', '2026-06-10 14:30:00', 0, 1, 0, 1);

INSERT INTO `appeals` (`appeal_id`, `tender_id`, `user_id`, `bid_id`, `subject`, `appeal_reason`, `status`, `admin_response`, `responded_by`, `responded_at`, `created_at`) VALUES
(1, 1, 3, 1, 'Clarification on evaluation', 'The evaluation report appears inconsistent with the tender requirements; please review the local content scoring.', 'submitted', NULL, NULL, NULL, '2026-06-11 06:30:00'),
(2, 4, 7, 2, 'Request for reconsideration', 'Our bid included the correct site inspection certificate but it was not marked in the evaluation panel.', 'submitted', NULL, NULL, NULL, '2026-06-11 06:45:00');


-- Data for table `audit_logs` --
INSERT INTO `audit_logs` (`audit_id`, `actor_user_id`, `action_type`, `target_table`, `target_id`, `details`, `created_at`, `immutable_flag`) VALUES
(1, 1, 'register', 'users', 1, 'Administrator registered', '2026-05-03 22:52:00', 1),
(2, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-03 22:52:39', 1),
(3, 1, 'logout', 'users', 1, 'User logged out', '2026-05-03 22:53:39', 1),
(4, 2, 'register_api', 'users', 2, 'New contractor registered via API', '2026-05-03 23:01:19', 1),
(5, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-03 23:01:41', 1),
(6, 1, 'review_applicant', 'contractor_profiles', 1, 'Contractor application approved', '2026-05-03 23:05:05', 1),
(7, 1, 'logout', 'users', 1, 'User logged out', '2026-05-03 23:05:38', 1),
(8, 3, 'register_api', 'users', 3, 'New contractor registered via API', '2026-05-03 23:12:54', 1),
(9, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-03 23:14:08', 1),
(10, 1, 'review_applicant', 'contractor_profiles', 2, 'Contractor application approved', '2026-05-03 23:14:13', 1),
(11, 1, 'logout', 'users', 1, 'User logged out', '2026-05-03 23:14:29', 1),
(12, 3, 'login_api', 'users', 3, 'User logged in via API', '2026-05-03 23:15:37', 1),
(13, 3, 'logout', 'users', 3, 'User logged out', '2026-05-03 23:17:38', 1),
(14, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-03 23:18:32', 1),
(15, 1, 'publish_tender', 'tenders', 1, 'Tender published with rubric weights', '2026-05-03 23:35:30', 1),
(16, 1, 'logout', 'users', 1, 'User logged out', '2026-05-03 23:36:29', 1),
(17, 3, 'login_api', 'users', 3, 'User logged in via API', '2026-05-03 23:47:39', 1),
(18, 3, 'logout', 'users', 3, 'User logged out', '2026-05-03 23:59:50', 1),
(19, 4, 'register', 'users', 4, 'Evaluator registered', '2026-05-04 08:11:12', 1),
(20, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-04 08:11:49', 1),
(21, 1, 'review_applicant', 'evaluator_profiles', 1, 'Evaluator application approved', '2026-05-04 08:15:52', 1),
(22, 1, 'logout', 'users', 1, 'User logged out', '2026-05-04 08:16:23', 1),
(23, 4, 'login_api', 'users', 4, 'User logged in via API', '2026-05-04 08:17:04', 1),
(24, 4, 'logout', 'users', 4, 'User logged out', '2026-05-04 08:21:55', 1),
(25, 3, 'login_api', 'users', 3, 'User logged in via API', '2026-05-04 09:19:39', 1),
(26, 3, 'logout', 'users', 3, 'User logged out', '2026-05-04 10:24:30', 1),
(27, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-04 12:32:23', 1),
(28, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-05 11:30:22', 1),
(29, 1, 'logout', 'users', 1, 'User logged out', '2026-05-05 11:31:20', 1),
(30, 4, 'login_api', 'users', 4, 'User logged in via API', '2026-05-05 11:32:36', 1),
(31, 4, 'logout', 'users', 4, 'User logged out', '2026-05-05 15:09:32', 1),
(32, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-05 15:20:24', 1),
(33, 1, 'logout', 'users', 1, 'User logged out', '2026-05-05 15:21:09', 1),
(34, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-05 15:30:33', 1),
(35, 5, 'register_api', 'users', 5, 'New contractor registered via API', '2026-05-05 15:45:09', 1),
(36, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-05 15:45:36', 1),
(37, 1, 'review_applicant', 'contractor_profiles', 3, 'Contractor application approved', '2026-05-05 15:45:44', 1),
(38, 1, 'logout', 'users', 1, 'User logged out', '2026-05-05 15:46:16', 1),
(39, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-05 15:46:34', 1),
(40, 5, 'logout', 'users', 5, 'User logged out', '2026-05-05 15:47:34', 1),
(41, 4, 'login_api', 'users', 4, 'User logged in via API', '2026-05-05 15:49:13', 1),
(42, 4, 'logout', 'users', 4, 'User logged out', '2026-05-05 16:05:44', 1),
(43, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-05 16:08:09', 1),
(44, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-05 16:09:50', 1),
(45, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-07 10:45:52', 1),
(46, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-07 11:54:07', 1),
(47, 1, 'logout', 'users', 1, 'User logged out', '2026-05-07 11:56:03', 1),
(48, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-07 11:57:10', 1),
(49, 5, 'logout', 'users', 5, 'User logged out', '2026-05-07 11:57:50', 1),
(50, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-07 15:52:54', 1),
(51, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-08 09:33:35', 1),
(52, 1, 'publish_tender', 'tenders', 2, 'Tender published with rubric weights', '2026-05-08 09:47:17', 1),
(53, 1, 'publish_tender', 'tenders', 3, 'Tender published with rubric weights', '2026-05-08 09:52:51', 1),
(54, 1, 'logout', 'users', 1, 'User logged out', '2026-05-08 09:53:21', 1),
(55, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-08 10:00:37', 1),
(56, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-08 10:16:00', 1),
(57, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-08 10:21:51', 1),
(58, 5, 'upload_document', 'contractor_documents', 3, 'Document uploaded: Tax Clearance Certificate', '2026-05-08 10:22:46', 1),
(59, 5, 'upload_document', 'contractor_documents', 3, 'Document uploaded: SNPF/ENPF Compliance', '2026-05-08 10:23:17', 1),
(60, 5, 'upload_document', 'contractor_documents', 3, 'Document uploaded: Police Clearance', '2026-05-08 10:23:55', 1),
(61, 5, 'upload_document', 'contractor_documents', 3, 'Document uploaded: Labour Compliance Certificate', '2026-05-08 10:24:32', 1),
(62, 5, 'logout', 'users', 5, 'User logged out', '2026-05-08 10:30:03', 1),
(63, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-08 10:30:38', 1),
(64, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-08 10:43:49', 1),
(65, 1, 'logout', 'users', 1, 'User logged out', '2026-05-08 10:46:32', 1),
(66, 6, 'register', 'users', 6, 'Evaluator registered', '2026-05-08 10:53:15', 1),
(67, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-08 10:54:16', 1),
(68, 1, 'review_applicant', 'evaluator_profiles', 2, 'Evaluator application approved', '2026-05-08 10:54:32', 1),
(69, 1, 'logout', 'users', 1, 'User logged out', '2026-05-08 10:54:57', 1),
(70, 6, 'login_api', 'users', 6, 'User logged in via API', '2026-05-08 10:55:27', 1),
(71, 6, 'logout', 'users', 6, 'User logged out', '2026-05-08 11:19:01', 1),
(72, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-08 11:28:52', 1),
(73, 5, 'logout', 'users', 5, 'User logged out', '2026-05-08 11:35:16', 1),
(74, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-08 11:35:50', 1),
(75, 1, 'publish_tender', 'tenders', 4, 'Tender published with rubric weights', '2026-05-08 11:52:04', 1),
(76, 1, 'logout', 'users', 1, 'User logged out', '2026-05-08 11:56:45', 1),
(77, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-08 12:17:19', 1),
(78, 5, 'logout', 'users', 5, 'User logged out', '2026-05-08 12:26:38', 1),
(79, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-08 12:27:12', 1),
(80, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-08 12:40:49', 1),
(81, 5, 'logout', 'users', 5, 'User logged out', '2026-05-08 12:43:14', 1),
(82, 6, 'login_api', 'users', 6, 'User logged in via API', '2026-05-08 12:43:36', 1),
(83, 6, 'logout', 'users', 6, 'User logged out', '2026-05-08 12:44:00', 1),
(84, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-08 12:48:11', 1),
(85, 5, 'logout', 'users', 5, 'User logged out', '2026-05-08 12:50:55', 1),
(86, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-08 12:53:25', 1),
(87, 1, 'logout', 'users', 1, 'User logged out', '2026-05-08 13:07:55', 1),
(88, 6, 'login_api', 'users', 6, 'User logged in via API', '2026-05-08 13:08:31', 1),
(89, 6, 'logout', 'users', 6, 'User logged out', '2026-05-08 13:09:48', 1),
(90, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-10 04:06:44', 1),
(91, 5, 'logout', 'users', 5, 'User logged out', '2026-05-10 04:47:10', 1),
(92, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-12 00:50:30', 1),
(93, 5, 'logout', 'users', 5, 'User logged out', '2026-05-12 01:34:11', 1),
(94, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-12 01:34:48', 1),
(95, 1, 'logout', 'users', 1, 'User logged out', '2026-05-12 01:36:26', 1),
(96, 6, 'login_api', 'users', 6, 'User logged in via API', '2026-05-12 01:37:25', 1),
(97, 6, 'logout', 'users', 6, 'User logged out', '2026-05-12 01:37:58', 1),
(98, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-14 01:37:27', 1),
(99, 5, 'logout', 'users', 5, 'User logged out', '2026-05-14 01:51:52', 1),
(100, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-14 01:52:29', 1);
INSERT INTO `audit_logs` (`audit_id`, `actor_user_id`, `action_type`, `target_table`, `target_id`, `details`, `created_at`, `immutable_flag`) VALUES
(101, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-15 09:03:32', 1),
(102, 5, 'logout', 'users', 5, 'User logged out', '2026-05-15 09:04:41', 1),
(103, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-15 09:47:19', 1),
(104, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-17 21:59:00', 1),
(105, 1, 'logout', 'users', 1, 'User logged out', '2026-05-17 22:01:18', 1),
(106, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-17 22:23:43', 1),
(107, 5, 'logout', 'users', 5, 'User logged out', '2026-05-17 22:44:53', 1),
(108, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-17 22:45:36', 1),
(109, 1, 'logout', 'users', 1, 'User logged out', '2026-05-17 22:45:58', 1),
(110, 7, 'register_api', 'users', 7, 'New contractor registered via API', '2026-05-17 23:21:40', 1),
(111, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-17 23:22:12', 1),
(112, 1, 'review_applicant', 'contractor_profiles', 4, 'Contractor application approved', '2026-05-17 23:22:29', 1),
(113, 1, 'logout', 'users', 1, 'User logged out', '2026-05-17 23:35:11', 1),
(114, 7, 'login_api', 'users', 7, 'User logged in via API', '2026-05-17 23:35:24', 1),
(115, 7, 'upload_document', 'contractor_documents', 4, 'Document uploaded: Tax Clearance Certificate', '2026-05-17 23:38:15', 1),
(116, 7, 'upload_document', 'contractor_documents', 4, 'Document uploaded: Form J &amp; Form C', '2026-05-17 23:48:19', 1),
(117, 7, 'upload_document', 'contractor_documents', 4, 'Document uploaded: Form J &amp; Form C', '2026-05-17 23:48:19', 1),
(118, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-19 19:27:13', 1),
(119, 5, 'logout', 'users', 5, 'User logged out', '2026-05-19 20:56:32', 1),
(120, 1, 'login_api', 'users', 1, 'User logged in via API', '2026-05-19 20:56:52', 1),
(121, 5, 'login_api', 'users', 5, 'User logged in via API', '2026-05-19 21:34:09', 1),
(122, 5, 'upload_document', 'contractor_documents', 3, 'Document uploaded: Certificate of Incorporation', '2026-05-19 21:41:39', 1),
(123, 5, 'upload_document', 'contractor_documents', 3, 'Document uploaded: Form J &amp; Form C', '2026-05-19 21:42:43', 1);

-- Data for table `notifications` --
INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `channel`, `sent_at`, `read_at`, `created_at`) VALUES
(1, 2, 'Your application has been approved. You may now access your account.', 'in-app', NULL, NULL, '2026-05-03 23:05:05'),
(2, 3, 'Your application has been approved. You may now access your account.', 'in-app', NULL, '2026-05-03 23:17:17', '2026-05-03 23:14:13'),
(3, 2, 'New tender &quot;Construction of community hall&quot; has been published. Check the tender details and upload required documents before bidding.', 'in-app', NULL, NULL, '2026-05-03 23:35:30'),
(4, 3, 'New tender &quot;Construction of community hall&quot; has been published. Check the tender details and upload required documents before bidding.', 'in-app', NULL, '2026-05-04 10:24:10', '2026-05-03 23:35:30'),
(5, 4, 'Your application has been approved. You may now access your account.', 'in-app', NULL, '2026-05-04 08:21:32', '2026-05-04 08:15:52'),
(6, 5, 'Your application has been approved. You may now access your account.', 'in-app', NULL, '2026-05-05 15:47:22', '2026-05-05 15:45:44'),
(7, 5, 'New tender &quot;Construction of classrooms at Mhlume primary&quot; has been published. Check the tender details and upload required documents before bidding.', 'in-app', NULL, '2026-05-08 11:35:10', '2026-05-08 09:47:17'),
(8, 5, 'New tender &quot;construction of community hall at Madulini&quot; has been published. Check the tender details and upload required documents before bidding.', 'in-app', NULL, '2026-05-08 11:35:10', '2026-05-08 09:52:51'),
(9, 6, 'Your application has been approved. You may now access your account.', 'in-app', NULL, '2026-05-12 01:37:35', '2026-05-08 10:54:32'),
(10, 5, 'New tender &quot;construction of Ndunazithini clinic&quot; has been published. Check the tender details and upload required documents before bidding.', 'in-app', NULL, '2026-05-10 04:47:03', '2026-05-08 11:52:04'),
(11, 7, 'Your application has been approved. You may now access your account.', 'in-app', NULL, '2026-05-17 23:36:12', '2026-05-17 23:22:29');

SET FOREIGN_KEY_CHECKS = 1;
-- Migration script for Committee Role and Post-Award Features

-- 1. Add 'committee' to users role ENUM
ALTER TABLE users MODIFY COLUMN role ENUM('admin','evaluator','contractor','committee') NOT NULL DEFAULT 'contractor';

-- 2. Create committee_profiles
CREATE TABLE IF NOT EXISTS committee_profiles (
    committee_profile_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    accreditation_number VARCHAR(120) NOT NULL,
    specialization VARCHAR(120) NOT NULL,
    department VARCHAR(150) NOT NULL,
    approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    reviewed_by INT UNSIGNED DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create award_recommendations
CREATE TABLE IF NOT EXISTS award_recommendations (
    recommendation_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id INT UNSIGNED NOT NULL UNIQUE,
    bid_id INT UNSIGNED NOT NULL,
    committee_user_id INT UNSIGNED NOT NULL,
    justification TEXT NOT NULL,
    status ENUM('pending_publish','published','rejected') NOT NULL DEFAULT 'pending_publish',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tender_id) REFERENCES tenders(tender_id) ON DELETE CASCADE,
    FOREIGN KEY (bid_id) REFERENCES bids(bid_id) ON DELETE CASCADE,
    FOREIGN KEY (committee_user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Create contracts table
CREATE TABLE IF NOT EXISTS contracts (
    contract_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_id INT UNSIGNED NOT NULL UNIQUE,
    contractor_id INT UNSIGNED NOT NULL,
    contract_amount DECIMAL(15,2) NOT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    status ENUM('draft','signed','active','completed','terminated') NOT NULL DEFAULT 'draft',
    signed_by_contractor TINYINT(1) NOT NULL DEFAULT 0,
    signed_by_admin TINYINT(1) NOT NULL DEFAULT 0,
    contract_document_path VARCHAR(400) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    FOREIGN KEY (tender_id) REFERENCES tenders(tender_id) ON DELETE CASCADE,
    FOREIGN KEY (contractor_id) REFERENCES contractors(contractor_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Create project_implementation_updates table
CREATE TABLE IF NOT EXISTS project_implementation_updates (
    update_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contract_id INT UNSIGNED NOT NULL,
    contractor_id INT UNSIGNED NOT NULL,
    milestone_title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    completion_percentage INT NOT NULL DEFAULT 0,
    status ENUM('submitted','approved','rejected') NOT NULL DEFAULT 'submitted',
    reviewed_by INT UNSIGNED DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_id) REFERENCES contracts(contract_id) ON DELETE CASCADE,
    FOREIGN KEY (contractor_id) REFERENCES contractors(contractor_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Add evaluation comments column to evaluations if it doesn't exist, or we can use the evaluation_comments table if it exists.
-- The schema shows evaluation_comments might exist, let's just make sure.

-- Seed data for testing committee member
INSERT INTO users (username, email, password_hash, role, status) VALUES 
('committee1', 'committee1@gmail.com', '$2y$10$lVwOXNrIciinnbnqU6GmleGol6H8eK64hEW8bN0iSnLogEBTC5j4G', 'committee', 'active');
-- $2y$10$lVwOXNrIciinnbnqU6GmleGol6H8eK64hEW8bN0iSnLogEBTC5j4G is the hash for 'password' (same as admin)

INSERT INTO committee_profiles (user_id, full_name, accreditation_number, specialization, department, approval_status) 
VALUES (LAST_INSERT_ID(), 'Jane Doe (Committee)', 'COM-001', 'Procurement', 'Finance', 'approved');

