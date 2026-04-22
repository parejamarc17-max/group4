<?php
session_start();
require_once '../config/database.php';

// If already logged in → redirect based on role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } elseif ($_SESSION['role'] === 'worker') {
        header("Location: ../worker.php");
    } else {
        header("Location: ../worker/dashboard.php");
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
<title>Login - CarRent System</title>
<link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
<div class="login-container">
    <div class="login-header">
        <img src="../assets/images/logo.png" class="logo">
        <h1>CarRent System</h1>
        <p>Register First Before Login</p>
    </div>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <form method="POST" action="../processor/login_process.php">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="form-group">
            <label>Username or Email</label>
            <input type="text" name="username" required placeholder="Enter your username or email">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter your password">
        </div>
        <button type="submit">Login</button>
    </form>

    <p style="text-align:center; margin-top:20px; color:#666;">
        <a href="register.php" style="color: #667eea; font-weight: bold;">Register an Account</a>
    </p>
    <!-- ✅ Back link below Register -->
    <p style="text-align:center; margin-top:10px; color:#666;">
    <a href="../index.php" style="color:#ff6b00; font-weight:bold;">← Back to Home</a>
    </p>
</div>
</body>
</html>