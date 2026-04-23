<?php
require_once '../config/auth.php';
require_once '../config/database.php';
checkAuth();

// ================= ORIGINAL =================

// Total Cars
$stmt = $pdo->query("SELECT COUNT(*) as total FROM car");
$total_cars = $stmt->fetch()['total'];

// Active Rentals
$stmt = $pdo->query("SELECT COUNT(*) as total FROM rentals WHERE status = 'active'");
$active_rentals = $stmt->fetch()['total'];

// Revenue
$stmt = $pdo->query("SELECT SUM(total_cost) as revenue FROM rentals WHERE status = 'completed'");
$revenue = $stmt->fetch()['revenue'] ?? 0;

// Total Rentals
$stmt = $pdo->query("SELECT COUNT(*) as total FROM rentals");
$total_rentals = $stmt->fetch()['total'];

// Recent Rentals
$stmt = $pdo->query("SELECT r.*, c.car_name FROM rentals r 
                    LEFT JOIN car c ON r.car_id = c.id 
                    ORDER BY r.id DESC LIMIT 5");
$recent_rentals = $stmt->fetchAll();


// ================= ADDED (WORKER PAYMENTS) =================

// Total Worker Payments
$stmt = $pdo->query("SELECT SUM(amount) as total FROM worker_payments");
$total_worker_payments = $stmt->fetch()['total'] ?? 0;

// Recent Worker Payments
$stmt = $pdo->query("SELECT wp.*, w.name as worker_name 
                    FROM worker_payments wp 
                    LEFT JOIN workers w ON wp.worker_id = w.id 
                    ORDER BY wp.id DESC LIMIT 5");
$recent_payments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
    <a href="dashboard.php" class="btn-nav">📊 Dashboard</a>
    <a href="manage_car.php" class="btn-nav">🚘 Manage Cars</a>
    <a href="rentals.php" class="btn-nav">📅 Rentals</a>
    <a href="products.php" class="btn-nav">📦 Products</a>
    <a href="sales.php" class="btn-nav">💰 Sales</a>
    <a href="worker_list.php" class="btn-nav">👷 Worker List</a>
    <a href="pending_workers.php" class="btn-nav">⏳ Pending Workers</a>

    <!-- ADDED -->
    <a href="worker_payment.php" class="btn-nav">💵 Worker Payments</a>

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

        <!-- ADDED -->
        <div class="card">👷 Worker Payments<br><h2>$<?= number_format($total_worker_payments, 2) ?></h2></div>
    </div>

    <!-- RECENT RENTALS -->
    <div class="panel">
        <h2>📋 Recent Rentals</h2>
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Car</th>
                    <th>Rental Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Amount</th>
                </tr>
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

    <!-- ADDED WORKER PAYMENTS PANEL -->
    <div class="panel">
        <h2>💵 Recent Worker Payments</h2>
        <table>
            <thead>
                <tr>
                    <th>Worker</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recent_payments as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['worker_name'] ?? 'N/A') ?></td>
                    <td>$<?= number_format($p['amount'], 2) ?></td>
                    <td><?= $p['payment_date'] ?></td>
                    <td><?= $p['status'] ?></td>
                    <td><?= htmlspecialchars($p['notes']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleMenuAdmin() {
    const menu = document.getElementById("adminMenu");
    const overlay = document.getElementById("adminOverlay");
    const hamburger = document.querySelector('.hamburger-btn');

    if (!menu) return;

    menu.classList.toggle("active");
    overlay.classList.toggle("active");
    hamburger.classList.toggle("active");
}

function closeMenuAdmin() {
    document.getElementById("adminMenu").classList.remove("active");
    document.getElementById("adminOverlay").classList.remove("active");
}
</script>

</body>
</html>