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
    
    // Check if username already exists in users table
    $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->execute([$username]);
    if ($check_stmt->fetch()) {
        header('Location: ../p_login/register_worker.php?error=Username already exists');
        exit();
    }
    
    // Check if email already exists in users table
    $check_email = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->execute([$email]);
    if ($check_email->fetch()) {
        header('Location: ../p_login/register_worker.php?error=Email already exists');
        exit();
    }
    
    // Hash the password after validation
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // First check what tables exist
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        // Look for existing worker-related tables
        $workerTable = null;
        foreach ($tables as $table) {
            if (stripos($table, 'worker') !== false || stripos($table, 'hiring') !== false || stripos($table, 'application') !== false) {
                $workerTable = $table;
                break;
            }
        }
        
        if ($workerTable) {
            // Use existing table
            $stmt = $pdo->prepare("INSERT INTO $workerTable (full_name, username, email, password, phone, address, experience, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        } else {
            // Create hiring table if it doesn't exist
            $pdo->exec("CREATE TABLE hiring (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                full_name VARCHAR(100) NOT NULL,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255),
                phone VARCHAR(20),
                address TEXT,
                experience INT,
                proof_file VARCHAR(255),
                meeting_date DATE,
                status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            
            $stmt = $pdo->prepare("INSERT INTO hiring (full_name, username, email, password, phone, address, experience, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        }
        
        $stmt->execute([$full_name, $username, $email, $hashed_password, $phone, $address, $experience]);
        
        header('Location: ../p_login/login.php?message=Application submitted successfully. Please wait for admin approval.');
        exit();
    } catch (PDOException $e) {
        header('Location: ../p_login/register_worker.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}
?>