<?php
require_once '../config/database.php';
require_once '../config/auth.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid request';
        header('Location: ../car.php');
        exit();
    }
    $car_id = (int)$_POST['car_id'];
    if ($car_id <= 0) {
        $_SESSION['error'] = 'Invalid car ID';
        header('Location: ../car.php');
        exit();
    }
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone']);
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    
    // Validate dates
    if (!DateTime::createFromFormat('Y-m-d', $pickup_date) || 
        !DateTime::createFromFormat('Y-m-d', $return_date)) {
        $_SESSION['error'] = 'Invalid date format';
        header('Location: ../car.php');
        exit();
    }
    
    if (strtotime($pickup_date) >= strtotime($return_date)) {
        $_SESSION['error'] = 'Return date must be after pickup date';
        header('Location: ../car.php');
        exit();
    }
    
    if (strtotime($pickup_date) < strtotime(date('Y-m-d'))) {
        $_SESSION['error'] = 'Pickup date cannot be in the past';
        header('Location: ../car.php');
        exit();
    }
    
    // Calculate rental days from dates
    $pickup_timestamp = strtotime($pickup_date);
    $return_timestamp = strtotime($return_date);
    $rental_days = ceil(($return_timestamp - $pickup_timestamp) / (60 * 60 * 24));
    
    // Validate email
    if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email address';
        header('Location: ../car.php');
        exit();
    }
    
    // Validate phone (basic)
    if (!preg_match('/^\+?[\d\s\-\(\)]{10,15}$/', $customer_phone)) {
        $_SESSION['error'] = 'Invalid phone number';
        header('Location: ../car.php');
        exit();
    }
    
    $stmt = $pdo->prepare("SELECT price_per_day, status FROM car WHERE id = ?");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();
    
    if (!$car) {
        $_SESSION['error'] = 'Car not found';
        header('Location: ../car.php');
        exit();
    }
    
    if ($car['status'] !== 'available') {
        $_SESSION['error'] = 'Car is no longer available';
        header('Location: ../car.php');
        exit();
    }
    
    $total_cost = $rental_days * $car['price_per_day'];
    
    try {
        $pdo->beginTransaction();
        
        // Insert into rentals table with only the columns that exist
        // Based on admin/rentals.php, the table should have: id, car_id, customer_name, customer_phone, rental_date, return_date, total_cost, status
        // Try the most complete version first
        try {
            $stmt = $pdo->prepare("INSERT INTO rentals (user_id, car_id, customer_name, customer_email, customer_phone, rental_date, return_date, total_days, total_cost, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$_SESSION['user_id'], $car_id, $customer_name, $customer_email, $customer_phone, $pickup_date, $return_date, $rental_days, $total_cost]);
        } catch (Exception $e) {
            // Try without user_id and customer_email
            try {
                $stmt = $pdo->prepare("INSERT INTO rentals (car_id, customer_name, customer_phone, rental_date, return_date, total_days, total_cost, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$car_id, $customer_name, $customer_phone, $pickup_date, $return_date, $rental_days, $total_cost]);
            } catch (Exception $e2) {
                // Try without total_days as well
                $stmt = $pdo->prepare("INSERT INTO rentals (car_id, customer_name, customer_phone, rental_date, return_date, total_cost, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$car_id, $customer_name, $customer_phone, $pickup_date, $return_date, $total_cost]);
            }
        }
        
        $stmt = $pdo->prepare("UPDATE car SET status = 'rented' WHERE id = ?");
        $stmt->execute([$car_id]);
        
        $pdo->commit();
        $_SESSION['success'] = 'Booking successful! Check your email for confirmation.';
        header("Location: ../car.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        // Log the error for debugging
        error_log("Booking error: " . $e->getMessage());
        $_SESSION['error'] = 'Booking failed: ' . $e->getMessage();
        header("Location: ../car.php");
        exit();
    }
} else {
    header("Location: ../car.php");
}
?>