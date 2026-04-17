<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid request';
        header('Location: car.php');
        exit();
    }
    $car_id = (int)$_POST['car_id'];
    if ($car_id <= 0) {
        $_SESSION['error'] = 'Invalid car ID';
        header('Location: car.php');
        exit();
    }
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone']);
    
    if (empty($customer_name) || empty($customer_email) || empty($customer_phone)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: car.php');
        exit();
    }
    $rental_days = (int)$_POST['rental_days'];
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    
    // Validate dates
    if (!DateTime::createFromFormat('Y-m-d', $pickup_date) || 
        !DateTime::createFromFormat('Y-m-d', $return_date)) {
        $_SESSION['error'] = 'Invalid date format';
        header('Location: car.php');
        exit();
    }
    
    if (strtotime($pickup_date) >= strtotime($return_date)) {
        $_SESSION['error'] = 'Return date must be after pickup date';
        header('Location: car.php');
        exit();
    }
    
    if (strtotime($pickup_date) < strtotime(date('Y-m-d'))) {
        $_SESSION['error'] = 'Pickup date cannot be in the past';
        header('Location: car.php');
        exit();
    }
    
    // Calculate actual days from dates
    $calculated_days = (strtotime($return_date) - strtotime($pickup_date)) / (60 * 60 * 24);
    
    if ($calculated_days != $rental_days) {
        $_SESSION['error'] = 'Number of days does not match the selected dates';
        header('Location: car.php');
        exit();
    }
    
    // Validate email
    if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email address';
        header('Location: car.php');
        exit();
    }
    
    // Validate phone (basic)
    if (!preg_match('/^[\d\s\-\+\(\)]{10,15}$/', $customer_phone)) {
        $_SESSION['error'] = 'Invalid phone number';
        header('Location: car.php');
        exit();
    }
    
    if ($rental_days < 1) {
        $_SESSION['error'] = 'Number of days must be at least 1';
        header('Location: car.php');
        exit();
    }

    $stmt = $pdo->prepare("SELECT price_per_day, status FROM cars WHERE id = ?");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();
    
    if (!$car) {
        $_SESSION['error'] = 'Car not found';
        header('Location: car.php');
        exit();
    }
    
    if ($car['status'] !== 'available') {
        $_SESSION['error'] = 'Car is no longer available';
        header('Location: car.php');
        exit();
    }
    
    $total_cost = $rental_days * $car['price_per_day'];
    $user_id = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO rentals (user_id, car_id, customer_name, customer_email, customer_phone, rental_date, return_date, total_days, total_cost, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$user_id, $car_id, $customer_name, $customer_email, $customer_phone, $pickup_date, $return_date, $rental_days, $total_cost]);
        $booking_id = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("UPDATE cars SET status = 'rented' WHERE id = ?");
        $stmt->execute([$car_id]);
        
        $pdo->commit();
        $_SESSION['booking_id'] = $booking_id;
        header("Location: booking_confirmation.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Booking failed. Please try again.';
        header("Location: car.php");
        exit();
    }
} else {
    header("Location: car.php");
    exit();
}
?>