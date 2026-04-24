-- Migration script to add new columns to car table
-- Run this script to update the car table structure for enhanced car management

ALTER TABLE `car` 
ADD COLUMN `category` VARCHAR(50) DEFAULT NULL AFTER `description`,
ADD COLUMN `transmission` VARCHAR(20) DEFAULT NULL AFTER `category`,
ADD COLUMN `fuel_type` VARCHAR(20) DEFAULT NULL AFTER `transmission`,
ADD COLUMN `seating_capacity` INT DEFAULT NULL AFTER `fuel_type`,
ADD COLUMN `color` VARCHAR(30) DEFAULT NULL AFTER `seating_capacity`,
ADD COLUMN `insurance_info` VARCHAR(255) DEFAULT NULL AFTER `color`,
ADD COLUMN `location` VARCHAR(255) DEFAULT NULL AFTER `insurance_info`;

-- Add indexes for better performance
ALTER TABLE `car` 
ADD INDEX `idx_category` (`category`),
ADD INDEX `idx_transmission` (`transmission`),
ADD INDEX `idx_fuel_type` (`fuel_type`),
ADD INDEX `idx_location` (`location`);
