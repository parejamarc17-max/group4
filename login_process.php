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
    $submitted_role = $_POST['role'] ?? ''; // Get role from login form (may be empty for unified login)

    // Check database first by username or email
    $stmt = $pdo->prepare("SELECT id, password, role, full_name FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // VALIDATE: Ensure submitted role matches actual user role (only if role was submitted)
        if ($submitted_role && $submitted_role !== $user['role']) {
            // User tried to login through wrong portal (e.g., admin username through worker login)
            $error_page = '../p_login/login.php';
            header("Location: {$error_page}?error=Invalid credentials for this role");
            exit();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $user['full_name'] ?? '';

        // Log worker login activity
        if ($user['role'] == 'worker') {
            try {
                // Create worker_activity table if it doesn't exist
                $create_table_sql = "CREATE TABLE IF NOT EXISTS worker_activity (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  user_id INT NOT NULL,
                  activity_type ENUM('login', 'logout') NOT NULL,
                  activity_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  ip_address VARCHAR(45) DEFAULT NULL,
                  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                  INDEX idx_user_id (user_id),
                  INDEX idx_activity_time (activity_time)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
                $pdo->exec($create_table_sql);
                
                // Insert login activity
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
                $stmt = $pdo->prepare("INSERT INTO worker_activity (user_id, activity_type, ip_address) VALUES (?, 'login', ?)");
                $stmt->execute([$user['id'], $ip_address]);
            } catch(PDOException $e) {
                // Silently continue even if activity logging fails
                error_log("Worker activity logging failed: " . $e->getMessage());
            }
        }

        if ($user['role'] == 'admin') {
            header('Location: ../admin/dashboard.php');
        } elseif ($user['role'] == 'worker') {
            header('Location: ../worker/dashboard.php');
        } elseif ($user['role'] == 'customer') {
            header('Location: ../customer/dashboard.php');
        }
        exit();
    }

    // Fallback to hardcoded
    if (isset($valid_users[$username]) && $valid_users[$username]['password'] == $password) {
        // VALIDATE: Ensure submitted role matches hardcoded user role (only if role was submitted)
        if ($submitted_role && $submitted_role !== $valid_users[$username]['role']) {
            // User tried to login through wrong portal
            $error_page = '../p_login/login.php';
            header("Location: {$error_page}?error=Invalid credentials for this role");
            exit();
        }

        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = $valid_users[$username]['role'];
        $_SESSION['username'] = $username;

        if ($valid_users[$username]['role'] == 'admin') {
            header('Location: ../admin/dashboard.php');
        } elseif ($valid_users[$username]['role'] == 'worker') {
            header('Location: ../worker/dashboard.php');
        } elseif ($valid_users[$username]['role'] == 'customer') {
            header('Location: ../customer/dashboard.php');
        } 
        exit();
    }

    // If no match, redirect back with error to the unified login page by default.
    $error_page = '../p_login/login.php';
    if ($submitted_role === 'admin') {
        $error_page = '../p_login/login.php';
    } elseif ($submitted_role === 'worker') {
        $error_page = '../p_login/login.php';
    }
    header("Location: {$error_page}?error=Invalid username or password");
    exit();
}

header('Location: ../p_login/login.php?error=1');
exit();
?>
