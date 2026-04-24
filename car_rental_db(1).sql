-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2026 at 11:40 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `car_rental_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `car`
--

CREATE TABLE `car` (
  `id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `car_name` varchar(100) NOT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `plate_num` varchar(100) NOT NULL,
  `year` int(11) DEFAULT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `transmission` varchar(20) DEFAULT NULL,
  `fuel_type` varchar(20) DEFAULT NULL,
  `seating_capacity` int(11) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `insurance_info` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('available','rented','maintenance') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car`
--

INSERT INTO `car` (`id`, `image`, `car_name`, `brand`, `model`, `plate_num`, `year`, `price_per_day`, `category`, `description`, `transmission`, `fuel_type`, `seating_capacity`, `color`, `insurance_info`, `location`, `status`, `created_at`) VALUES
(1, '../uploads/cars/1776959301_69ea3f45acb1c.jpg', 'GTR', 'Nissan', 'gt-r', 'R35 GTR', 2025, 5000.00, 'Sports', 'The Nissan GT-R is a high-performance sports car (supercar) manufactured by the Japanese brand Nissan. The current, final generation is the R35 (produced 2007–2025), which is renowned for its 3.8-liter twin-turbo V6 engine, all-wheel drive, and \"Godzilla\" nickname due to its superior performance.', 'Manual', 'Hybrid', 4, 'white,blue', 'Full Coverage', 'Mati city, Davao oriental', 'rented', '2026-04-22 01:51:20'),
(3, '../uploads/cars/1776959599_69ea406f4b828.jpg', 'maclaren', 'honda', 'honda', 'MC-243', 2021, 4000.00, 'Sports', 'maganda', 'Automatic', 'Hybrid', 2, 'blue', 'Full Coverage', 'dahicna', 'rented', '2026-04-23 15:53:19'),
(9, '../assets/images/1777003333_69eaeb457d014.jpg', 'Porsche', 'Porsche', '911 GT1 Straßenversion', '09897865432', 2023, 5000.00, 'Luxury', 'best car ever', 'CVT', 'Electric', 0, 'white', 'Limited', 'mati city', 'available', '2026-04-24 04:02:13'),
(10, '../assets/images/1777003840_69eaed40b3ca5.jpg', 'mustang', 'ford mustang', '2026 ford mustang', 'MS-123', 2021, 10000.00, 'Luxury', 'best car', 'Manual', 'Hybrid', 2, 'red', 'Full Coverage', 'mati city', 'available', '2026-04-24 04:10:40'),
(12, '../assets/images/1777011014_69eb094605daa.jpg', 'hilux', 'hilux', 'toyota', 'hilux-345', 2026, 7000.00, 'Luxury', 'best car', 'Automatic', 'Gasoline', 4, 'red', 'Full Coverage', 'dahican', 'available', '2026-04-24 06:10:14'),
(13, '../assets/images/1777020016_69eb2c708ae50.png', 'Fernce', 'Kregur', 'Fernce-274', 'CR1TT3R', 2026, 1000.00, 'Economy', '', 'Automatic', 'Diesel', 4, 'Red', '', '', 'available', '2026-04-24 08:40:16');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `phone`, `address`, `created_at`) VALUES
(1, 1, 'N/A', 'Admin Address', '2026-04-16 15:48:55'),
(4, 14, '09098978676', 'mati', '2026-04-22 15:47:50'),
(5, 15, '', '', '2026-04-22 15:48:48'),
(6, 17, '', '', '2026-04-23 15:01:36'),
(7, 22, '09897898767', 'mati', '2026-04-23 18:48:04'),
(8, 23, '09123454321', 'dahican', '2026-04-23 18:53:24'),
(9, 24, '09894567898', 'mati', '2026-04-24 01:54:00'),
(10, 28, '09098887077', 'mati', '2026-04-24 05:12:47'),
(11, 29, '098535234241233', 'Mati', '2026-04-24 08:19:52');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_role` enum('admin','worker','customer') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('booking','payment','approval','system') DEFAULT 'booking',
  `is_read` tinyint(1) DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `user_role`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES
