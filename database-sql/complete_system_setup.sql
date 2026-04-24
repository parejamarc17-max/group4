-- Complete Car Rental System Database Setup
-- Run this script to ensure all tables exist for the complete system

-- 1. Notifications Table (for the notification system)
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `user_role` enum('admin','worker','customer') NOT NULL,
    `title` varchar(255) NOT NULL,
    `message` text NOT NULL,
    `type` varchar(50) DEFAULT 'info',
    `link` varchar(255) DEFAULT NULL,
    `is_read` tinyint(1) DEFAULT 0,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_notifications` (`user_id`, `user_role`),
    KEY `idx_unread` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Payment Requests Table (for the new payment system)
CREATE TABLE IF NOT EXISTS `payment_requests` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `rental_id` int(11) NOT NULL,
    `customer_id` int(11) NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `payment_method` varchar(50) DEFAULT NULL,
    `payment_details` json DEFAULT NULL,
    `qr_code` text DEFAULT NULL,
    `transaction_reference` varchar(255) DEFAULT NULL,
    `receipt_image` varchar(255) DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `status` enum('pending','paid','failed','expired') DEFAULT 'pending',
    `expires_at` timestamp NULL DEFAULT NULL,
    `sent_at` timestamp NULL DEFAULT NULL,
    `paid_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rental_payment` (`rental_id`),
    KEY `idx_customer_payment` (`customer_id`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`rental_id`) REFERENCES `rentals`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Update Rentals Table to include approval status
ALTER TABLE `rentals` 
ADD COLUMN IF NOT EXISTS `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS `approved_by` int(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `approved_at` timestamp NULL DEFAULT NULL;

-- 4. Update Payments Table to match new system
ALTER TABLE `payments` 
ADD COLUMN IF NOT EXISTS `verified_by` int(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `verified_at` timestamp NULL DEFAULT NULL;

-- 5. Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_rentals_approval` ON `rentals` (`approval_status`, `status`);
CREATE INDEX IF NOT EXISTS `idx_rentals_customer` ON `rentals` (`user_id`, `created_at`);
CREATE INDEX IF NOT EXISTS `idx_payments_verification` ON `payments` (`status`, `created_at`);

-- 6. Insert default notification settings (optional)
INSERT IGNORE INTO `notifications` (`user_id`, `user_role`, `title`, `message`, `type`) 
SELECT id, 'admin', 'System Setup Complete', 'Car rental system has been successfully configured with all features.', 'system'
FROM users WHERE role = 'admin' LIMIT 1;

-- 7. Update car table if missing columns (from previous migration)
ALTER TABLE `car` 
ADD COLUMN IF NOT EXISTS `category` varchar(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `transmission` varchar(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `fuel_type` varchar(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `seating_capacity` int(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `color` varchar(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `insurance_info` text DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `location` varchar(255) DEFAULT NULL;
