<?php
session_start();
require_once 'config/database.php';

// FORCE LOGIN FIRST
if (!isset($_SESSION['user_id'])) {
    header("Location: p_login/login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarRent - Premium Car Rental</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<header>
    <div class="custom-header">
        <div class="header-left">

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="hamburger-btn hamburger" onclick="toggleMenu()" title="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            <?php endif; ?>

            <h2>CarRent</h2>
        </div>

        <div class="header-right">
            <nav>
                <a href="worker.php">Home</a>
                <a href="worker/worker_dashboard.php">Dashboard</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-section">
                    <span class="username">
                        <?= htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                    </span>
                
                </div>
            <?php endif; ?>

        </div>
     </div>
</header>


<!-- ✅ WORKER SIDE MENU -->
<div class="side-menu" id="sideMenu">
    <img src="assets/images/download (4).jpg" class="profile-img" style="width:60px;height:60px;border-radius:50%;margin:10px auto;display:block;">
    <h2>🚗 DRIVE WORKER</h2>
    <a href="worker.php" class="btn-nav" onclick="closeMenus()">← Home</a>
    <a href="worker/worker_dashboard.php" class="btn-nav" onclick="closeMenus()">📊 Dashboard</a>
    <a href="worker/worker_manage_car.php" class="btn-nav" onclick="closeMenus()">🚘 Manage Cars</a>
    <a href="worker/worker_rentals.php" class="btn-nav" onclick="closeMenus()">📅 Rentals</a>
    <a href="worker/worker_products.php" class="btn-nav" onclick="closeMenus()">📦 Products</a>
    <a href="worker/worker_sales.php" class="btn-nav" onclick="closeMenus()">💰 Sales</a>
    <a href="p_login/logout.php" class="btn-nav" onclick="closeMenus()">🚪 Logout</a>
</div>

<!-- ✅ OVERLAY FOR WORKER -->
<div class="overlay" onclick="closeMenus()"></div>

<section class="hero">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Worker'); ?></h1>
    <p style="font-size: 1.2rem;">Manage your work assignments and vehicle operations</p>
    <button class="btn-vc" onclick="document.getElementById('sideMenu').classList.add('active'); document.getElementById('overlay').classList.add('active');">📋 View Menu</button>
</section>

<section class="cars">
    <h2 style="text-align:center; margin-bottom: 30px;">📊 Quick Actions</h2>

<?php
// Get some quick stats for the worker
$stmt = $pdo->query("SELECT COUNT(*) as count FROM cars WHERE status = 'available'");
$available_cars = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM rentals WHERE status = 'active'");
$active_rentals = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE stock > 0");
$products_count = $stmt->fetch()['count'];
?>

    <div class="car-card">
        <h3>🚘 Manage Cars</h3>
        <p><strong><?= $available_cars ?></strong> Available</p>
        <button onclick="window.location.href='worker/worker_manage_car.php'">Go to Management</button>
    </div>

    <div class="car-card">
        <h3>📅 Active Rentals</h3>
        <p><strong><?= $active_rentals ?></strong> Active</p>
        <button onclick="window.location.href='worker/worker_rentals.php'">View Rentals</button>
    </div>

    <div class="car-card">
        <h3>📦 Products</h3>
        <p><strong><?= $products_count ?></strong> Available</p>
        <button onclick="window.location.href='worker/worker_products.php'">Manage Products</button>
    </div>

    <div class="car-card">
        <h3>💰 Sales</h3>
        <p>Track and manage sales</p>
        <button onclick="window.location.href='worker/worker_sales.php'">View Sales</button>
    </div>

</section>

<footer>
    <p>&copy; <?= date('Y') ?> CarRent System. All rights reserved.</p>
</footer>

<script>
function toggleMenu() {
    const menu = document.getElementById("sideMenu");
    const overlay = document.getElementById("overlay");
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

function closeMenus() {
    const menu = document.getElementById("sideMenu");
    const overlay = document.getElementById("overlay");
    const hamburger = document.querySelector('.hamburger-btn');

    if (menu) menu.classList.remove("active");
    if (overlay) overlay.classList.remove("active");
    if (hamburger) hamburger.classList.remove('active');
}
</script>

</body>
</html>