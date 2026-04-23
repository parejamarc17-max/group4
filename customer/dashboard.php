<?php
require_once '../config/auth.php';
require_once '../config/database.php';
checkAuth();

// Check if user is customer
if ($_SESSION['role'] !== 'customer') {
    header('Location: ../p_login/login.php?error=Access denied');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get customer's bookings
$stmt = $pdo->prepare("SELECT r.*, c.car_name, c.brand, c.model, c.image 
                       FROM rentals r 
                       JOIN car c ON r.car_id = c.id 
                       WHERE r.user_id = ? 
                       ORDER BY r.created_at DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

// Get customer info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$customer = $stmt->fetch();

// Get customer's additional info if exists
$stmt = $pdo->prepare("SELECT * FROM customers WHERE user_id = ?");
$stmt->execute([$user_id]);
$customer_details = $stmt->fetch();

// Count active rentals
$active_rentals = 0;
$total_spent = 0;
foreach ($bookings as $booking) {
    if ($booking['status'] === 'active') {
        $active_rentals++;
    }
    if ($booking['status'] === 'completed') {
        $total_spent += $booking['total_cost'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - CarRent</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/customer-style.css">
</head>
<body>

<header>
    <div class="custom-header">
        <div class="header-left">
            <div class="hamburger-btn" onclick="toggleMenuCustomer()" title="Menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <h2>Customer Dashboard</h2>
        </div>
        <div class="header-right">
            <div class="user-section">
                <span class="username">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Customer'); ?>
                </span>
            </div>
        </div>
    </div>
</header>

<div class="side-menu" id="customerMenu">
    <img src="../assets/images/logo.png" class="profile-img" style="width:60px;height:60px;border-radius:50%;margin:10px auto;display:block;">
    <h2>DRIVE CUSTOMER</h2>
    <a href="dashboard.php" class="btn-nav"> Dashboard</a>
    <a href="bookings.php" class="btn-nav"> My Bookings</a>
    <a href="profile.php" class="btn-nav"> My Profile</a>
    <a href="../car.php" class="btn-nav"> Browse Cars</a>
    <a href="../p_login/logout.php" class="btn-nav"> Logout</a>
</div>

<div class="overlay" id="customerOverlay" onclick="closeMenuCustomer()"></div>

<div class="dashboard">
    <div class="dashboard">
    <div class="main" style="margin-left: 0; width: 100%;">
        <h1>Customer Dashboard</h1>
        
        <div class="cards">
            <div class="card"> Active Rentals<br><h2><?= $active_rentals ?></h2></div>
            <div class="card"> Total Bookings<br><h2><?= count($bookings) ?></h2></div>
            <div class="card">💰 Total Spent<br><h2>$<?= number_format($total_spent, 2) ?></h2></div>
            <div class="card">� Email<br><h2><?= htmlspecialchars($customer['email']) ?></h2></div>
        </div>

        <div class="recent-bookings">
            <h2>Recent Bookings</h2>
            <?php if (empty($bookings)): ?>
                <p>You haven't made any bookings yet. <a href="../car.php">Browse our cars</a> to get started!</p>
            <?php else: ?>
                <div class="booking-list">
                    <?php 
                    $display_count = 0;
                    foreach ($bookings as $booking): 
                        if ($display_count >= 5) break; // Show only 5 most recent
                    ?>
                        <div class="booking-item">
                            <div class="booking-info">
                                <h4><?= htmlspecialchars($booking['car_name']) ?></h4>
                                <p><?= htmlspecialchars($booking['brand'] . ' ' . $booking['model']) ?></p>
                                <p><strong>From:</strong> <?= date('M d, Y', strtotime($booking['rental_date'])) ?></p>
                                <p><strong>To:</strong> <?= date('M d, Y', strtotime($booking['return_date'])) ?></p>
                                <p><strong>Total:</strong> $<?= number_format($booking['total_cost'], 2) ?></p>
                            </div>
                            <div class="booking-status">
                                <span class="status <?= $booking['status'] ?>"><?= ucfirst($booking['status']) ?></span>
                            </div>
                        </div>
                    <?php 
                        $display_count++;
                    endforeach; 
                    ?>
                </div>
                <?php if (count($bookings) > 5): ?>
                    <div class="view-all">
                        <a href="bookings.php">View All Bookings →</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="../car.php" class="btn btn-primary">🚗 Browse Cars</a>
                <a href="profile.php" class="btn btn-secondary">👤 Update Profile</a>
                <a href="bookings.php" class="btn btn-info">📋 View All Bookings</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger-btn');
    const menu = document.getElementById("customerMenu");
    const overlay = document.getElementById("customerOverlay");

    function toggleMenuCustomer() {
        if (menu.classList.contains("active")) {
            closeMenuCustomer();
        } else {
            openMenuCustomer();
        }
    }

    function openMenuCustomer() {
        menu.classList.add("active");
        overlay.classList.add("active");
        hamburger.classList.add("active");
        document.body.style.overflow = 'hidden'; // Prevent background scroll
    }

    function closeMenuCustomer() {
        menu.classList.remove("active");
        overlay.classList.remove("active");
        hamburger.classList.remove("active");
        document.body.style.overflow = ''; // Restore scroll
    }

    // Attach event listeners
    if (hamburger) {
        hamburger.addEventListener('click', toggleMenuCustomer);
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeMenuCustomer);
    }

    // Close menu on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && menu.classList.contains("active")) {
            closeMenuCustomer();
        }
    });

    // Make functions global for onclick attributes
    window.toggleMenuCustomer = toggleMenuCustomer;
    window.closeMenuCustomer = closeMenuCustomer;
});
</script>

</body>
</html>
