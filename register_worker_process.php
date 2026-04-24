<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $experience = $_POST['experience'];
    
    // Validate password confirmation
    if ($password !== $confirm_password) {
        header('Location: ../p_login/register_worker.php?error=Passwords do not match');
        exit();
    }
    
    // Hash the password after validation
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO worker_applications (full_name, phone, address, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$full_name, $phone, $address]);
        header('Location: ../p_login/login.php?message=Application submitted, awaiting approval');
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>