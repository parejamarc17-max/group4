<?php
session_start();
require_once '../config/database.php';

// If already logged in as admin → go to admin dashboard
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: ../admin/dashboard.php");
    exit();
} elseif (isset($_SESSION['user_id'])) {
    // If logged in but as different role, redirect to appropriate dashboard
    if ($_SESSION['role'] === 'worker') {
        header("Location: ../worker.php");
    }
    exit();
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - CarRent System</title>
<link rel="stylesheet" href="../assets/css/login.css">
<style>
    .login-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .login-header h1 {
        color: #fff;
    }
    .login-header p {
        color: #e0e0e0;
    }
    .admin-badge {
        display: inline-block;
        background: #ff6b00;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: bold;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }
</style>
</head>

<body>
<div class="login-container">
    <div class="login-header">
        <img src="../assets/images/download (4).jpg" class="logo">
        <div class="admin-badge">🔐 ADMIN ACCESS</div>
        <h1>CarRent Admin Panel</h1>
        <p>Administrator Login</p>
    </div>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <form method="POST" action="../processor/login_process.php">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="role" value="admin">
        
        <div class="form-group">
            <label>Admin Username</label>
            <input type="text" name="username" required placeholder="Enter admin username">
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter password">
        </div>
        
        <button type="submit" style="background: #667eea;">Login as Admin</button>
    </form>

    <p style="text-align:center; margin-top:15px;">
        <a href="login.php">← Back to Main Login</a> | 
        <a href="worker_login.php">Worker Login →</a>
    </p>
</div>
</body>
</html>
