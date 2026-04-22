<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireAdmin();

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['username'], $hashed, $_POST['full_name'], $_POST['email'], $_POST['role']]);
    $success = "User added!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$_POST['delete']]);
    header("Location: users.php");
    exit();
}

$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
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
            <h2> User Management</h2>
        </div>
        <div class="header-right">
            <div class="user-section">
                <span class="username">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                </span>
                <a href="../p_login/logout.php" class="logout-btn"> Logout</a>
            </div>
        </div>
    </div>
</header>

<div class="side-menu" id="adminMenu">
    <img src="../assets/images/logo.png" class="profile-img" style="width:60px;height:60px;border-radius:50%;margin:10px auto;display:block;" alt="Admin">
    <h2> DRIVE ADMIN</h2>
    <a href="dashboard.php" class="btn-nav"> Dashboard</a>
    <a href="manage_car.php" class="btn-nav"> Manage Cars</a>
    <a href="rentals.php" class="btn-nav"> Rentals</a>
    <a href="products.php" class="btn-nav"> Products</a>
    <a href="sales.php" class="btn-nav"> Sales</a>
    <a href="users.php" class="btn-nav"> Users</a>
    <a href="pending_workers.php" class="btn-nav"> Pending Workers</a>
    <a href="../p_login/logout.php" class="btn-nav"> Logout</a>
</div>

<div class="overlay" id="adminOverlay" onclick="closeMenuAdmin()"></div>

<div class="dashboard">

    <div class="main">
        <h1> User Management</h1>
        
        <div class="panel">
            <h3>Add New User</h3>
            <?php if(isset($success)): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <table>
                    <tr><td><input type="text" name="username" placeholder="Username" required></td>
                    <td><input type="password" name="password" placeholder="Password" required></td>
                    <td><input type="text" name="full_name" placeholder="Full Name" required></td>
                    <td><input type="email" name="email" placeholder="Email"></td>
                    <td><select name="role"><option>worker</option><option>admin</option></select></td>
                    <td><button type="submit" name="add_user" style="background:#ff6b00; padding:8px 16px; border:none; color:white; cursor:pointer;">Add User</button></td></tr>
                </table>
            </form>
        </div>
        
        <div class="panel">
            <h3>User List</h3>
            <table>
                <thead><tr><th>Username</th><th>Full Name</th><th>Email</th><th>Role</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['full_name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= $u['role'] ?></td>
                        <td><?php if($u['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="delete" value="<?= $u['id'] ?>">
                                <button type="submit" style="color:red; background:none; border:none; cursor:pointer;">Delete</button>
                            </form>
                        <?php else: ?>Current<?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
