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

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get customer details
$stmt = $pdo->prepare("SELECT * FROM customers WHERE user_id = ?");
$stmt->execute([$user_id]);
$customer_details = $stmt->fetch();

// Get booking statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_bookings, SUM(total_cost) as total_spent FROM rentals WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

$total_bookings = $stats['total_bookings'] ?? 0;
$total_spent = $stats['total_spent'] ?? 0;

// Get active bookings count
$stmt = $pdo->prepare("SELECT COUNT(*) as active FROM rentals WHERE user_id = ? AND status = 'active'");
$stmt->execute([$user_id]);
$active_rentals = $stmt->fetch()['active'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CarRent</title>
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
            object-fit: cover;
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
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Profile Grid */
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header i {
            font-size: 1.5rem;
        }

        .card-header h2 {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
        }

        .card-body {
            padding: 25px;
        }

        /* Profile Avatar Section */
        .avatar-section {
            text-align: center;
            margin-bottom: 25px;
        }

        .avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .avatar i {
            font-size: 3.5rem;
            color: white;
        }

        .avatar-section h3 {
            color: #333;
            font-size: 1.3rem;
            margin-bottom: 5px;
        }

        .avatar-section p {
            color: #888;
            font-size: 0.85rem;
        }

        /* Info Rows */
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #666;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-label i {
            width: 20px;
            color: #667eea;
        }

        .info-value {
            color: #333;
            font-weight: 600;
        }

        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 10px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #666;
            margin-top: 5px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
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
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard {
                padding: 15px;
            }
            
            .profile-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
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
            <h2><i class="fas fa-user-circle"></i> My Profile</h2>
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
    <h2>DRIVE CUSTOMER</h2>
    <a href="dashboard.php" class="btn-nav">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="bookings.php" class="btn-nav">
        <i class="fas fa-calendar-check"></i> My Bookings
    </a>
    <a href="profile.php" class="btn-nav active">
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
    <h1><i class="fas fa-user-circle"></i> My Profile</h1>
    
    <div class="profile-grid">
        <!-- Personal Information Card -->
        <div class="profile-card">
            <div class="card-header">
                <i class="fas fa-user"></i>
                <h2>Personal Information</h2>
            </div>
            <div class="card-body">
                <div class="avatar-section">
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                    <p><?= htmlspecialchars($user['username']) ?></p>
                </div>
                
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-user-tag"></i> Username</span>
                    <span class="info-value"><?= htmlspecialchars($user['username']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-signature"></i> Full Name</span>
                    <span class="info-value"><?= htmlspecialchars($user['full_name']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-envelope"></i> Email Address</span>
                    <span class="info-value"><?= htmlspecialchars($user['email'] ?? 'Not provided') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-phone"></i> Phone Number</span>
                    <span class="info-value"><?= htmlspecialchars($customer_details['phone'] ?? 'Not provided') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-map-marker-alt"></i> Address</span>
                    <span class="info-value"><?= htmlspecialchars($customer_details['address'] ?? 'Not provided') ?></span>
                </div>
            </div>
        </div>
        
        <!-- Account Statistics Card -->
        <div class="profile-card">
            <div class="card-header">
                <i class="fas fa-chart-line"></i>
                <h2>Account Statistics</h2>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-user-check"></i> Account Type</span>
                    <span class="info-value">Customer</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-calendar-alt"></i> Member Since</span>
                    <span class="info-value"><?= date('F d, Y', strtotime($user['created_at'])) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-clock"></i> Account Age</span>
                    <span class="info-value">
                        <?php
                        $created = new DateTime($user['created_at']);
                        $now = new DateTime();
                        $interval = $created->diff($now);
                        echo $interval->y . ' years, ' . $interval->m . ' months';
                        ?>
                    </span>
                </div>
                
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-number"><?= $total_bookings ?></div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= $active_rentals ?></div>
                        <div class="stat-label">Active Rentals</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">₱<?= number_format($total_spent, 2) ?></div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions Card -->
        <div class="profile-card">
            <div class="card-header">
                <i class="fas fa-bolt"></i>
                <h2>Quick Actions</h2>
            </div>
            <div class="card-body">
                <div class="action-buttons">
                    <a href="bookings.php" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i> My Bookings
                    </a>
                    <a href="car.php" class="btn btn-secondary">
                        <i class="fas fa-car"></i> Browse Cars
                    </a>
                </div>
                <div class="action-buttons" style="margin-top: 10px;">
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="../p_login/logout.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle Menu Functions
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