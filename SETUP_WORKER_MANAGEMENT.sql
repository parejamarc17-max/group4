-- QUICK SETUP: Worker Management System Database Migration
-- Run this in phpMyAdmin or your database client immediately
-- Database: car_rental_db

-- Step 1: Add status column to users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT NULL;

-- Step 2: Add user_id column to worker_applications table
ALTER TABLE worker_applications 
ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL;

-- Step 3: Create worker_activity table for login/logout tracking
CREATE TABLE IF NOT EXISTS worker_activity (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  activity_type ENUM('login', 'logout') NOT NULL,
  activity_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_activity_time (activity_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Verification: Run these to confirm setup
-- SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME='status';
-- SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='worker_applications' AND COLUMN_NAME='user_id';
-- SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME='worker_activity';
