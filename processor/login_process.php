<?php
session_start();
require_once '../config/database.php';

// CSRF token check
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die("Invalid CSRF token");
}

// Hardcoded credentials for testing only
$valid_users = [
    'admin' => ['password' => 'admin123', 'role' => 'admin'],
    'worker' => ['password' => 'worker123', 'role' => 'worker']
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $submitted_role = $_POST['role'] ?? ''; // Get role from login form

    // Check database first by username or email
    $stmt = $pdo->prepare("SELECT id, password, role, full_name FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // VALIDATE: Ensure submitted role matches actual user role
        if ($submitted_role && $submitted_role !== $user['role']) {
            // User tried to login through wrong portal (e.g., admin username through worker login)
            $error_page = $submitted_role === 'admin' ? '../p_login/admin_login.php' : '../p_login/worker_login.php';
            header("Location: {$error_page}?error=Invalid credentials for this role");
            exit();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $user['full_name'] ?? '';

        if ($user['role'] == 'admin') {
            header('Location: ../admin/dashboard.php');
        } elseif ($user['role'] == 'worker') {
            header('Location: ../worker.php');
        }
        exit();
    }

    // Fallback to hardcoded
    if (isset($valid_users[$username]) && $valid_users[$username]['password'] == $password) {
        // VALIDATE: Ensure submitted role matches hardcoded user role
        if ($submitted_role && $submitted_role !== $valid_users[$username]['role']) {
            // User tried to login through wrong portal
            $error_page = $submitted_role === 'admin' ? '../p_login/admin_login.php' : '../p_login/worker_login.php';
            header("Location: {$error_page}?error=Invalid credentials for this role");
            exit();
        }

        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = $valid_users[$username]['role'];
        $_SESSION['username'] = $username;

        if ($valid_users[$username]['role'] == 'admin') {
            header('Location: ../admin/dashboard.php');
        } elseif ($valid_users[$username]['role'] == 'worker') {
            header('Location: ../worker.php');
        } 
        exit();
    }

    // If no match, redirect back with error to the unified login page by default.
    $error_page = '../p_login/login.php';
    if ($submitted_role === 'admin') {
        $error_page = '../p_login/admin_login.php';
    } elseif ($submitted_role === 'worker') {
        $error_page = '../p_login/worker_login.php';
    }
    header("Location: {$error_page}?error=Invalid username or password");
    exit();
}

header('Location: ../p_login/login.php?error=1');
exit();
?>
