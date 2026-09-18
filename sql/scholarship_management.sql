-- IS1207 Group 2: Online Scholarship Management System
-- Import this complete file through phpMyAdmin.

CREATE DATABASE IF NOT EXISTS scholarship_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE scholarship_management;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS scholarships;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(25) NULL,
    faculty VARCHAR(100) NULL,
    year_of_study VARCHAR(30) NULL,
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE scholarships (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    provider VARCHAR(160) NOT NULL,
    description TEXT NOT NULL,
    eligibility TEXT NOT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    deadline DATE NOT NULL,
    status ENUM('Open', 'Closed') NOT NULL DEFAULT 'Open',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_scholarship_creator FOREIGN KEY (created_by)
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    scholarship_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    gpa DECIMAL(3,2) NOT NULL,
    household_income DECIMAL(12,2) NOT NULL,
    personal_statement TEXT NOT NULL,
    document_path VARCHAR(255) NULL,
    status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    admin_comment TEXT NULL,
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    CONSTRAINT uq_one_application UNIQUE (scholarship_id, user_id),
    CONSTRAINT fk_application_scholarship FOREIGN KEY (scholarship_id)
        REFERENCES scholarships(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_application_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Required ordinary user: username=ucsc, password=ucsc
-- Demonstration administrator: username=admin, password=admin123
INSERT INTO users (username, password, full_name, email, role) VALUES
('ucsc', '$2y$12$Tq8nKMSTGOAdFIjt1O/4auVcQCPYhWswHDyM7s.lY4zz4nfLG3QA.', 'Default UCSC User', 'ucsc@example.local', 'student'),
('admin', '$2y$12$DMgc12.gyrnesBVFylQ2u.0KHVBWFZpO4BosuNwCa5kv7vJg3JPVy', 'System Administrator', 'admin@example.local', 'admin');

INSERT INTO scholarships (title, provider, description, eligibility, amount, deadline, status, created_by) VALUES
('Undergraduate Academic Excellence Scholarship', 'University Scholarship Office', 'Supports undergraduate students who demonstrate strong academic performance and active participation in university activities.', 'Current undergraduate student; minimum GPA 3.20; satisfactory attendance; no disciplinary action.', 100000.00, '2026-12-15', 'Open', 2),
('Student Financial Assistance Grant', 'Student Welfare Division', 'Provides financial assistance to eligible students from low-income households.', 'Registered student; documented financial need; annual household income below the published limit.', 75000.00, '2026-11-30', 'Open', 2),
('Technology Innovation Award', 'Faculty Innovation Fund', 'Recognizes students developing practical technology solutions for university or community problems.', 'Submit a project summary; demonstrate originality, usefulness and feasibility.', 150000.00, '2026-10-20', 'Open', 2);

-- Useful SQL queries to include and explain in the final report:
-- 1. SELECT * FROM users ORDER BY created_at DESC;
-- 2. SELECT * FROM scholarships WHERE status='Open' AND deadline >= CURDATE();
-- 3. SELECT a.*, u.full_name, s.title FROM applications a
--      JOIN users u ON u.id=a.user_id
--      JOIN scholarships s ON s.id=a.scholarship_id;
-- 4. UPDATE applications SET status='Approved', admin_comment='Eligible' WHERE id=1;
-- 5. DELETE FROM users WHERE id=10;
-- 6. SELECT status, COUNT(*) AS total FROM applications GROUP BY status;
