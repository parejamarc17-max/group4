<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireAdmin();

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = '';
$error = '';

// Handle adding new worker manually
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['username'], $hashed, $_POST['full_name'], $_POST['email'], 'worker']);
        $success = "Worker added successfully!";
    } catch (PDOException $e) {
        $error = "Error adding worker: " . $e->getMessage();
    }
}

// Handle firing/deleting worker
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_worker'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $worker_id = $_POST['delete_worker'];
    
    // Prevent deleting yourself
    if ($worker_id == $_SESSION['user_id']) {
        $error = "You cannot delete your own account!";
    } else {
        try {
            // Get worker info to update worker_applications if exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'worker'");
            $stmt->execute([$worker_id]);
            $worker = $stmt->fetch();
            
            if ($worker) {
                // Mark worker_applications as deleted (if exists)
                $stmt = $pdo->prepare("UPDATE worker_applications SET status = 'deleted' WHERE user_id = ?");
                $stmt->execute([$worker_id]);
                
                // Delete from users table
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$worker_id]);
                
                $success = "Worker has been fired successfully!";
                // Redirect to refresh the page
                header("Location: workers.php?message=" . urlencode($success));
                exit();
            } else {
                $error = "Worker not found or is not a worker account!";
            }
        } catch (PDOException $e) {
            $error = "Error deleting worker: " . $e->getMessage();
        }
    }
}

// Get all workers (from users table with role='worker')
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'worker' ORDER BY id DESC");
$workers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Worker Management</title>
       <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    </style>
</head>
<body>

<header>
    <div class="custom-header">
        <div class="header-left">
            <div class="hamburger-btn" onclick="toggleMenuAdmin()" title="Menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <h2>👥 Worker Management</h2>
        </div>
        <div class="header-right">
            <div class="user-section">
                <span class="username">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                </span>
                <a href="../p_login/logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </div>
    </div>
</header>

<div class="side-menu" id="adminMenu">
    <img src="../assets/images/download (4).jpg" class="profile-img" style="width:60px;height:60px;border-radius:50%;margin:10px auto;display:block;" alt="Admin">
    <h2>🚗 DRIVE ADMIN</h2>
    <a href="dashboard.php" class="btn-nav">📊 Dashboard</a>
    <a href="car_list.php" class="btn-nav">🚘 Automobile</a>
    <a href="rentals.php" class="btn-nav">📅 Rentals</a>
    <a href="products.php" class="btn-nav">📦 Products</a>
    <a href="sales.php" class="btn-nav">💰 Sales</a>
    <a href="workers.php" class="btn-nav">👥 Workers</a>
    <a href="pending_workers.php" class="btn-nav">👷 Pending Workers</a>
    <a href="../p_login/logout.php" class="btn-nav">🚪 Logout</a>
</div>

<div class="overlay" id="adminOverlay" onclick="closeMenuAdmin()"></div>

<div class="dashboard">

    <div class="main">
        <h1>👥 Worker Management</h1>
        
        <?php if($success): ?>
            <div style="padding:15px; background:#4caf50; color:white; border-radius:5px; margin-bottom:20px;">
                ✓ <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div style="padding:15px; background:#d32f2f; color:white; border-radius:5px; margin-bottom:20px;">
                ✗ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="panel">
            <h3>Add New Worker</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <table>
                    <tr>
                        <td><input type="text" name="username" placeholder="Username" required></td>
                        <td><input type="password" name="password" placeholder="Password" required></td>
                        <td><input type="text" name="full_name" placeholder="Full Name" required></td>
                        <td><input type="email" name="email" placeholder="Email" required></td>
                        <td><button type="submit" name="add_user" style="background:#ff6b00; padding:8px 16px; border:none; color:white; cursor:pointer;">Add Worker</button></td>
                    </tr>
                </table>
            </form>
        </div>
        
        <div class="panel">
            <h3>Active Workers</h3>
            <?php if (empty($workers)): ?>
                <p style="text-align:center; color:#999; padding:20px;">No workers currently active.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($workers as $worker): ?>
                            <tr>
                                <td><?= htmlspecialchars($worker['username']) ?></td>
                                <td><?= htmlspecialchars($worker['full_name']) ?></td>
                                <td><?= htmlspecialchars($worker['email'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($worker['phone'] ?? 'N/A') ?></td>
                                <td><?= date('M d, Y', strtotime($worker['created_at'])) ?></td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('🚨 WARNING: Are you sure you want to FIRE this worker?\n\nThis action cannot be undone!')">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="delete_worker" value="<?= $worker['id'] ?>">
                                        <button type="submit" class="fire-warning" style="background:none; border:none; cursor:pointer; text-decoration:underline;">🔥 Fire Worker</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
function toggleMenuAdmin() {
    const menu = document.getElementById("adminMenu");
    const overlay = document.getElementById("adminOverlay");
    const hamburger = document.querySelector('.hamburger-btn');

    if (!menu) return;

    if (menu.classList.contains("active")) {
        menu.classList.remove("active");
        if (overlay) overlay.classList.remove("active");
        if (hamburger) hamburger.classList.remove('active');
    } else {
        menu.classList.add("active");
        if (overlay) overlay.classList.add("active");
        if (hamburger) hamburger.classList.add('active');
    }
}

function closeMenuAdmin() {
    const menu = document.getElementById("adminMenu");
    const overlay = document.getElementById("adminOverlay");
    const hamburger = document.querySelector('.hamburger-btn');

    if (menu) menu.classList.remove("active");
    if (overlay) overlay.classList.remove("active");
    if (hamburger) hamburger.classList.remove('active');
}
</script>

</body>
</html>
