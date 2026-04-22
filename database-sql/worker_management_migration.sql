-- Worker Management System Database Updates
-- Run these queries to add necessary columns and tables

-- 1. Add status column to users table if it doesn't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT NULL;

-- 2. Add user_id column to worker_applications if it doesn't exist  
ALTER TABLE worker_applications 
ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL;

-- 3. Create worker_activity table for tracking login/logout
CREATE TABLE IF NOT EXISTS worker_activity (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  activity_type ENUM('login', 'logout') NOT NULL,
  activity_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (user_id),
  INDEX (activity_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Update worker_applications status to include 'approved' if using enum
-- This may not be necessary if enum already includes approved status
