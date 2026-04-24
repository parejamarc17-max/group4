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

// Get all customer's bookings with approval status
$stmt = $pdo->prepare("SELECT r.*, c.car_name, c.brand, c.model, c.image, c.price_per_day
                       FROM rentals r 
                       JOIN car c ON r.car_id = c.id 
                       WHERE r.user_id = ? 
                       ORDER BY r.created_at DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

// Calculate statistics
$active_count = 0;
$completed_count = 0;
$cancelled_count = 0;
$pending_approval_count = 0;
$approved_pending_payment_count = 0;
$total_spent = 0;

foreach ($bookings as $booking) {
    if ($booking['status'] === 'active') {
        $active_count++;
    } elseif ($booking['status'] === 'completed') {
        $completed_count++;
        $total_spent += $booking['total_cost'];
    } elseif ($booking['status'] === 'cancelled') {
        $cancelled_count++;
    }
    
    // Count pending approvals
    if ($booking['approval_status'] === 'pending') {
        $pending_approval_count++;
    }
    
    // Count approved but not paid
    if ($booking['approval_status'] === 'approved' && $booking['payment_status'] !== 'paid') {
        $approved_pending_payment_count++;
    }
}

$current_date = date('Y-m-d');

// Helper function to get booking status display
function getBookingDisplayStatus($booking) {
    if ($booking['approval_status'] === 'pending') {
        return ['text' => 'Pending Approval', 'class' => 'pending-approval', 'icon' => 'fa-clock'];
    } elseif ($booking['approval_status'] === 'rejected') {
        return ['text' => 'Rejected', 'class' => 'rejected', 'icon' => 'fa-times-circle'];
    } elseif ($booking['approval_status'] === 'approved' && $booking['payment_status'] === 'pending') {
        return ['text' => 'Payment Required', 'class' => 'payment-pending', 'icon' => 'fa-credit-card'];
    } elseif ($booking['payment_status'] === 'paid' && $booking['status'] === 'active') {
        return ['text' => 'Active Rental', 'class' => 'active', 'icon' => 'fa-car'];
    } elseif ($booking['status'] === 'completed') {
        return ['text' => 'Completed', 'class' => 'completed', 'icon' => 'fa-check-circle'];
    } elseif ($booking['status'] === 'cancelled') {
        return ['text' => 'Cancelled', 'class' => 'cancelled', 'icon' => 'fa-ban'];
    }
    return ['text' => ucfirst($booking['status']), 'class' => $booking['status'], 'icon' => 'fa-info-circle'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - CarRent</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #666;
            font-size: 0.75rem;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }

        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 25px;
            border: none;
            background: white;
            border-radius: 25px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
            color: #666;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .filter-btn:hover:not(.active) {
            background: #e0e0e0;
        }

        /* Bookings Grid */
        .bookings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 25px;
        }

        .booking-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .booking-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .booking-header.pending-approval { background: #fff3cd; }
        .booking-header.rejected { background: #f8d7da; }
        .booking-header.payment-pending { background: #cce5ff; }
        .booking-header.active { background: #d4edda; }
        .booking-header.completed { background: #d1ecf1; }
        .booking-header.cancelled { background: #f5c6cb; }

        .booking-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
            color: #333;
        }

        .status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status.pending-approval {
            background: #ffc107;
            color: #856404;
        }

        .status.rejected {
            background: #dc3545;
            color: white;
        }

        .status.payment-pending {
            background: #17a2b8;
            color: white;
        }

        .status.active {
            background: #28a745;
            color: white;
        }

        .status.completed {
            background: #6c757d;
            color: white;
        }

        .status.cancelled {
            background: #dc3545;
            color: white;
        }

        .car-image {
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .booking-card:hover .car-image img {
            transform: scale(1.05);
        }

        .booking-details {
            padding: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #666;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .detail-value {
            color: #333;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .payment-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .payment-status.paid {
            background: #d4edda;
            color: #155724;
        }

        .payment-status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .payment-status.refunded {
            background: #d1ecf1;
            color: #0c5460;
        }

        .booking-footer {
            padding: 15px 20px 20px;
            border-top: 1px solid #f0f0f0;
        }

        .btn {
            padding: 10px 20px;
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
            gap: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
            justify-content: center;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            width: 100%;
            justify-content: center;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.3);
        }

        .btn-warning {
            background: #ffc107;
            color: #856404;
            width: 100%;
            justify-content: center;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #667eea;
            color: #667eea;
            width: 100%;
            justify-content: center;
        }

        .btn-outline:hover {
            background: #667eea;
            color: white;
        }

        .btn-disabled {
            background: #6c757d;
            cursor: not-allowed;
            width: 100%;
            justify-content: center;
            opacity: 0.7;
        }

        .btn-disabled:hover {
            transform: none;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 20px;
        }

        .approval-message {
            font-size: 0.7rem;
            margin-top: 5px;
            color: #666;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .bookings-grid {
                grid-template-columns: 1fr;
            }
            
            .booking-header h3 {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-tabs {
                justify-content: center;
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
            <h2><i class="fas fa-calendar-alt"></i> My Bookings</h2>
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
    <a href="bookings.php" class="btn-nav active">
        <i class="fas fa-calendar-check"></i> My Bookings
    </a>
    <a href="profile.php" class="btn-nav">
        <i class="fas fa-user"></i> My Profile
    </a>
    <a href="../index.php#cars" class="btn-nav">
        <i class="fas fa-car"></i> Browse Cars
    </a>
    <a href="../p_login/logout.php" class="btn-nav">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<div class="overlay" id="customerOverlay" onclick="closeMenuCustomer()"></div>

<div class="dashboard">
    <h1><i class="fas fa-calendar-alt"></i> My Bookings</h1>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <h3><?= count($bookings) ?></h3>
                <p>Total Bookings</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-bookmark"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h3><?= $pending_approval_count ?></h3>
                <p>Pending Approval</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h3><?= $approved_pending_payment_count ?></h3>
                <p>Pending Payment</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-credit-card"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h3><?= $active_count ?></h3>
                <p>Active Rentals</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-car-side"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h3>₱<?= number_format($total_spent, 2) ?></h3>
                <p>Total Spent</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
    </div>
    
    <?php if (empty($bookings)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>No Bookings Yet</h3>
            <p>You haven't made any car rental bookings yet.</p>
            <a href="../index.php#cars" class="btn btn-primary" style="display: inline-block; width: auto;">
                <i class="fas fa-car"></i> Browse Available Cars
            </a>
        </div>
    <?php else: ?>
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="filter-btn active" data-filter="all">All Bookings</button>
            <button class="filter-btn" data-filter="pending-approval">Pending Approval</button>
            <button class="filter-btn" data-filter="payment-pending">Payment Required</button>
            <button class="filter-btn" data-filter="active">Active Rentals</button>
            <button class="filter-btn" data-filter="completed">Completed</button>
            <button class="filter-btn" data-filter="cancelled">Cancelled</button>
        </div>

        <!-- Bookings Grid -->
        <div class="bookings-grid" id="bookingsGrid">
            <?php foreach ($bookings as $booking): 
                $displayStatus = getBookingDisplayStatus($booking);
                $headerClass = $displayStatus['class'];
            ?>
                <div class="booking-card" data-status="<?= $displayStatus['class'] ?>">
                    <div class="booking-header <?= $headerClass ?>">
                        <h3><?= htmlspecialchars($booking['car_name']) ?></h3>
                        <span class="status <?= $displayStatus['class'] ?>">
                            <i class="fas <?= $displayStatus['icon'] ?>"></i> <?= $displayStatus['text'] ?>
                        </span>
                    </div>
                    
                    <?php if ($booking['image']): ?>
                        <div class="car-image">
                            <img src="../uploads/<?= htmlspecialchars($booking['image']) ?>" alt="<?= htmlspecialchars($booking['car_name']) ?>">
                        </div>
                    <?php else: ?>
                        <div class="car-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-car" style="font-size: 4rem; color: rgba(255,255,255,0.5);"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="booking-details">
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-tag"></i> Brand/Model:</span>
                            <span class="detail-value"><?= htmlspecialchars($booking['brand'] . ' ' . $booking['model']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-calendar-check"></i> Pickup Date:</span>
                            <span class="detail-value"><?= date('M d, Y', strtotime($booking['rental_date'])) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-calendar-times"></i> Return Date:</span>
                            <span class="detail-value"><?= date('M d, Y', strtotime($booking['return_date'])) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-clock"></i> Duration:</span>
                            <span class="detail-value"><?= $booking['total_days'] ?> days</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-money-bill"></i> Total Cost:</span>
                            <span class="detail-value" style="color: #667eea; font-size: 1rem;">₱<?= number_format($booking['total_cost'], 2) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-credit-card"></i> Payment Status:</span>
                            <span class="detail-value">
                                <span class="payment-status <?= $booking['payment_status'] ?>"><?= ucfirst($booking['payment_status']) ?></span>
                            </span>
                        </div>
                        <?php if ($booking['approval_status'] === 'pending'): ?>
                            <div class="approval-message">
                                <i class="fas fa-info-circle"></i> Your booking is waiting for admin/worker approval. You will receive a notification once approved.
                            </div>
                        <?php elseif ($booking['approval_status'] === 'rejected'): ?>
                            <div class="approval-message" style="color: #dc3545;">
                                <i class="fas fa-exclamation-circle"></i> Your booking has been rejected. Please contact support for more information.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="booking-footer">
                        <?php if ($booking['approval_status'] === 'pending'): ?>
                            <button class="btn btn-warning" disabled>
                                <i class="fas fa-clock"></i> Waiting for Approval
                            </button>
                        <?php elseif ($booking['approval_status'] === 'approved' && $booking['payment_status'] !== 'paid'): ?>
                            <a href="make_payment.php?rental_id=<?= $booking['id'] ?>" class="btn btn-success">
                                <i class="fas fa-credit-card"></i> Pay Now
                            </a>
                        <?php elseif ($booking['payment_status'] === 'paid' && $booking['status'] === 'active'): ?>
                            <button class="btn btn-primary" disabled>
                                <i class="fas fa-car"></i> Active Rental
                            </button>
                        <?php elseif ($booking['status'] === 'completed'): ?>
                            <a href="../index.php#cars" class="btn btn-outline">
                                <i class="fas fa-redo-alt"></i> Rent Again
                            </a>
                        <?php elseif ($booking['status'] === 'cancelled' || $booking['approval_status'] === 'rejected'): ?>
                            <a href="../index.php#cars" class="btn btn-outline">
                                <i class="fas fa-car"></i> Browse Cars
                            </a>
                        <?php else: ?>
                            <a href="../index.php#cars" class="btn btn-primary">
                                <i class="fas fa-search"></i> Browse More Cars
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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

// Filter Functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const bookingCards = document.querySelectorAll('.booking-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            
            bookingCards.forEach(card => {
                if (filter === 'all') {
                    card.style.display = 'block';
                } else {
                    const status = card.getAttribute('data-status');
                    if (status === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        });
    });
});

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