-- ============================================
-- MITAOE STUDENT INFORMATION PORTAL
-- Complete Database Structure
-- Version: 1.0
-- Date: 2024
-- ============================================

-- Drop database if exists (CAUTION: This will delete all data!)
-- DROP DATABASE IF EXISTS mitaoe_portal;

-- Create database
CREATE DATABASE IF NOT EXISTS mitaoe_portal 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Use the database
USE mitaoe_portal;

-- ============================================
-- TABLE 1: admin - Admin Users
-- ============================================
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_is_active (is_active),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin account
-- Username: admin
-- Password: password
INSERT INTO admin (username, email, password, full_name, phone, role, is_active) 
VALUES (
    'admin',
    'admin@mitaoe.ac.in',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'System Administrator',
    '+91 9876543210',
    'super_admin',
    1
);

-- ============================================
-- TABLE 2: students - Student Records
-- ============================================
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prn VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    dob DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    department VARCHAR(100) NOT NULL,
    class VARCHAR(10) NOT NULL,
    division VARCHAR(10) NOT NULL,
    roll_number VARCHAR(50) NOT NULL,
    batch_year INT NOT NULL,
    parent_name VARCHAR(100),
    parent_phone VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_prn (prn),
    INDEX idx_email (email),
    INDEX idx_department (department),
    INDEX idx_class (class),
    INDEX idx_division (division),
    INDEX idx_batch_year (batch_year),
    INDEX idx_is_active (is_active),
    INDEX idx_full_name (first_name, last_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample student data (optional - remove if not needed)
INSERT INTO students (prn, first_name, middle_name, last_name, email, phone, dob, gender, address, city, state, department, class, division, roll_number, batch_year, parent_name, parent_phone, is_active) VALUES
('2024001', 'John', 'Kumar', 'Doe', 'john.doe@mitaoe.ac.in', '9876543210', '2005-01-15', 'Male', '123 Main Street', 'Pune', 'Maharashtra', 'Computer Science', 'FE', 'A', '101', 2024, 'Robert Doe', '9876543211', 1);

-- ============================================
-- TABLE 3: teachers - Teacher Records
-- ============================================
CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    department VARCHAR(100) NOT NULL,
    designation VARCHAR(100) NOT NULL,
    qualification VARCHAR(100) NOT NULL,
    experience INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_department (department),
    INDEX idx_designation (designation),
    INDEX idx_is_active (is_active),
    INDEX idx_full_name (first_name, last_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample teacher data (optional - remove if not needed)
INSERT INTO teachers (first_name, middle_name, last_name, email, phone, gender, address, city, state, department, designation, qualification, experience, is_active) VALUES
('Rajesh', 'Kumar', 'Sharma', 'rajesh.sharma@mitaoe.ac.in', '9876543220', 'Male', '789 College Road', 'Pune', 'Maharashtra', 'Computer Science', 'Professor', 'PhD', 15, 1),
('Priya', 'Devi', 'Patel', 'priya.patel@mitaoe.ac.in', '9876543221', 'Female', '321 University Lane', 'Pune', 'Maharashtra', 'Information Technology', 'Assistant Professor', 'M.Tech', 5, 1);

-- ============================================
-- TABLE 4: faculty - Faculty Login Accounts
-- ============================================
CREATE TABLE IF NOT EXISTS faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    department VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_department (department),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample faculty account (optional - remove if not needed)
-- Username: faculty@mitaoe.ac.in
-- Password: faculty123
INSERT INTO faculty (full_name, email, password, phone, department, is_active) VALUES
('Dr. Sample Faculty', 'faculty@mitaoe.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+91 9876543230', 'Computer Science', 1);

-- ============================================
-- TABLE 5: activity_logs - Activity Tracking
-- ============================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL DEFAULT 0,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample activity log (optional - remove if not needed)
INSERT INTO activity_logs (admin_id, action, details, ip_address, user_agent) VALUES
(1, 'ADMIN_LOGIN', 'Admin logged in successfully', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

-- ============================================
-- TABLE 6: login_logs - Login Attempts Tracking
-- ============================================
CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('admin', 'faculty') NOT NULL,
    user_id INT NOT NULL DEFAULT 0,
    identifier VARCHAR(255) NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    details TEXT,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_type (user_type),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_identifier (identifier(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample login log (optional - remove if not needed)
INSERT INTO login_logs (user_type, user_id, identifier, status, details, ip_address, user_agent) VALUES
('admin', 1, 'admin', 'success', 'Login successful', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

-- ============================================
-- VIEWS (Optional - for easier data access)
-- ============================================

-- View: Active Students
CREATE OR REPLACE VIEW active_students AS
SELECT 
    id, prn, 
    CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name) AS full_name,
    email, phone, department, class, division, batch_year
FROM students
WHERE is_active = 1
ORDER BY first_name, last_name;

-- View: Active Teachers
CREATE OR REPLACE VIEW active_teachers AS
SELECT 
    id,
    CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name) AS full_name,
    email, phone, department, designation, qualification, experience
FROM teachers
WHERE is_active = 1
ORDER BY first_name, last_name;

-- View: Recent Activities (Last 100)
CREATE OR REPLACE VIEW recent_activities AS
SELECT 
    al.id,
    al.action,
    al.details,
    a.username,
    a.full_name AS admin_name,
    al.ip_address,
    al.created_at
FROM activity_logs al
LEFT JOIN admin a ON al.admin_id = a.id
ORDER BY al.created_at DESC
LIMIT 100;

-- ============================================
-- STORED PROCEDURES (Optional - for common operations)
-- ============================================

DELIMITER //

-- Procedure: Get Student Count by Department
CREATE PROCEDURE IF NOT EXISTS GetStudentCountByDepartment()
BEGIN
    SELECT 
        department,
        COUNT(*) AS student_count,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_count
    FROM students
    GROUP BY department
    ORDER BY student_count DESC;
END //

-- Procedure: Get Teacher Count by Department
CREATE PROCEDURE IF NOT EXISTS GetTeacherCountByDepartment()
BEGIN
    SELECT 
        department,
        COUNT(*) AS teacher_count,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_count
    FROM teachers
    GROUP BY department
    ORDER BY teacher_count DESC;
END //

-- Procedure: Get Dashboard Statistics
CREATE PROCEDURE IF NOT EXISTS GetDashboardStats()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM students WHERE is_active = 1) AS total_students,
        (SELECT COUNT(*) FROM teachers WHERE is_active = 1) AS total_teachers,
        (SELECT COUNT(*) FROM faculty WHERE is_active = 1) AS total_faculty,
        (SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()) AS today_activities,
        (SELECT COUNT(*) FROM login_logs WHERE status = 'success' AND DATE(created_at) = CURDATE()) AS today_logins;
END //

DELIMITER ;

-- ============================================
-- TRIGGERS (Optional - for automatic logging)
-- ============================================

DELIMITER //

-- Trigger: Log student additions
CREATE TRIGGER IF NOT EXISTS after_student_insert
AFTER INSERT ON students
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (admin_id, action, details, ip_address)
    VALUES (0, 'STUDENT_ADDED', CONCAT('New student added: ', NEW.first_name, ' ', NEW.last_name, ' (PRN: ', NEW.prn, ')'), '0.0.0.0');
END //

-- Trigger: Log teacher additions
CREATE TRIGGER IF NOT EXISTS after_teacher_insert
AFTER INSERT ON teachers
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (admin_id, action, details, ip_address)
    VALUES (0, 'TEACHER_ADDED', CONCAT('New teacher added: ', NEW.first_name, ' ', NEW.last_name), '0.0.0.0');
END //

DELIMITER ;

-- ============================================
-- DATABASE INFORMATION QUERY
-- ============================================

-- Show all tables
SHOW TABLES;

-- Show database size
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'mitaoe_portal'
GROUP BY table_schema;

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Verify admin account
SELECT 'Admin Verification' AS Info, username, email, role, is_active 
FROM admin 
WHERE username = 'admin';

-- Verify table structures
SELECT 
    TABLE_NAME AS 'Table',
    TABLE_ROWS AS 'Rows',
    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'mitaoe_portal'
ORDER BY TABLE_NAME;

-- Count records in each table
SELECT 'students' AS table_name, COUNT(*) AS record_count FROM students
UNION ALL
SELECT 'teachers', COUNT(*) FROM teachers
UNION ALL
SELECT 'faculty', COUNT(*) FROM faculty
UNION ALL
SELECT 'admin', COUNT(*) FROM admin
UNION ALL
SELECT 'activity_logs', COUNT(*) FROM activity_logs
UNION ALL
SELECT 'login_logs', COUNT(*) FROM login_logs;

-- ============================================
-- USEFUL QUERIES FOR TESTING
-- ============================================

-- Get all active students
-- SELECT * FROM active_students;

-- Get all active teachers
-- SELECT * FROM active_teachers;

-- Get recent activities
-- SELECT * FROM recent_activities;

-- Get dashboard statistics
-- CALL GetDashboardStats();

-- Get student count by department
-- CALL GetStudentCountByDepartment();

-- Get teacher count by department
-- CALL GetTeacherCountByDepartment();

-- ============================================
-- BACKUP INSTRUCTIONS
-- ============================================
-- To backup: mysqldump -u root -p mitaoe_portal > mitaoe_portal_backup.sql
-- To restore: mysql -u root -p mitaoe_portal < mitaoe_portal_backup.sql

-- ============================================
-- NOTES FOR YOUR FRIENDS
-- ============================================
-- 1. Default Admin Login:
--    Username: admin
--    Password: password
--
-- 2. Database Name: mitaoe_portal
--
-- 3. Required PHP Extensions:
--    - mysqli
--    - pdo_mysql
--
-- 4. Run this entire SQL file in phpMyAdmin or MySQL command line
--
-- 5. After importing, test login at:
--    http://localhost:8080/mitaoe_portal12/admin/admin_login.php
--
-- 6. All tables use utf8mb4 encoding for international characters
--
-- 7. Indexes are created for faster searches
--
-- 8. Sample data is included (can be removed if not needed)
--
-- ============================================

-- End of SQL file
SELECT '✅ Database setup complete!' AS Status;
SELECT '🔐 Default Admin: admin / password' AS Credentials;
SELECT '📊 Total Tables Created: 6' AS Info;
SELECT '🎯 Ready to use!' AS Message;