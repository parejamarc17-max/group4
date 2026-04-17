<?php
session_start();
require_once "config/database.php";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register_admin.php");
    exit();
}

// Get and sanitize inputs
$username = trim($_POST['username']);
$password = $_POST['password'];
$full_name = trim($_POST['full_name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$address = trim($_POST['address']);

// Validate required fields
if (empty($username) || empty($password) || empty($full_name)) {
    $_SESSION['error'] = "Username, password, and full name are required!";
    header("Location: register_admin.php");
    exit();
}

try {
    // Check if username or email already exists
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->execute([$username, $email]);
    
    if ($check->rowCount() > 0) {
        $_SESSION['error'] = "Username or email already exists!";
        header("Location: register_admin.php");
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert into users table as admin
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, 'admin')");
    $stmt->execute([$username, $hashed_password, $full_name, $email]);
    
    $user_id = $pdo->lastInsertId();
    
    // Check if customers table exists, also save phone/address there
    $check = $pdo->query("SHOW TABLES LIKE 'customers'");
    if ($check && $check->rowCount() > 0) {
        $stmt = $pdo->prepare("INSERT INTO customers (user_id, phone, address) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $phone, $address]);
    }
    
    $_SESSION['success'] = "Admin account created successfully! Please login.";
    header('Location: login.php');
    exit();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: register_admin.php");
    exit();
}
?>