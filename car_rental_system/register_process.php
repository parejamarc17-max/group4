<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register_worker.php");
    exit();
}

$username   = trim($_POST['username']);
$full_name  = trim($_POST['full_name']);
$email      = trim($_POST['email']);
$password   = $_POST['password'];
$phone      = trim($_POST['phone']);
$address    = trim($_POST['address']);
$experience = trim($_POST['experience']);

if (empty($username) || empty($full_name) || empty($email) || empty($password)) {
    $_SESSION['error'] = "Please fill all required fields!";
    header("Location: register_worker.php");
    exit();
}

try {
    // Check duplicate user
    $check = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=?");
    $check->execute([$username, $email]);

    if ($check->rowCount() > 0) {
        $_SESSION['error'] = "Username or email already exists!";
        header("Location: register_worker.php");
        exit();
    }

    // Create user FIRST (role = worker but pending approval)
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (username, full_name, email, password, role)
        VALUES (?, ?, ?, ?, 'worker')
    ");

    $stmt->execute([$username, $full_name, $email, $hashed]);
    $user_id = $pdo->lastInsertId();

    // Insert application
    $app = $pdo->prepare("
        INSERT INTO worker_applications
        (user_id, full_name, phone, address, experience, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");

    $app->execute([$user_id, $full_name, $phone, $address, $experience]);

    $_SESSION['success'] = "Application submitted! Wait for admin approval.";
    header("Location: login.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: register_worker.php");
    exit();
}
?>