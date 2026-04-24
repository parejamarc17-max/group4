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
$stmt = $pdo->prepare("SELECT r.*, c.car_name, c.brand, c.model, c.image, c.price_per_day
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

// Get available cars count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM car WHERE status = 'available'");
$available_cars = $stmt->fetch()['count'];

// Calculate statistics
$active_rentals = 0;
$completed_rentals = 0;
$total_spent = 0;
$upcoming_rentals = 0;
$current_date = date('Y-m-d');

foreach ($bookings as $booking) {
    if ($booking['status'] === 'active') {
        if ($booking['rental_date'] <= $current_date && $booking['return_date'] >= $current_date) {
            $active_rentals++;
        } elseif ($booking['rental_date'] > $current_date) {
            $upcoming_rentals++;
        }
    }
    if ($booking['status'] === 'completed') {
        $completed_rentals++;
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/customer.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            overflow-x: hidden;
        }

        /* Header Styles */
        .custom-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .hamburger-btn {
            cursor: pointer;
            width: 30px;
            height: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 1001;
        }

        .hamburger-btn span {
            width: 100%;
            height: 3px;
            background: white;
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        .hamburger-btn.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }

        .hamburger-btn.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger-btn.active span:nth-child(3) {
            transform: rotate(-45deg) translate(8px, -8px);
        }

        .header-left h2 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .username {
            font-weight: 500;
        }

        /* Side Menu */
        .side-menu {
            position: fixed;
            top: 0;
            left: -300px;
            width: 280px;
            height: 100%;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: left 0.3s ease;
            z-index: 1002;
            padding: 20px;
            overflow-y: auto;
        }

        .side-menu.active {
            left: 0;
        }

        .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 20px auto;
            display: block;
            border: 3px solid #667eea;
            padding: 3px;
        }

        .side-menu h2 {
            text-align: center;
            color: #333;
            font-size: 1.3rem;
            margin-bottom: 20px;
        }

        .btn-nav {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            margin: 8px 0;
            color: #555;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-nav i {
            width: 24px;
            font-size: 1.2rem;
        }

        .btn-nav:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateX(5px);
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1001;
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Dashboard Content */
        .dashboard {
            margin-top: 70px;
            padding: 20px 30px;
        }

        .dashboard h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 30px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-info h3 {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .stat-info .number {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        /* Sections */
        .section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f2f5;
        }

        .section-header h2 {
            font-size: 1.3rem;
            color: #333;
        }

        .section-header a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        /* Booking Items */
        .booking-list {
            display: grid;
            gap: 15px;
        }

        .booking-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .booking-item:hover {
            background: #f0f2f5;
            transform: translateX(5px);
        }

        .booking-info h4 {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 5px;
        }

        .booking-info p {
            color: #666;
            font-size: 0.85rem;
            margin: 3px 0;
        }

        .booking-status {
            text-align: right;
        }

        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status.active {
            background: #d4edda;
            color: #155724;
        }

        .status.completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status.cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        /* Quick Actions */
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
        }

        /* Available Cars Grid */
        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .car-card {
            background: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .car-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            background: #ddd;
        }

        .car-info {
            padding: 15px;
        }

        .car-info h4 {
            font-size: 1rem;
            color: #333;
            margin-bottom: 5px;
        }

        .car-price {
            color: #667eea;
            font-weight: 700;
            font-size: 1.1rem;
            margin: 10px 0;
        }

        .btn-sm {
            padding: 8px 15px;
            font-size: 0.85rem;
            width: 100%;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .booking-item {
                flex-direction: column;
                text-align: center;
            }
            
            .booking-status {
                margin-top: 10px;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ccc;
        }
    </style>
</head>
<body>

<header>
    <div class="custom-header">
        <div class="header-left">
            <div class="hamburger-btn" onclick="toggleMenuCustomer()">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <h2><i class="fas fa-car"></i> CarRent Customer Portal</h2>
        </div>
        <div class="header-right">
            <div class="user-section">
                <i class="fas fa-user-circle" style="font-size: 1.5rem;"></i>
                <span class="username">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Customer'); ?>
                </span>
            </div>
        </div>
    </div>
</header>

<div class="side-menu" id="customerMenu">
    <img src="../assets/images/logo.png" class="profile-img" alt="Profile">
    <h2>Drive Customer</h2>
    <a href="dashboard.php" class="btn-nav active">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="bookings.php" class="btn-nav">
        <i class="fas fa-calendar-check"></i> My Bookings
    </a>
    <a href="profile.php" class="btn-nav">
        <i class="fas fa-user"></i> My Profile
    </a>
    <a href="car.php" class="btn-nav">
        <i class="fas fa-car"></i> Browse Cars
    </a>
    <a href="../p_login/logout.php" class="btn-nav">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<div class="overlay" id="customerOverlay" onclick="closeMenuCustomer()"></div>

<div class="dashboard">
    <h1>Welcome back, <?= htmlspecialchars($customer['full_name'] ?? $_SESSION['username']) ?>!</h1>
    
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <h3>Active Rentals</h3>
                <div class="number"><?= $active_rentals ?></div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-car"></i>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-info">
                <h3>Upcoming Rentals</h3>
                <div class="number"><?= $upcoming_rentals ?></div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-calendar-week"></i>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-info">
                <h3>Total Bookings</h3>
                <div class="number"><?= count($bookings) ?></div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-bookmark"></i>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-info">
                <h3>Total Spent</h3>
                <div class="number">₱<?= number_format($total_spent, 2) ?></div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
    </div>

    <!-- Active/Current Rentals Section -->
    <?php if ($active_rentals > 0): ?>
    <div class="section">
        <div class="section-header">
            <h2><i class="fas fa-car-side"></i> Currently Renting</h2>
            <a href="bookings.php">View All →</a>
        </div>
        <div class="booking-list">
            <?php foreach ($bookings as $booking): ?>
                <?php if ($booking['status'] === 'active' && $booking['rental_date'] <= $current_date && $booking['return_date'] >= $current_date): ?>
                <div class="booking-item">
                    <div class="booking-info">
                        <h4><?= htmlspecialchars($booking['car_name']) ?> - <?= htmlspecialchars($booking['brand']) ?></h4>
                        <p><i class="fas fa-calendar"></i> From: <?= date('M d, Y', strtotime($booking['rental_date'])) ?></p>
                        <p><i class="fas fa-calendar-check"></i> To: <?= date('M d, Y', strtotime($booking['return_date'])) ?></p>
                        <p><i class="fas fa-clock"></i> Days remaining: <?= max(0, ceil((strtotime($booking['return_date']) - time()) / 86400)) ?> days</p>
                    </div>
                    <div class="booking-status">
                        <span class="status active">Active</span>
                        <p style="margin-top: 10px; font-size: 1.1rem; font-weight: 600; color: #667eea;">
                            ₱<?= number_format($booking['total_cost'], 2) ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Bookings Section -->
    <div class="section">
        <div class="section-header">
            <h2><i class="fas fa-history"></i> Recent Bookings</h2>
            <a href="bookings.php">View All →</a>
        </div>
        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>You haven't made any bookings yet.</p>
                <a href="car.php" class="btn btn-primary" style="margin-top: 15px; display: inline-block;">Browse Available Cars</a>
            </div>
        <?php else: ?>
            <div class="booking-list">
                <?php 
                $display_count = 0;
                foreach ($bookings as $booking): 
                    if ($display_count >= 5) break;
                ?>
                    <div class="booking-item">
                        <div class="booking-info">
                            <h4><?= htmlspecialchars($booking['car_name']) ?> - <?= htmlspecialchars($booking['brand'] . ' ' . $booking['model']) ?></h4>
                            <p><i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($booking['rental_date'])) ?> → <?= date('M d, Y', strtotime($booking['return_date'])) ?></p>
                            <p><i class="fas fa-tag"></i> <?= $booking['total_days'] ?> days @ ₱<?= number_format($booking['price_per_day'], 2) ?>/day</p>
                        </div>
                        <div class="booking-status">
                            <span class="status <?= $booking['status'] ?>"><?= ucfirst($booking['status']) ?></span>
                            <p style="margin-top: 8px; font-weight: 600;">₱<?= number_format($booking['total_cost'], 2) ?></p>
                        </div>
                    </div>
                <?php 
                    $display_count++;
                endforeach; 
                ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="section">
        <div class="section-header">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
        </div>
        <div class="action-buttons">
            <a href="car.php" class="btn btn-primary">
                <i class="fas fa-car"></i> Browse Available Cars
            </a>
            <a href="profile.php" class="btn btn-secondary">
                <i class="fas fa-user-edit"></i> Update Profile
            </a>
            <a href="bookings.php" class="btn btn-info">
                <i class="fas fa-list"></i> View All Bookings
            </a>
        </div>
    </div>

    <!-- Available Cars Preview -->
    <div class="section">
        <div class="section-header">
            <h2><i class="fas fa-car"></i> Available Cars for Rent</h2>
            <a href="car.php">Browse All →</a>
        </div>
        <div class="cars-grid">
            <?php
            // Get 3 available cars for preview
            $stmt = $pdo->prepare("SELECT * FROM car WHERE status = 'available' LIMIT 3");
            $stmt->execute();
            $available_cars_list = $stmt->fetchAll();
            
            if (empty($available_cars_list)):
            ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <i class="fas fa-car-side"></i>
                    <p>No cars available at the moment. Please check back later!</p>
                </div>
            <?php else: ?>
                <?php foreach ($available_cars_list as $car): ?>
                <div class="car-card">
                    <?php 
$image_path = '';
if (!empty($car['image'])) {
    if (strpos($car['image'], 'assets/images/') === false) {
        $image_path = '../assets/images/' . $car['image'];
    } else {
        $image_path = '../' . $car['image'];
    }
} else {
    $image_path = '../assets/images/default-car.svg';
}
?>
<img src="<?= htmlspecialchars($image_path) ?>" class="car-image" alt="<?= htmlspecialchars($car['car_name']) ?>" onerror="this.src='../assets/images/default-car.svg'">
                    <div class="car-info">
                        <h4><?= htmlspecialchars($car['car_name']) ?></h4>
                        <p><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></p>
                        <div class="car-price">₱<?= number_format($car['price_per_day'], 2) ?>/day</div>
                        <a href="car.php?book=<?= $car['id'] ?>" class="btn btn-primary btn-sm">Book Now</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleMenuCustomer() {
    const menu = document.getElementById("customerMenu");
    const overlay = document.getElementById("customerOverlay");
    const hamburger = document.querySelector('.hamburger-btn');
    
    if (menu.classList.contains("active")) {
        closeMenuCustomer();
    } else {
        openMenuCustomer();
    }
}

function openMenuCustomer() {
    const menu = document.getElementById("customerMenu");
    const overlay = document.getElementById("customerOverlay");
    const hamburger = document.querySelector('.hamburger-btn');
    
    menu.classList.add("active");
    overlay.classList.add("active");
    if (hamburger) hamburger.classList.add("active");
    document.body.style.overflow = 'hidden';
}

function closeMenuCustomer() {
    const menu = document.getElementById("customerMenu");
    const overlay = document.getElementById("customerOverlay");
    const hamburger = document.querySelector('.hamburger-btn');
    
    menu.classList.remove("active");
    overlay.classList.remove("active");
    if (hamburger) hamburger.classList.remove("active");
    document.body.style.overflow = '';
}

// Close menu on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMenuCustomer();
    }
});

// Make functions global
window.toggleMenuCustomer = toggleMenuCustomer;
window.closeMenuCustomer = closeMenuCustomer;
</script>

</body>
</html>