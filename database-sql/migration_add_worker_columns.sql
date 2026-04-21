-- Migration: Add username, email, and password columns to worker_applications table
-- This allows storing worker credentials during registration, then creating user account on approval

ALTER TABLE `worker_applications` ADD COLUMN `username` varchar(50) AFTER `full_name`;
ALTER TABLE `worker_applications` ADD COLUMN `email` varchar(100) AFTER `username`;
ALTER TABLE `worker_applications` ADD COLUMN `password` varchar(255) AFTER `email`;
ALTER TABLE `worker_applications` MODIFY COLUMN `status` enum('pending','approved','rejected','deleted') DEFAULT 'pending';

-- Update existing pending record if any
-- (Leave existing data as-is since username/password were not previously stored)
