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
        // Create worker_applications table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS worker_applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            experience INT,
            password_hash VARCHAR(255),
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            user_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        $stmt = $pdo->prepare("INSERT INTO worker_applications (username, email, full_name, phone, address, experience, password_hash, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$username, $email, $full_name, $phone, $address, $experience, $hashed_password]);
        header('Location: ../p_login/login.php?message=Application submitted successfully. Please wait for admin approval.');
        exit();
    } catch (PDOException $e) {
        header('Location: ../p_login/register_worker.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}
?>