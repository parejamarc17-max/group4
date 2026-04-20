<?php
require_once "../config/database.php";

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];

// Insert into users
$stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, 'customer')");
$stmt->execute([$username, $password, $full_name, $email]);

$user_id = $pdo->lastInsertId();

// If a customers table exists, also save phone/address there.
$check = $pdo->query("SHOW TABLES LIKE 'customers'");
if ($check && $check->rowCount() > 0) {
    $stmt = $pdo->prepare("INSERT INTO customers (user_id, phone, address) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $phone, $address]);
}

header('Location: ../p\ login/login.php?message=Registered successfully');
exit();
?>