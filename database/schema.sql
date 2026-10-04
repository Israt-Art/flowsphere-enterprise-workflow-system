-- ============================================================
-- FlowSphere Database Schema
-- Enterprise Workflow Management System
-- ============================================================
-- This file creates 3 tables:
--   1. users             -> stores every login (employee, manager, hr, director)
--   2. documents          -> stores every submitted document and its current status
--   3. workflow_history    -> stores every approve/reject action (an audit trail)
--
-- Beginner note: think of workflow_history as a "log" table.
-- Every time someone approves/rejects, we INSERT a new row here.
-- We never delete or overwrite history rows -> that's what makes it an audit trail.
-- ============================================================

CREATE DATABASE IF NOT EXISTS flowsphere;
USE flowsphere;

-- ------------------------------------------------------------
-- 1. USERS TABLE
-- ------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,   -- store the HASH, never the real password
    role ENUM('employee', 'manager', 'hr', 'director') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- 2. DOCUMENTS TABLE
-- ------------------------------------------------------------
-- current_status tracks WHERE the document currently sits in the workflow.
-- This is the single source of truth for "what stage is this document at".
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submitted_by INT NOT NULL,             -- FK -> users.id (the employee who submitted it)
    title VARCHAR(150) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),                -- where the uploaded attachment lives on disk
    current_status ENUM(
        'pending_manager',
        'pending_hr',
        'pending_director',
        'approved',
        'rejected'
    ) NOT NULL DEFAULT 'pending_manager',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (submitted_by) REFERENCES users(id)
);

-- ------------------------------------------------------------
-- 3. WORKFLOW_HISTORY TABLE (the audit trail)
-- ------------------------------------------------------------
-- Every approve/reject/forward action creates ONE row here.
-- This lets the employee see the full history of their document.
CREATE TABLE workflow_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,              -- FK -> documents.id
    action_by INT NOT NULL,                -- FK -> users.id (who performed the action)
    role_at_time ENUM('manager', 'hr', 'director') NOT NULL,
    action ENUM('approved', 'rejected', 'forwarded') NOT NULL,
    comment TEXT,                          -- required when rejecting
    action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (document_id) REFERENCES documents(id),
    FOREIGN KEY (action_by) REFERENCES users(id)
);

-- ------------------------------------------------------------
-- Sample seed data (1 user per role) so you can log in immediately.
-- Password for ALL of these is: password123
-- (the hash below is a real bcrypt hash of "password123")
-- ------------------------------------------------------------
INSERT INTO users (full_name, email, password_hash, role) VALUES
('Alice Employee', 'employee@flowsphere.com', '$2b$10$uNJLoyxTL5WXkTmPQEMF8uaNm.fnrcLZt6PURZs2bdYbS2KFd/M16', 'employee'),
('Mark Manager',   'manager@flowsphere.com',  '$2b$10$uNJLoyxTL5WXkTmPQEMF8uaNm.fnrcLZt6PURZs2bdYbS2KFd/M16', 'manager'),
('Hana HR',        'hr@flowsphere.com',       '$2b$10$uNJLoyxTL5WXkTmPQEMF8uaNm.fnrcLZt6PURZs2bdYbS2KFd/M16', 'hr'),
('Derek Director', 'director@flowsphere.com', '$2b$10$uNJLoyxTL5WXkTmPQEMF8uaNm.fnrcLZt6PURZs2bdYbS2KFd/M16', 'director');
