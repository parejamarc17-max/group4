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

// Get all customer's bookings
$stmt = $pdo->prepare("SELECT r.*, c.car_name, c.brand, c.model, c.image 
                       FROM rentals r 
                       JOIN car c ON r.car_id = c.id 
                       WHERE r.user_id = ? 
                       ORDER BY r.created_at DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - CarRent</title>
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
            <h2>My Bookings</h2>
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
    <div class="main" style="margin-left: 0; width: 100%;">
        <h1>My Bookings</h1>
        
        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <h3>No bookings yet</h3>
                <p>You haven't made any car rental bookings yet.</p>
                <a href="../car.php" class="btn btn-primary">Browse Available Cars</a>
            </div>
        <?php else: ?>
            <div class="booking-stats">
                <div class="stat-card">
                    <h3><?= count($bookings) ?></h3>
                    <p>Total Bookings</p>
                </div>
                <div class="stat-card">
                    <h3><?= count(array_filter($bookings, fn($b) => $b['status'] === 'active')) ?></h3>
                    <p>Active Rentals</p>
                </div>
                <div class="stat-card">
                    <h3><?= count(array_filter($bookings, fn($b) => $b['status'] === 'completed')) ?></h3>
                    <p>Completed</p>
                </div>
            </div>

            <div class="bookings-grid">
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <h3><?= htmlspecialchars($booking['car_name']) ?></h3>
                            <span class="status <?= $booking['status'] ?>"><?= ucfirst($booking['status']) ?></span>
                        </div>
                        
                        <?php if ($booking['image']): ?>
                            <div class="car-image">
                                <img src="../uploads/<?= htmlspecialchars($booking['image']) ?>" alt="<?= htmlspecialchars($booking['car_name']) ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="booking-details">
                            <p><strong>Brand:</strong> <?= htmlspecialchars($booking['brand']) ?></p>
                            <p><strong>Model:</strong> <?= htmlspecialchars($booking['model']) ?></p>
                            <p><strong>Pickup:</strong> <?= date('M d, Y', strtotime($booking['rental_date'])) ?></p>
                            <p><strong>Return:</strong> <?= date('M d, Y', strtotime($booking['return_date'])) ?></p>
                            <p><strong>Duration:</strong> <?= $booking['total_days'] ?> days</p>
                            <p><strong>Total Cost:</strong> $<?= number_format($booking['total_cost'], 2) ?></p>
                            <p><strong>Payment:</strong> <span class="payment-status <?= $booking['payment_status'] ?>"><?= ucfirst($booking['payment_status']) ?></span></p>
                            <p><strong>Booked:</strong> <?= date('M d, Y', strtotime($booking['created_at'])) ?></p>
                        </div>
                        
                        <div class="booking-actions">
                            <?php if ($booking['status'] === 'active'): ?>
                                <button class="btn btn-info" onclick="window.location.href='../car.php'">Book Another Car</button>
                            <?php elseif ($booking['status'] === 'completed'): ?>
                                <button class="btn btn-primary" onclick="window.location.href='../car.php'">Book Again</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
        document.body.style.overflow = 'hidden';
    }

    function closeMenuCustomer() {
        menu.classList.remove("active");
        overlay.classList.remove("active");
        hamburger.classList.remove("active");
        document.body.style.overflow = '';
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
