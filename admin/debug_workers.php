<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireAdmin();

echo "<h2>Debug Worker Database</h2>";

// Check users table
echo "<h3>Users Table (role = 'worker'):</h3>";
try {
    $stmt = $pdo->query("SELECT id, username, full_name, email, phone, role, created_at FROM users WHERE role = 'worker' ORDER BY created_at DESC");
    $workers = $stmt->fetchAll();
    
    if (empty($workers)) {
        echo "<p>No workers found in users table.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Created</th></tr>";
        foreach ($workers as $worker) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($worker['id']) . "</td>";
            echo "<td>" . htmlspecialchars($worker['username']) . "</td>";
            echo "<td>" . htmlspecialchars($worker['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($worker['email']) . "</td>";
            echo "<td>" . htmlspecialchars($worker['phone']) . "</td>";
            echo "<td>" . htmlspecialchars($worker['role']) . "</td>";
            echo "<td>" . htmlspecialchars($worker['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<p>Error querying users table: " . $e->getMessage() . "</p>";
}

// Check worker_applications table
echo "<h3>Worker Applications Table:</h3>";
try {
    $stmt = $pdo->query("SELECT id, username, email, full_name, phone, address, experience, status, user_id, created_at FROM worker_applications ORDER BY created_at DESC");
    $applications = $stmt->fetchAll();
    
    if (empty($applications)) {
        echo "<p>No applications found in worker_applications table.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Phone</th><th>Status</th><th>User ID</th><th>Created</th></tr>";
        foreach ($applications as $app) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($app['id']) . "</td>";
            echo "<td>" . htmlspecialchars($app['username']) . "</td>";
            echo "<td>" . htmlspecialchars($app['email']) . "</td>";
            echo "<td>" . htmlspecialchars($app['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($app['phone']) . "</td>";
            echo "<td>" . htmlspecialchars($app['status']) . "</td>";
            echo "<td>" . htmlspecialchars($app['user_id']) . "</td>";
            echo "<td>" . htmlspecialchars($app['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<p>Error querying worker_applications table: " . $e->getMessage() . "</p>";
}

// Check if tables exist
echo "<h3>Table Structure Check:</h3>";
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tables in database: " . implode(", ", $tables) . "</p>";
    
    if (in_array('users', $tables)) {
        echo "<p>✅ Users table exists</p>";
        $columns = $pdo->query("DESCRIBE users")->fetchAll();
        echo "<p>Users table columns: " . implode(", ", array_column($columns, 'Field')) . "</p>";
    }
    
    if (in_array('worker_applications', $tables)) {
        echo "<p>✅ Worker_applications table exists</p>";
        $columns = $pdo->query("DESCRIBE worker_applications")->fetchAll();
        echo "<p>Worker_applications table columns: " . implode(", ", array_column($columns, 'Field')) . "</p>";
    }
} catch (PDOException $e) {
    echo "<p>Error checking tables: " . $e->getMessage() . "</p>";
}

echo "<br><a href='worker_list.php'>← Back to Worker List</a>";
?>
