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
                <a href="index.php">Home</a>
                <a href="car.php">Cars</a>

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


<?php if ($_SESSION['role'] === 'admin'): ?>

<!-- ✅ SIDE MENU FOR ADMIN -->
<div class="side-menu" id="sideMenu">

    <div class="menu-header">
        <h3>Dashboard Menu</h3>
    </div>

    <a href="admin/dashboard.php">📊 Dashboard</a>
    <a href="admin/manage_car.php">🚘 Manage Cars</a>
    <a href="admin/rentals.php">📅 Rentals</a>
    <a href="admin/products.php">📦 Products</a>
    <a href="admin/sales.php">💰 Sales</a>
    <a href="admin/users.php">👥 Users</a>
    <a href="p_login/logout.php">🚪 Logout</a>

</div>

<!-- ✅ OVERLAY FOR ADMIN -->
<div class="overlay" onclick="closeMenus()"></div>

<?php endif; ?>

<section class="hero">
    <h1>Drive Your Dream Car Today</h1>
    <p style="font-size: 1.2rem;">Premium cars available at your fingertips</p>
    <button class="btn-vc" onclick="window.location.href='car.php'">View Cars</button>
</section>

<section class="cars">

<?php
$stmt = $pdo->query("SELECT * FROM cars WHERE status = 'available' LIMIT 4");
$cars = $stmt->fetchAll();

foreach($cars as $car):
?>

    <div class="car-card">
        <img src="assets/images/download (4).jpg">
        <h3><?= htmlspecialchars($car['car_name']) ?></h3>
        <p>$<?= number_format($car['price_per_day'], 2) ?>/day</p>
        <button onclick="window.location.href='car.php'">Rent Now</button>
    </div>

<?php endforeach; ?>

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