<?php
session_start();
require_once '../config/database.php';
require_once '../includes/NotificationHelper.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../p_login/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$car_id = (int)$_POST['car_id'];
$customer_name = trim($_POST['customer_name']);
$customer_email = trim($_POST['customer_email']);
$customer_phone = trim($_POST['customer_phone']);
$rental_date = $_POST['rental_date'];
$return_date = $_POST['return_date'];
$location = trim($_POST['location']);

// Calculate days and cost
$start = new DateTime($rental_date);
$end = new DateTime($return_date);
$total_days = $start->diff($end)->days;
$price_per_day = (float)$_POST['price_per_day'];
$total_cost = $total_days * $price_per_day;

try {
    // Insert booking with pending approval
    $stmt = $pdo->prepare("INSERT INTO rentals (car_id, user_id, customer_name, customer_email, customer_phone, rental_date, return_date, total_days, total_cost, status, approval_status, payment_status, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', 'pending', NOW())");
    $stmt->execute([$car_id, $user_id, $customer_name, $customer_email, $customer_phone, $rental_date, $return_date, $total_days, $total_cost]);
    
    $rental_id = $pdo->lastInsertId();
    
    // Update car status to rented (temporarily)
    $stmt = $pdo->prepare("UPDATE car SET status = 'rented' WHERE id = ?");
    $stmt->execute([$car_id]);
    
    // Send notification to ALL workers and admins
    $notificationHelper = new NotificationHelper($pdo);
    $notificationHelper->notifyAllStaff(
        'New Booking Request',
        "New booking request from $customer_name for car #$car_id. Please review and approve.",
        'booking',
        'worker/approve_bookings.php'
    );
    
    $_SESSION['booking_success'] = "Booking submitted successfully! Our team will review and send payment instructions within 24 hours.";
    header("Location: my_bookings.php");
    exit();
    
} catch (PDOException $e) {
    $_SESSION['booking_error'] = "Error: " . $e->getMessage();
    header("Location: book_car.php?car_id=$car_id");
    exit();
}
?>