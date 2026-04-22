<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $experience = $_POST['experience'];

    try {
        // Insert into worker_applications with username, email, and hashed password (needed for approval process)
        $stmt = $pdo->prepare("INSERT INTO worker_applications (full_name, phone, address, experience, status, username, email, password) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?)");
        $stmt->execute([$full_name, $phone, $address, $experience, $username, $email, $password]);
        header('Location: ../p_login/login.php?message=Application submitted, awaiting approval');
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>