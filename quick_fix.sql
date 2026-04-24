-- Quick fix for missing verified_by columns
-- Run this in phpMyAdmin if the PHP script doesn't work

-- Fix payments table
ALTER TABLE payments ADD COLUMN verified_by INT NULL AFTER status;
ALTER TABLE payments ADD COLUMN verified_at TIMESTAMP NULL AFTER verified_by;

-- Fix payment_requests table  
ALTER TABLE payment_requests ADD COLUMN verified_by INT NULL AFTER status;
ALTER TABLE payment_requests ADD COLUMN verified_at TIMESTAMP NULL AFTER verified_by;

-- Verify the columns were added
DESCRIBE payments;
DESCRIBE payment_requests;
