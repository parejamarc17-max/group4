<?php
session_start();
require_once '../config/database.php';

// Determine user role before clearing session
$user_role = $_SESSION['role'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

// Log worker logout activity
if ($user_id && $user_role === 'worker') {
    try {
        $stmt = $pdo->prepare("INSERT INTO worker_activity (user_id, activity_type) VALUES (?, 'logout')");
        $stmt->execute([$user_id]);
    } catch (Exception $e) {
        // Silently fail - don't break logout
    }
}

// Destroy session for all user types (admin, worker, customer)
session_unset();
session_destroy();

// Redirect to appropriate login page based on role
if ($user_role === 'admin') {
    header("Location: admin_login.php");
} elseif ($user_role === 'worker') {
    header("Location: worker_login.php");
} else {
    // Default for customers and unknown roles
    header("Location: login.php");
}
exit();