(1, 1, 'customer', 'Booking Approved!', 'Your booking for GTR has been approved. Please complete the payment to confirm your rental.', 'payment', 0, 'customer/make_payment.php?rental_id=2', '2026-04-24 01:05:22'),
(2, 1, 'customer', 'Booking Approved!', 'Your booking for GTR has been approved. Please complete the payment to confirm your rental.', 'payment', 0, 'customer/make_payment.php?rental_id=1', '2026-04-24 01:06:56'),
(3, 15, 'customer', 'Booking Approved!', 'Your booking for GTR has been approved. Please complete the payment to confirm your rental.', 'payment', 0, 'customer/make_payment.php?rental_id=4', '2026-04-24 01:07:00'),
(4, 15, 'customer', 'Booking Approved!', 'Your booking for GTR has been approved. Please complete the payment to confirm your rental.', 'payment', 0, 'customer/make_payment.php?rental_id=5', '2026-04-24 09:46:47'),
(5, 1, 'admin', 'Payment Received - Need Verification', 'Customer justine has submitted payment for GTR. Reference: blasss213232434', 'payment', 0, 'worker/verify_payments.php', '2026-04-24 12:17:57'),
(6, 5, 'worker', 'Payment Received - Need Verification', 'Customer justine has submitted payment for GTR. Reference: blasss213232434', 'payment', 0, 'worker/verify_payments.php', '2026-04-24 12:17:57'),
(7, 6, 'worker', 'Payment Received - Need Verification', 'Customer justine has submitted payment for GTR. Reference: blasss213232434', 'payment', 0, 'worker/verify_payments.php', '2026-04-24 12:17:57'),
(8, 19, 'worker', 'Payment Received - Need Verification', 'Customer justine has submitted payment for GTR. Reference: blasss213232434', 'payment', 0, 'worker/verify_payments.php', '2026-04-24 12:17:57'),
(9, 20, 'worker', 'Payment Received - Need Verification', 'Customer justine has submitted payment for GTR. Reference: blasss213232434', 'payment', 0, 'worker/verify_payments.php', '2026-04-24 12:17:57'),
(10, 21, 'worker', 'Payment Received - Need Verification', 'Customer justine has submitted payment for GTR. Reference: blasss213232434', 'payment', 0, 'worker/verify_payments.php', '2026-04-24 12:17:57'),
(11, 24, 'customer', 'Booking Approved!', 'Your booking for Porsche has been approved by admin. Please complete the payment to confirm your rental.', 'payment', 0, 'customer/make_payment.php?rental_id=7', '2026-04-24 12:33:37'),
(12, 28, 'customer', 'Booking Approved!', 'Your booking for mustang has been approved by admin. Please complete the payment to confirm your rental.', 'payment', 0, 'customer/make_payment.php?rental_id=8', '2026-04-24 13:14:25'),
(13, 15, 'customer', 'Booking Approved!', 'Your booking for maclaren has been approved. Please complete the payment to confirm your rental.', 'payment', 0, 'customer/make_payment.php?rental_id=6', '2026-04-24 13:34:10'),
(14, 28, 'customer', 'Booking Approved!', 'Your booking for maclaren has been approved. Please complete the payment to confirm your rental.', 'payment', 0, 'customer/make_payment.php?rental_id=9', '2026-04-24 15:31:32'),
(15, 1, 'admin', 'Payment Received - Need Verification', 'Customer hayabutaw has submitted payment for maclaren. Reference: 1281726337647', 'payment', 0, 'worker/verify_payments.php', '2026-04-24 15:33:11'),
(16, 5, 'worker', 'Payment Received - Need Verification', 'Customer hayabutaw has submitted payment for maclaren. Reference: 1281726337647', 'payment', 0, 'worker/verify_payments.php', '2026-04-24 15:33:11'),
(17, 6, 'worker', 'Payment Received - Need Verification', 'Customer hayabutaw has submitted payment for maclaren. Reference: 1281726337647', 'payment', 0, 'worker/verify_payments.php', '2026-04-24 15:33:11'),
(18, 19, 'worker', 'Payment Received - Need Verification', 'Customer hayabutaw has submitted payment for maclaren. Reference: 1281726337647', 'payment', 0, 'worker/verify_payments.php', '2026-04-24 15:33:11'),
(19, 20, 'worker', 'Payment Received - Need Verification', 'Customer hayabutaw has submitted payment for maclaren. Reference: 1281726337647', 'payment', 0, 'worker/verify_payments.php', '2026-04-24 15:33:11'),
(20, 21, 'worker', 'Payment Received - Need Verification', 'Customer hayabutaw has submitted payment for maclaren. Reference: 1281726337647', 'payment', 0, 'worker/verify_payments.php', '2026-04-24 15:33:11'),
(21, 15, 'customer', 'Payment Verified!', 'Your payment for GTR has been verified. Your rental is now active!', 'booking', 0, 'customer/my_bookings.php', '2026-04-24 16:12:56');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','gcash','bank_transfer','credit_card') DEFAULT 'cash',
  `reference_number` varchar(100) DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `status` enum('pending','verified','failed','refunded') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_requests`
--

CREATE TABLE `payment_requests` (
  `id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_details` text DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `status` enum('pending','paid','expired','cancelled') DEFAULT 'pending',
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `sent_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_requests`
--

INSERT INTO `payment_requests` (`id`, `rental_id`, `customer_id`, `amount`, `payment_method`, `payment_details`, `qr_code`, `status`, `verified_by`, `verified_at`, `sent_at`, `expires_at`, `paid_at`, `transaction_reference`, `receipt_image`, `notes`) VALUES
(1, 2, 1, 40000.00, NULL, '{\"reference\":\"DRV1776963922444\",\"instructions\":\"1. Transfer to our bank account<br>2. Use reference number as description<br>3. Upload receipt<br>4. Wait for verification\",\"account_name\":\"DriveGo Car Rental\",\"account_number\":\"1234-5678-9012\"}', 'eyJhbW91bnQiOiI0MDAwMC4wMCIsInJlZmVyZW5jZSI6IkRSVjE3NzY5NjM5MjI0NDQiLCJtZXJjaGFudCI6IkRyaXZlR28gUmVudGFscyJ9', 'pending', NULL, NULL, '2026-04-24 01:05:22', '2026-04-26 19:05:22', NULL, NULL, NULL, NULL),
(2, 1, 1, 40000.00, NULL, '{\"reference\":\"DRV1776964016172\",\"instructions\":\"1. Transfer to our bank account<br>2. Use reference number as description<br>3. Upload receipt<br>4. Wait for verification\",\"account_name\":\"DriveGo Car Rental\",\"account_number\":\"1234-5678-9012\"}', 'eyJhbW91bnQiOiI0MDAwMC4wMCIsInJlZmVyZW5jZSI6IkRSVjE3NzY5NjQwMTYxNzIiLCJtZXJjaGFudCI6IkRyaXZlR28gUmVudGFscyJ9', 'pending', NULL, NULL, '2026-04-24 01:06:56', '2026-04-26 19:06:56', NULL, NULL, NULL, NULL),
(3, 4, 15, 30000.00, NULL, '{\"reference\":\"DRV1776964020293\",\"instructions\":\"1. Transfer to our bank account<br>2. Use reference number as description<br>3. Upload receipt<br>4. Wait for verification\",\"account_name\":\"DriveGo Car Rental\",\"account_number\":\"1234-5678-9012\"}', 'eyJhbW91bnQiOiIzMDAwMC4wMCIsInJlZmVyZW5jZSI6IkRSVjE3NzY5NjQwMjAyOTMiLCJtZXJjaGFudCI6IkRyaXZlR28gUmVudGFscyJ9', 'pending', NULL, NULL, '2026-04-24 01:07:00', '2026-04-26 19:07:00', NULL, NULL, NULL, NULL),
(4, 5, 15, 40000.00, 'gcash', '{\"reference\":\"DRV1776995207263\",\"instructions\":\"1. Transfer to our bank account<br>2. Use reference number as description<br>3. Upload receipt<br>4. Wait for verification\",\"account_name\":\"DriveGo Car Rental\",\"account_number\":\"1234-5678-9012\"}', 'eyJhbW91bnQiOiI0MDAwMC4wMCIsInJlZmVyZW5jZSI6IkRSVjE3NzY5OTUyMDcyNjMiLCJtZXJjaGFudCI6IkRyaXZlR28gUmVudGFscyJ9', '', 1, '2026-04-24 08:12:56', '2026-04-24 09:46:47', '2026-04-27 03:46:47', '2026-04-24 12:17:57', 'blasss213232434', '../uploads/receipts/receipt_1777004277_5.jpg', 'confirm'),
(5, 7, 24, 25000.00, 'bank_transfer', '{\"reference\":\"DRV1777005217825\",\"instructions\":\"1. Transfer to our bank account<br>2. Use reference number as description<br>3. Upload receipt<br>4. Wait for verification\",\"account_name\":\"DriveGo Car Rental\",\"account_number\":\"1234-5678-9012\"}', 'eyJhbW91bnQiOiIyNTAwMC4wMCIsInJlZmVyZW5jZSI6IkRSVjE3NzcwMDUyMTc4MjUiLCJtZXJjaGFudCI6IkRyaXZlR28gUmVudGFscyJ9', 'pending', NULL, NULL, '2026-04-24 12:33:37', '2026-04-27 06:33:37', NULL, NULL, NULL, NULL),
(6, 8, 28, 40000.00, 'bank_transfer', '{\"reference\":\"DRV1777007665467\",\"instructions\":\"1. Transfer to our bank account<br>2. Use reference number as description<br>3. Upload receipt<br>4. Wait for verification\",\"account_name\":\"DriveGo Car Rental\",\"account_number\":\"1234-5678-9012\"}', 'eyJhbW91bnQiOiI0MDAwMC4wMCIsInJlZmVyZW5jZSI6IkRSVjE3NzcwMDc2NjU0NjciLCJtZXJjaGFudCI6IkRyaXZlR28gUmVudGFscyJ9', 'pending', NULL, NULL, '2026-04-24 13:14:25', '2026-04-27 07:14:25', NULL, NULL, NULL, NULL),
(7, 6, 15, 68000.00, 'bank_transfer', '{\"reference\":\"DRV1777008850299\",\"instructions\":\"1. Transfer to our bank account<br>2. Use reference number as description<br>3. Upload receipt<br>4. Wait for verification\",\"account_name\":\"DriveGo Car Rental\",\"account_number\":\"1234-5678-9012\"}', 'eyJhbW91bnQiOiI2ODAwMC4wMCIsInJlZmVyZW5jZSI6IkRSVjE3NzcwMDg4NTAyOTkiLCJtZXJjaGFudCI6IkRyaXZlR28gUmVudGFscyJ9', 'pending', NULL, NULL, '2026-04-24 13:34:10', '2026-04-27 07:34:10', NULL, NULL, NULL, NULL),
(8, 9, 28, 16000.00, 'gcash', '{\"reference\":\"DRV1777015892270\",\"instructions\":\"1. Transfer to our bank account<br>2. Use reference number as description<br>3. Upload receipt<br>4. Wait for verification\",\"account_name\":\"DriveGo Car Rental\",\"account_number\":\"1234-5678-9012\"}', 'eyJhbW91bnQiOiIxNjAwMC4wMCIsInJlZmVyZW5jZSI6IkRSVjE3NzcwMTU4OTIyNzAiLCJtZXJjaGFudCI6IkRyaXZlR28gUmVudGFscyJ9', 'paid', NULL, NULL, '2026-04-24 15:31:32', '2026-04-27 09:31:32', '2026-04-24 15:33:11', '1281726337647', '../uploads/receipts/receipt_1777015991_9.jpg', 'dfsdgfdgdf');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `id` int(11) NOT NULL,
  `car_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `rental_date` date NOT NULL,
  `return_date` date NOT NULL,
  `total_days` int(11) DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `payment_status` enum('pending','partial','paid','refunded','overdue') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_due_date` date DEFAULT NULL,
  `payment_instructions` text DEFAULT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`id`, `car_id`, `customer_name`, `customer_phone`, `customer_email`, `rental_date`, `return_date`, `total_days`, `total_cost`, `status`, `approval_status`, `approved_by`, `approved_at`, `created_at`, `user_id`, `payment_status`, `payment_method`, `payment_due_date`, `payment_instructions`, `booking_date`) VALUES
(1, 1, 'reyven', '01234567890', 'reyven2@gmail.com', '2026-08-09', '2026-08-11', 2, 40000.00, 'completed', 'approved', 1, '2026-04-24 01:06:56', '2026-04-16 16:12:16', 1, 'pending', NULL, NULL, NULL, '2026-04-16 16:12:16'),
(2, 1, 'althea', '09878675674', 'althea@gmail.com', '2026-09-11', '2026-09-13', 2, 40000.00, 'completed', 'approved', 1, '2026-04-24 01:05:22', '2026-04-17 06:45:37', 1, 'paid', NULL, NULL, NULL, '2026-04-17 06:45:37'),
(3, 3, 'martonbayot', '09878987656', 'marton@gmail.com', '2026-05-01', '2026-05-31', 30, 150000.00, 'cancelled', 'pending', NULL, NULL, '2026-04-17 07:20:39', 3, 'refunded', NULL, NULL, NULL, '2026-04-17 07:20:39'),
(4, 1, 'reyven', '09789786567', 'hayop@gmail.com', '2026-05-01', '2026-04-25', 6, 30000.00, 'cancelled', 'approved', 1, '2026-04-24 01:07:00', '2026-04-23 15:40:47', 15, 'refunded', NULL, NULL, NULL, '2026-04-23 15:40:47'),
(5, 1, 'justine', '09897678765', 'justinegwapo@gmail.com', '2026-05-06', '2026-05-14', 8, 40000.00, 'active', 'approved', 1, '2026-04-24 09:46:47', '2026-04-24 01:45:47', 15, '', 'gcash', NULL, NULL, '2026-04-24 01:45:47'),
(6, 3, 'justine', '09897678987', 'justinegwapo@gmail.com', '2026-04-25', '2026-05-12', 17, 68000.00, 'completed', 'approved', 1, '2026-04-24 13:34:10', '2026-04-24 01:51:16', 15, 'paid', NULL, NULL, NULL, '2026-04-24 01:51:16'),
(7, 9, 'reyvensayp', '09894567898', 'reyven@gmail.com', '2026-05-14', '2026-05-19', 5, 25000.00, 'completed', 'approved', 1, '2026-04-24 12:33:37', '2026-04-24 04:33:11', 24, 'paid', NULL, NULL, NULL, '2026-04-24 04:33:11'),
(8, 10, 'hayabutaw', '09098887077', 'hayabusa@gmail.com', '2026-04-25', '2026-04-29', 4, 40000.00, 'completed', 'approved', 1, '2026-04-24 13:14:25', '2026-04-24 05:13:37', 28, 'paid', NULL, NULL, NULL, '2026-04-24 05:13:37'),
(9, 3, 'hayabutaw', '09098887077', 'hayabusa@gmail.com', '2026-05-02', '2026-05-06', 4, 16000.00, 'active', 'approved', 1, '2026-04-24 15:31:32', '2026-04-24 07:30:04', 28, 'paid', 'gcash', NULL, NULL, '2026-04-24 07:30:04');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `grand_total` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(20) DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','worker','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `phone`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@carrental.com', NULL, 'admin', '2026-04-16 15:48:55'),
(5, 'worker123_69e87fc41d86b', '$2y$10$iCtrz3gY8ex7ANRtrGGEI.JHYlisjWD8qrXmDtkKPP6ddfM454C3y', 'worker123', 'worker123@worker.local', '09897654656', 'worker', '2026-04-22 07:59:00'),
(6, 'reyvensaypan_69e8a713b4071', '$2y$10$N9XB85mEHApNL3BrVDam2Oo/zhVGhGad7ZJGNE4IqAm51lfmYpcF.', 'reyvensaypan', 'reyvensaypan@worker.local', '09897675456', 'worker', '2026-04-22 10:46:43'),
(14, 'gdg', '$2y$10$vB6KmWEF1ObFabYvuTCKW.cyNC9nXoTCj69L9jJdljt6VEfGYfqH.', 'gdgrgd', 'gdg@gmail.com', NULL, 'customer', '2026-04-22 15:47:50'),
(15, 'justine', '$2y$10$1pCQVO.b.4TrJb6hIm21Duzx8BfKjFQ49OfQ4RhjyO6rsSRvJ2EUq', 'justine', 'justinegwapo@gmail.com', '09897656754', 'customer', '2026-04-22 15:48:48'),
(17, 'althea', '$2y$10$FNAcIN1BDQbi1DdM3gAT2.U08..PO4KvI4.KeT.HqoMJdjpMT1/J6', 'altheaa', 'althea@gmail.com', NULL, 'customer', '2026-04-23 15:01:36'),
(19, 'jobertkopal_69ea369867568', '$2y$10$jNtw4VNtxoBSQ5ePHK39Pu.QZ8DjU.5Q9JJV2WenyCCqJbqhwWxBm', 'jobertkopal', 'jobertkopal@worker.local', '09898712345', 'worker', '2026-04-23 15:11:20'),
(20, 'reyvenhaha_69ea39838667c', '$2y$10$MhU9aUF010IfUTytBkmxGuNTI6AKC0VFVr6m0l7ubJt/1TTP10t8S', 'reyvenhaha', 'reyvenhaha@worker.local', '09890989889', 'worker', '2026-04-23 15:23:47'),
(21, 'wokerhaah_69ea4689d6d5a', '$2y$10$xYpxQp8Q60k8ZAQMFzPw/uR2MEcWLQKAOW6WPBUl08XOe1h.BbnAG', 'wokerhaah', 'wokerhaah@worker.local', '09099988765', 'worker', '2026-04-23 16:19:21'),
(22, 'hahaha', '$2y$10$GrXy5q7C5MleYoJ7EEMDLelfUYvFSP83eyKpwryf6BxyDJycAdKeu', 'hahahasay', 'haha@gmail.com', NULL, 'customer', '2026-04-23 18:48:04'),
(23, 'maayo', '$2y$10$bzG5ECOJVaym17ennmxW/OUj.3KnIVgSc9I7uD.h.RMgXeIOCNQb6', 'maayokaayo', 'maayo@gmail.com', NULL, 'customer', '2026-04-23 18:53:24'),
(24, 'reyven', '$2y$10$ygoyPKOsJNp3sNjUBVKdIOyhrzEAMuu23YHZ.p/hGL51BQyQ9hLKm', 'reyvensayp', 'reyven@gmail.com', NULL, 'customer', '2026-04-24 01:53:59'),
(28, 'hayabusa', '$2y$10$dBHmiMSb4nrari1nvEP4reR/iI2GzfUi52x2pjUuLi7iN3omOREkC', 'hayabutaw', 'hayabusa@gmail.com', NULL, 'customer', '2026-04-24 05:12:47'),
(29, 'Khader', '$2y$10$tbGKi2K0dFM6Y0ck7bt3H.XiIUC6cZC1xD5Nn2H/xJ1YItx0BYiwm', 'Josh Ryler S. Stevan', 'joshryler@gmail.com', NULL, 'customer', '2026-04-24 08:19:52');

-- --------------------------------------------------------

--
-- Table structure for table `worker_activity`
--

CREATE TABLE `worker_activity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` enum('login','logout') NOT NULL,
  `activity_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `worker_activity`
