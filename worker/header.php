<?php
session_start();
require_once '../config/database.php';
require_once '../includes/NotificationHelper.php';

// Check if user is logged in and is a worker
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    header('Location: ../p_login/login.php?error=Access denied');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get worker info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$worker = $stmt->fetch();

// Get notification counts
$notificationHelper = new NotificationHelper($pdo);
$unread_count = $notificationHelper->getUnreadCount($user_id, 'worker');

// Get pending approvals count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE approval_status = 'pending'");
$stmt->execute();
$pending_approvals = $stmt->fetchColumn();

// Get pending verifications count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM payment_requests WHERE status = 'paid'");
$stmt->execute();
$pending_verifications = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard - CarRent System</title>
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
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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

        .worker-badge {
            background: #ff9800;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
        }

        /* Notification Styles */
        .notification-badge {
            position: relative;
            cursor: pointer;
            margin-right: 10px;
        }

        .notification-badge i {
            font-size: 1.3rem;
        }

        .notification-badge .badge-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.65rem;
            font-weight: 600;
            min-width: 18px;
            text-align: center;
        }

        /* Notification Dropdown */
        .notification-dropdown {
            position: absolute;
            top: 45px;
            right: 0;
            width: 380px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-dropdown.active {
            display: block;
        }

        .notification-header {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
            background: white;
            border-radius: 12px 12px 0 0;
        }

        .notification-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item.unread {
            background: #f0f2ff;
        }

        .notification-title {
            font-weight: 600;
            font-size: 0.85rem;
            color: #333;
            margin-bottom: 4px;
        }

        .notification-message {
            font-size: 0.75rem;
            color: #666;
            margin-bottom: 4px;
        }

        .notification-time {
            font-size: 0.65rem;
            color: #999;
        }

        .notification-empty {
            padding: 30px;
            text-align: center;
            color: #999;
        }

        /* Pending Badge on Menu Items */
        .menu-badge {
            background: #ff4757;
            color: white;
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 0.65rem;
            font-weight: 600;
            margin-left: auto;
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
            position: relative;
        }

        .btn-nav i {
            width: 24px;
            font-size: 1.2rem;
        }

        .btn-nav:hover {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            transform: translateX(5px);
        }

        .btn-nav:hover .menu-badge {
            background: white;
            color: #1e3c72;
        }

        .btn-nav.active {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
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

        /* Side Menu */
        .side-menu {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100vh;
            background: white;
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 1002;
            overflow-y: auto;
        }

        .side-menu.active {
            left: 0;
        }

        .side-menu .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 30px auto 15px;
            display: block;
            object-fit: cover;
        }

        .side-menu h2 {
            text-align: center;
            color: #1e3c72;
            margin-bottom: 30px;
            font-size: 1.3rem;
        }

        .side-menu hr {
            margin: 15px 20px;
            border-color: #f0f0f0;
        }

        .side-menu .btn-nav {
            margin: 8px 20px;
            padding: 15px 20px;
        }

        .side-menu .btn-nav:hover {
            transform: translateX(5px);
        }

        /* Main Content */
        .main-content {
            margin-top: 70px;
            padding: 20px 30px;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 20px;
            padding: 25px 30px;
            color: white;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .welcome-text h1 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .stat-info h3 {
            font-size: 2.2rem;
            font-weight: 800;
            color: #1e3c72;
            margin-bottom: 5px;
            line-height: 1;
        }

        .stat-info p {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            box-shadow: 0 4px 10px rgba(30, 60, 114, 0.3);
        }

        /* Section Styles */
        .section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 35px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 18px;
            border-bottom: 2px solid #f0f2f5;
        }

        .section-header h2 {
            font-size: 1.5rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }

        .btn-add {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        /* Cars Grid */
        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .car-card {
            background: #f8f9fa;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .car-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .car-image {
            width: 100%;
            height: 150px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .car-image i {
            font-size: 3rem;
            color: rgba(255,255,255,0.7);
        }

        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .car-details {
            padding: 15px;
        }

        .car-details h3 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .car-details p {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 5px;
        }

        .car-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e3c72;
            margin: 10px 0;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .status-available {
            background: #d4edda;
            color: #155724;
        }

        .status-rented {
            background: #fff3cd;
            color: #856404;
        }

        .status-maintenance {
            background: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-refunded {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-payment-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .status-cancelled {
            background: #e2e3e5;
            color: #383d41;
        }

        .car-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-edit, .btn-delete {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit {
            background: #2196f3;
            color: white;
        }

        .btn-delete {
            background: #f44336;
            color: white;
        }

        .btn-return, .btn-approve {
            background: #4caf50;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-block;
        }

        .btn-cancel, .btn-reject {
            background: #f44336;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-block;
        }

        .btn-verify {
            background: #2196f3;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-block;
        }

        /* Tables */
        .rentals-table {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            width: 90%;
            max-width: 550px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #1e3c72;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
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

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .cars-grid {
                grid-template-columns: 1fr;
            }
            
            .notification-dropdown {
                width: 320px;
                right: -50px;
            }
            
            .side-menu {
                width: 280px;
                left: -280px;
            }
            
            .side-menu.active {
                left: 0;
            }
            
            .welcome-banner {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .date-badge {
                margin-top: 10px;
            }
            
            table, thead, tbody, th, td, tr {
                display: block;
            }
            
            tr {
                margin-bottom: 15px;
                border: 1px solid #f0f0f0;
                border-radius: 10px;
            }
            
            td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px;
            }
            
            td::before {
                content: attr(data-label);
                font-weight: 600;
                width: 40%;
            }
        }
        /* Quick Actions Container */
.quick-actions-container {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.priority-action {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(243, 156, 18, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(243, 156, 18, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(243, 156, 18, 0);
    }
}

/* Date Badge */
.date-badge {
    background: rgba(255,255,255,0.2);
    padding: 10px 20px;
    border-radius: 12px;
    text-align: center;
    backdrop-filter: blur(10px);
}

.date-badge .date {
    font-size: 1.2rem;
    font-weight: 600;
}

.date-badge .day {
    font-size: 0.8rem;
    opacity: 0.8;
}

/* Car Info */
.car-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.car-thumb {
    width: 45px;
    height: 45px;
    border-radius: 8px;
    object-fit: cover;
    background: #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.car-name {
    font-weight: 600;
    margin-bottom: 3px;
}

.car-model {
    font-size: 0.7rem;
    color: #666;
}

/* Top Cars Grid */
.top-cars-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
}

.top-car-item {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
}

.top-car-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.top-car-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.top-car-info h4 {
    font-size: 0.9rem;
    margin-bottom: 3px;
}

.top-car-info p {
    font-size: 0.7rem;
    color: #666;
}

.top-car-count {
    margin-left: auto;
    font-weight: 700;
    color: #1e3c72;
    font-size: 1.1rem;
}

/* Status Badge Extras */
.status-payment-pending {
    background: #fff3cd;
    color: #856404;
}

.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

.status-cancelled {
    background: #e2e3e5;
    color: #383d41;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}
    </style>
</head>
<body>

<header>
    <div class="custom-header">
        <div class="header-left">
            <div class="hamburger-btn" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <h2><i class="fas fa-tools"></i> Worker Dashboard</h2>
            <div class="worker-badge">WORKER ACCESS</div>
        </div>
        <div class="header-right">
            <!-- Notification Bell -->
            <div class="notification-badge" id="notificationBell">
                <i class="fas fa-bell"></i>
                <?php if ($unread_count > 0): ?>
                    <span class="badge-count" id="notificationCount"><?= $unread_count ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Notification Dropdown -->
            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">
                    <i class="fas fa-bell"></i> Notifications
                </div>
                <div id="notificationList">
                    <div class="notification-empty">
                        <i class="fas fa-inbox"></i>
                        <p>Loading notifications...</p>
                    </div>
                </div>
            </div>
            
            <div class="user-section">
                <i class="fas fa-user-circle" style="font-size: 1.5rem;"></i>
                <span><?= htmlspecialchars($worker['username'] ?? 'Worker') ?></span>
            </div>
        </div>
    </div>
</header>

<div class="side-menu" id="sideMenu">
    <img src="../assets/images/logo.png" class="profile-img" alt="Logo">
    <h2>CarRent Worker</h2>
    
    <a href="dashboard.php" class="btn-nav <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    
    <a href="approve_booking.php" class="btn-nav <?= basename($_SERVER['PHP_SELF']) == 'approve_booking.php' ? 'active' : '' ?>">
        <i class="fas fa-clock"></i> Approve Bookings
        <?php if ($pending_approvals > 0): ?>
            <span class="menu-badge"><?= $pending_approvals ?></span>
        <?php endif; ?>
    </a>
    
    <a href="verify_payment.php" class="btn-nav <?= basename($_SERVER['PHP_SELF']) == 'verify_payment.php' ? 'active' : '' ?>">
        <i class="fas fa-credit-card"></i> Verify Payments
        <?php if ($pending_verifications > 0): ?>
            <span class="menu-badge"><?= $pending_verifications ?></span>
        <?php endif; ?>
    </a>
    
    <a href="manage_cars.php" class="btn-nav <?= basename($_SERVER['PHP_SELF']) == 'manage_cars.php' ? 'active' : '' ?>">
        <i class="fas fa-car"></i> Manage Cars
    </a>
    
    <a href="manage_rentals.php" class="btn-nav <?= basename($_SERVER['PHP_SELF']) == 'manage_rentals.php' ? 'active' : '' ?>">
        <i class="fas fa-calendar-check"></i> Manage Rentals
    </a>
    
    <a href="rental_history.php" class="btn-nav <?= basename($_SERVER['PHP_SELF']) == 'rental_history.php' ? 'active' : '' ?>">
        <i class="fas fa-history"></i> Rental History
    </a>
    
    <hr style="margin: 10px 0; border-color: #eee;">
    
    <a href="../p_login/logout.php" class="btn-nav">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<div class="overlay" id="overlay" onclick="closeMenu()"></div>

<script>
// Toggle Menu Functions
function toggleMenu() {
    const menu = document.getElementById("sideMenu");
    const overlay = document.getElementById("overlay");
    const hamburger = document.querySelector('.hamburger-btn');
    
    if (menu.classList.contains("active")) {
        closeMenu();
    } else {
        openMenu();
    }
}

function openMenu() {
    const menu = document.getElementById("sideMenu");
    const overlay = document.getElementById("overlay");
    const hamburger = document.querySelector('.hamburger-btn');
    
    menu.classList.add("active");
    overlay.classList.add("active");
    if (hamburger) hamburger.classList.add("active");
    document.body.style.overflow = 'hidden';
}

function closeMenu() {
    const menu = document.getElementById("sideMenu");
    const overlay = document.getElementById("overlay");
    const hamburger = document.querySelector('.hamburger-btn');
    
    menu.classList.remove("active");
    overlay.classList.remove("active");
    if (hamburger) hamburger.classList.remove("active");
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMenu();
        closeNotificationDropdown();
    }
});

window.toggleMenu = toggleMenu;
window.closeMenu = closeMenu;

// ============ NOTIFICATION FUNCTIONS ============
const notificationBell = document.getElementById('notificationBell');
const notificationDropdown = document.getElementById('notificationDropdown');
const notificationList = document.getElementById('notificationList');

// Toggle notification dropdown
notificationBell.addEventListener('click', function(e) {
    e.stopPropagation();
    notificationDropdown.classList.toggle('active');
    if (notificationDropdown.classList.contains('active')) {
        loadNotifications();
    }
});

// Close dropdown when clicking outside
document.addEventListener('click', function() {
    notificationDropdown.classList.remove('active');
});

function closeNotificationDropdown() {
    notificationDropdown.classList.remove('active');
}

// Load notifications via AJAX
function loadNotifications() {
    fetch('../processor/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.notifications.length === 0) {
                notificationList.innerHTML = '<div class="notification-empty"><i class="fas fa-inbox"></i><p>No notifications</p></div>';
            } else {
                notificationList.innerHTML = data.notifications.map(notif => `
                    <div class="notification-item ${notif.is_read == 0 ? 'unread' : ''}" onclick="markAsRead(${notif.id}, '${notif.link}')">
                        <div class="notification-title">${notif.title}</div>
                        <div class="notification-message">${notif.message}</div>
                        <div class="notification-time">${notif.time_ago}</div>
                    </div>
                `).join('');
            }
            
            // Update badge count
            const badgeCount = document.getElementById('notificationCount');
            if (data.unread_count > 0) {
                if (badgeCount) {
                    badgeCount.textContent = data.unread_count;
                } else {
                    notificationBell.innerHTML += `<span class="badge-count" id="notificationCount">${data.unread_count}</span>`;
                }
            } else if (badgeCount) {
                badgeCount.remove();
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            notificationList.innerHTML = '<div class="notification-empty"><i class="fas fa-exclamation-circle"></i><p>Error loading notifications</p></div>';
        });
}

// Mark notification as read and redirect
function markAsRead(id, link) {
    fetch('../processor/mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    }).then(() => {
        if (link) {
            window.location.href = link;
        } else {
            loadNotifications();
        }
    });
}

// Auto-refresh notifications every 30 seconds
setInterval(loadNotifications, 30000);

// Initial load for stats
function refreshStats() {
    fetch('../processor/get_worker_stats.php')
        .then(response => response.json())
        .then(data => {
            // Update pending badges in menu
            const pendingApprovalsBadge = document.querySelector('a[href="approve_booking.php"] .menu-badge');
            const pendingVerificationsBadge = document.querySelector('a[href="verify_payment.php"] .menu-badge');
            
            if (pendingApprovalsBadge) {
                if (data.pending_approvals > 0) {
                    pendingApprovalsBadge.textContent = data.pending_approvals;
                    pendingApprovalsBadge.style.display = '';
                } else {
                    pendingApprovalsBadge.style.display = 'none';
                }
            }
            
            if (pendingVerificationsBadge) {
                if (data.pending_verifications > 0) {
                    pendingVerificationsBadge.textContent = data.pending_verifications;
                    pendingVerificationsBadge.style.display = '';
                } else {
                    pendingVerificationsBadge.style.display = 'none';
                }
            }
        });
}

// Refresh stats every minute
setInterval(refreshStats, 60000);
</script>

<div class="main-content">