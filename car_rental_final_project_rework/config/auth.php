<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'cookie_samesite' => 'Strict',
    'gc_maxlifetime' => 3600,  // 1 hour
]);

// Check if user is logged in
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Check if user is admin
function requireAdmin() {
    checkAuth();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: admin.php');
        exit();
    }
}

// Check if user is worker
function requireWorker() {
    checkAuth();
    if ($_SESSION['role'] !== 'worker') {
        header('Location: ../index.php');
        exit();
    }
}

// Get current user
function getCurrentUser($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
?>