--

INSERT INTO `worker_activity` (`id`, `user_id`, `activity_type`, `activity_time`, `ip_address`) VALUES
(5, 1, 'logout', '2026-04-22 15:15:38', NULL),
(10, 1, 'logout', '2026-04-23 14:54:17', NULL),
(11, 1, 'logout', '2026-04-23 15:00:57', NULL),
(12, 1, 'logout', '2026-04-23 15:04:20', NULL),
(13, 1, 'logout', '2026-04-23 15:22:37', NULL),
(14, 1, 'logout', '2026-04-23 15:25:41', NULL),
(15, 1, 'logout', '2026-04-23 15:29:45', NULL),
(16, 1, 'logout', '2026-04-23 15:40:02', NULL),
(17, 1, 'logout', '2026-04-23 15:49:22', NULL),
(18, 1, 'logout', '2026-04-23 15:53:26', NULL),
(19, 1, 'logout', '2026-04-23 16:00:25', NULL),
(20, 1, 'logout', '2026-04-23 16:16:46', NULL),
(21, 1, 'logout', '2026-04-23 16:19:47', NULL),
(22, 1, 'logout', '2026-04-23 17:00:07', NULL),
(23, 1, 'logout', '2026-04-23 17:01:49', NULL),
(24, 1, 'logout', '2026-04-23 17:05:27', NULL),
(25, 1, 'logout', '2026-04-23 17:05:54', NULL),
(26, 1, 'logout', '2026-04-23 18:46:47', NULL),
(27, 1, 'logout', '2026-04-23 18:51:00', NULL),
(28, 1, 'logout', '2026-04-24 01:25:36', NULL),
(29, 1, 'logout', '2026-04-24 01:39:01', NULL),
(30, 1, 'logout', '2026-04-24 01:46:53', NULL),
(31, 1, 'logout', '2026-04-24 01:49:04', NULL),
(32, 1, 'logout', '2026-04-24 02:40:59', NULL),
(33, 1, 'logout', '2026-04-24 02:49:21', NULL),
(34, 1, 'logout', '2026-04-24 03:07:40', NULL),
(35, 1, 'logout', '2026-04-24 03:22:42', NULL),
(36, 1, 'logout', '2026-04-24 03:27:00', NULL),
(37, 1, 'logout', '2026-04-24 03:44:58', NULL),
(38, 1, 'logout', '2026-04-24 04:02:23', NULL),
(39, 1, 'logout', '2026-04-24 04:10:49', NULL),
(40, 1, 'logout', '2026-04-24 04:15:46', NULL),
(41, 1, 'logout', '2026-04-24 05:06:47', NULL),
(42, 1, 'logout', '2026-04-24 05:46:41', NULL),
(43, 1, 'logout', '2026-04-24 05:49:47', NULL),
(44, 1, 'logout', '2026-04-24 06:01:00', NULL),
(45, 1, 'logout', '2026-04-24 06:10:19', NULL),
(46, 1, 'logout', '2026-04-24 07:31:38', NULL),
(47, 1, 'logout', '2026-04-24 07:34:24', NULL),
(48, 1, 'logout', '2026-04-24 07:47:59', NULL),
(49, 1, 'logout', '2026-04-24 08:34:17', NULL),
(50, 1, 'logout', '2026-04-24 08:40:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `worker_applications`
--

CREATE TABLE `worker_applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `experience` varchar(50) DEFAULT NULL,
  `proof_file` varchar(255) DEFAULT NULL,
  `meeting_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `worker_applications`
--

INSERT INTO `worker_applications` (`id`, `user_id`, `full_name`, `phone`, `address`, `experience`, `proof_file`, `meeting_date`, `status`, `created_at`) VALUES
(1, 4, 'Thomaston', '0987632422223', 'mati', NULL, NULL, NULL, 'approved', '2026-04-17 11:09:35'),
(2, NULL, 'jamesgwapo', '09897898767', 'mati city', NULL, NULL, NULL, 'approved', '2026-04-22 02:17:53'),
(3, NULL, 'reyvensayp', '09898989898', 'maticity', NULL, NULL, NULL, 'approved', '2026-04-22 02:19:33'),
(4, NULL, 'Jamie Ryler S.Stevan', '092412153521', 'mati', NULL, NULL, NULL, 'approved', '2026-04-22 07:37:44'),
(5, 5, 'worker123', '09897654656', 'Davao', NULL, NULL, NULL, 'approved', '2026-04-22 07:58:46'),
(6, 6, 'reyvensaypan', '09897675456', 'mati city', NULL, NULL, NULL, 'approved', '2026-04-22 10:30:21'),
(7, 7, 'reyvensaypan', '09898789786', 'mati', NULL, NULL, NULL, 'approved', '2026-04-22 10:47:40'),
(8, 8, 'James Ryan S. Esteban', '09876543657', 'Manila', NULL, NULL, NULL, 'approved', '2026-04-22 10:52:11'),
(9, 9, 'reyvensayp', '09897654321', 'matii', NULL, NULL, NULL, 'approved', '2026-04-22 14:08:25'),
(10, 10, 'saypan', '09897865643', 'davao\r\n', NULL, NULL, NULL, 'rejected', '2026-04-22 14:27:42'),
(11, NULL, 'saypan', '09897865643', 'davao\r\n', NULL, NULL, NULL, 'rejected', '2026-04-22 14:38:30'),
(12, 11, 'saypan', '09897865432', 'mati', NULL, NULL, NULL, 'approved', '2026-04-22 15:05:10'),
(13, NULL, 'marton bernaldez', '09878678654', 'mati city', NULL, NULL, NULL, 'pending', '2026-04-22 15:19:39'),
(14, 16, 'altheagwapa', '09897867865', 'taga ila', NULL, NULL, NULL, 'approved', '2026-04-23 14:55:05'),
(15, 18, 'intotagalog', '09897867567', 'central', NULL, NULL, NULL, 'approved', '2026-04-23 15:05:09'),
(16, 19, 'jobertkopal', '09898712345', 'taga ila', NULL, NULL, NULL, 'approved', '2026-04-23 15:10:35'),
(17, 20, 'reyvenhaha', '09890989889', 'mati\r\n', NULL, NULL, NULL, 'approved', '2026-04-23 15:23:24'),
(18, 21, 'wokerhaah', '09099988765', 'mati', NULL, NULL, NULL, 'approved', '2026-04-23 16:18:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `car`
--
ALTER TABLE `car`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate_num` (`plate_num`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_read` (`is_read`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rental_id` (`rental_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `payment_requests`
--
ALTER TABLE `payment_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rental_id` (`rental_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_id` (`car_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_booking_dates` (`rental_date`,`return_date`),
  ADD KEY `idx_approval_status` (`approval_status`),
  ADD KEY `idx_booking_status` (`status`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `username_2` (`username`,`full_name`,`email`,`phone`),
  ADD KEY `idx_users_role` (`role`);

--
-- Indexes for table `worker_activity`
--
ALTER TABLE `worker_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_activity_time` (`activity_time`);

--
-- Indexes for table `worker_applications`
--
ALTER TABLE `worker_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `car`
--
ALTER TABLE `car`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_requests`
--
ALTER TABLE `payment_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `worker_activity`
--
ALTER TABLE `worker_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `worker_applications`
--
ALTER TABLE `worker_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_requests`
--
ALTER TABLE `payment_requests`
  ADD CONSTRAINT `payment_requests_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `worker_activity`
--
ALTER TABLE `worker_activity`
  ADD CONSTRAINT `worker_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
