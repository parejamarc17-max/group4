<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireWorker();

$stmt = $pdo->query("SELECT COUNT(*) as count FROM car");
$total_cars = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM rentals WHERE status = 'active'");
$active_rentals = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT SUM(total_cost) as total FROM rentals WHERE status = 'completed'");
$revenue = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Worker Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header>
    <div class="custom-header">
        <div class="header-left">

            <!-- ✅ FIXED FUNCTION NAME -->
            <div class="hamburger-btn hamburger" onclick="toggleMenuWorker()" title="Menu">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <h2>🚗 Worker Dashboard</h2>
        </div>

        <div class="header-right">
            <div class="user-section">
                <span class="username">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Worker'); ?>
                </span>
            </div>
        </div>
    </div>
</header>

<div class="sidebar" id="workerMenu">
    <img src="../assets/images/download (4).jpg" class="profile-img" style="width:60px;height:60px;border-radius:50%;margin:10px auto;display:block;">
    <h2>🚗 DRIVE WORKER</h2>
    <a href="../worker.php" class="btn-nav" onclick="closeMenuWorker()">← Home</a>
    <a href="worker_dashboard.php" class="btn-nav" onclick="closeMenuWorker()">📊 Dashboard</a>
    <a href="worker_manage_car.php" class="btn-nav" onclick="closeMenuWorker()">🚘 Manage Cars</a>
    <a href="worker_rentals.php" class="btn-nav" onclick="closeMenuWorker()">📅 Rentals</a>
    <a href="worker_products.php" class="btn-nav" onclick="closeMenuWorker()">📦 Products</a>
    <a href="worker_sales.php" class="btn-nav" onclick="closeMenuWorker()">💰 Sales</a>
    <a href="../p_login/logout.php" class="btn-nav" onclick="closeMenuWorker()">🚪 Logout</a>
</div>

<div class="overlay" id="workerOverlay" onclick="closeMenuWorker()"></div>

<div class="dashboard">
    <div class="main" style="margin-left: 0; width: 100%;">
        <h1>Dashboard Overview</h1>
        <div class="cards">
            <div class="card">🚗 Total Cars<br><h2><?= $total_cars ?></h2></div>
            <div class="card">🚘 Active Rentals<br><h2><?= $active_rentals ?></h2></div>
            <div class="card">💰 Revenue<br><h2>$<?= number_format($revenue, 2) ?></h2></div>
            <div class="card">👥 Users<br><h2><?= $total_users ?></h2></div>
        </div>
    </div>
</div>

<script>
function toggleMenuWorker() {
    const menu = document.getElementById("workerMenu");
    const overlay = document.getElementById("workerOverlay");
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

function closeMenuWorker() {
    const menu = document.getElementById("workerMenu");
    const overlay = document.getElementById("workerOverlay");
    const hamburger = document.querySelector('.hamburger-btn');

    if (menu) menu.classList.remove("active");
    if (overlay) overlay.classList.remove("active");
    if (hamburger) hamburger.classList.remove('active');
}
</script>

</body>
</html>