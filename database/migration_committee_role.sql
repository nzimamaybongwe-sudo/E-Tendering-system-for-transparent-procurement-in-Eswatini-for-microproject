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

