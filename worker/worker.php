<?php
require_once '../config/auth.php';
require_once '../config/database.php';
checkAuth();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM car");
$total_cars = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM rentals WHERE status = 'active'");
$active_rentals = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(total_cost) as revenue FROM rentals WHERE status = 'completed'");
$revenue = $stmt->fetch()['revenue'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as total FROM rentals");
$total_rentals = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT r.*, c.car_name FROM rentals r LEFT JOIN car c ON r.car_id = c.id ORDER BY r.id DESC LIMIT 5");
$recent_rentals = $stmt->fetchAll();
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    die("Access denied");
}

// stats
$cars = $pdo->query("SELECT COUNT(*) FROM car")->fetchColumn();
$rentals = $pdo->query("SELECT COUNT(*) FROM rentals")->fetchColumn();
$products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$sales = $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard</title>
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
            <h2>🚗 Admin Dashboard</h2>
        </div>
        <div class="header-right">
            <div class="user-section">
                <span class="username">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                </span>
            </div>
        </div>
    </div>
</header>

<div class="side-menu" id="adminMenu">
    <img src="../assets/images/logo.png" class="profile-img" style="width:60px;height:60px;border-radius:50%;margin:10px auto;display:block;" alt="Admin">
    <h2>🚗 DRIVE ADMIN</h2>
    <a href="workers_dashboard.php" class="btn-nav">📊 Dashboard</a>
    <a href="manage_car.php" class="btn-nav">🚘 Manage Cars</a>
    <a href="rentals.php" class="btn-nav">📅 Rentals</a>
    <a href="products.php" class="btn-nav">📦 Products</a>
    <a href="sales.php" class="btn-nav">💰 Sales</a>
    <a href="customer_list.php" class="btn-nav">👷 Customer List</a>
    <a href="../p_login/logout.php" class="btn-nav">🚪 Logout</a>
</div>

<div class="overlay" id="adminOverlay" onclick="closeMenuAdmin()"></div>

<div class="dashboard">
        <h1>Dashboard Overview</h1>
        <p>Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!</p>

        <div class="cards">
            <div class="card">🚗 Total Cars<br><h2><?= $total_cars ?></h2></div>
            <div class="card">🚘 Active Rentals<br><h2><?= $active_rentals ?></h2></div>
            <div class="card">💰 Revenue<br><h2>$<?= number_format($revenue, 2) ?></h2></div>
            <div class="card">📊 Total Rentals<br><h2><?= $total_rentals ?></h2></div>
        </div>

        <div class="panel">
            <h2>📋 Recent Rentals</h2>
            <table>
                <thead>
                    <tr><th>Customer</th><th>Car</th><th>Rental Date</th><th>Return Date</th><th>Status</th><th>Amount</th></tr>
                </thead>
                <tbody>
                    <?php foreach($recent_rentals as $rental): ?>
                    <tr>
                        <td><?= htmlspecialchars($rental['customer_name']) ?></td>
                        <td><?= htmlspecialchars($rental['car_name'] ?? 'N/A') ?></td>
                        <td><?= $rental['rental_date'] ?></td>
                        <td><?= $rental['return_date'] ?></td>
                        <td><span class="status-<?= $rental['status'] ?>"><?= $rental['status'] ?></span></td>
                        <td>$<?= number_format($rental['total_cost'], 2) ?></td>
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

    if (menu) {
        menu.classList.remove("active");
        if (overlay) overlay.classList.remove("active");
        if (hamburger) hamburger.classList.remove('active');
    }
}
</script>

</body>
</html>
    <title>Worker Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<h1>👷 Worker Dashboard</h1>

<div class="cards">
    <div class="card">🚗 Cars: <?= $cars ?></div>
    <div class="card">📅 Rentals: <?= $rentals ?></div>
    <div class="card">📦 Products: <?= $products ?></div>
    <div class="card">💰 Sales: <?= $sales ?></div>
</div>

<a href="../p_login/logout.php">Logout</a>

</body>
</html>
</html>
