<?php
require_once "../config/database.php";

$username = $_POST['username'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];

// Validate password confirmation
if ($password !== $confirm_password) {
    header('Location: ../p_login/register_customer.php?error=Passwords do not match');
    exit();
}

// Check if username already exists
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);
$existing_user = $stmt->fetch();

if ($existing_user) {
    if ($existing_user['username'] === $username) {
        header('Location: ../p_login/register_customer.php?error=Username already exists');
        exit();
    } else {
        header('Location: ../p_login/register_customer.php?error=Email already registered');
        exit();
    }
}

// Hash the password after validation
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert into users
$stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, 'customer')");
$stmt->execute([$username, $hashed_password, $full_name, $email]);

$user_id = $pdo->lastInsertId();

// If a customers table exists, also save phone/address there.
$check = $pdo->query("SHOW TABLES LIKE 'customers'");
if ($check && $check->rowCount() > 0) {
    $stmt = $pdo->prepare("INSERT INTO customers (user_id, phone, address) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $phone, $address]);
}

header('Location: ../p_login/login.php?message=Registered successfully');
exit();
?>