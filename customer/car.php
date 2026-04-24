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

// Handle booking submission
$booking_success = '';
$booking_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_car'])) {
    $car_id = $_POST['car_id'];
    $rental_date = $_POST['rental_date'];
    $return_date = $_POST['return_date'];
    $customer_name = $_POST['customer_name'];
    $customer_phone = $_POST['customer_phone'];
    $customer_email = $_POST['customer_email'];
    
    // Calculate days and total cost
    $rental = new DateTime($rental_date);
    $return = new DateTime($return_date);
    $total_days = $rental->diff($return)->days;
    
    // Get car price
    $stmt = $pdo->prepare("SELECT price_per_day FROM car WHERE id = ?");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();
    $total_cost = $car['price_per_day'] * $total_days;
    
    // Insert booking
    try {
        $stmt = $pdo->prepare("INSERT INTO rentals (car_id, customer_name, customer_phone, customer_email, rental_date, return_date, total_days, total_cost, status, user_id, payment_status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, 'pending')");
        $stmt->execute([$car_id, $customer_name, $customer_phone, $customer_email, $rental_date, $return_date, $total_days, $total_cost, $user_id]);
        
        // Update car status to rented
        $stmt = $pdo->prepare("UPDATE car SET status = 'rented' WHERE id = ?");
        $stmt->execute([$car_id]);
        
        $booking_success = "Car booked successfully! Redirecting to your bookings...";
        echo "<script>setTimeout(function(){ window.location.href = 'customer/bookings.php'; }, 2000);</script>";
    } catch (PDOException $e) {
        $booking_error = "Booking failed: " . $e->getMessage();
    }
}

// Get customer info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$customer = $stmt->fetch();

// Get customer details
$stmt = $pdo->prepare("SELECT * FROM customers WHERE user_id = ?");
$stmt->execute([$user_id]);
$customer_details = $stmt->fetch();

// Get available cars (not rented or under maintenance)
$stmt = $pdo->prepare("SELECT * FROM car WHERE status = 'available' ORDER BY created_at DESC");
$stmt->execute();
$available_cars = $stmt->fetchAll();

// Get customer's active bookings
$stmt = $pdo->prepare("SELECT r.*, c.car_name, c.brand, c.image 
                       FROM rentals r 
                       JOIN car c ON r.car_id = c.id 
                       WHERE r.user_id = ? AND r.status = 'active'
                       ORDER BY r.rental_date ASC");
$stmt->execute([$user_id]);
$active_bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Cars - CarRent Customer Portal</title>
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

        /* Main Content */
        .main-content {
            margin-top: 70px;
            padding: 20px 30px;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            color: white;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .welcome-text h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .welcome-text p {
            opacity: 0.9;
        }

        .stats-badge {
            background: rgba(255,255,255,0.2);
            padding: 15px 25px;
            border-radius: 15px;
            text-align: center;
        }

        .stats-badge .number {
            font-size: 2rem;
            font-weight: 700;
        }

        /* Section Titles */
        .section-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Active Bookings Section */
        .active-bookings-section {
            margin-bottom: 40px;
        }

        .active-bookings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .active-booking-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #ff9800;
        }

        .active-booking-card .card-header {
            background: #fff8e1;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .active-booking-card .car-name {
            font-weight: 600;
            color: #333;
        }

        .status-badge {
            background: #ff9800;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .active-booking-card .card-body {
            padding: 15px;
        }

        .date-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.85rem;
        }

        .date-label {
            color: #666;
        }

        .date-value {
            font-weight: 500;
            color: #333;
        }

        /* Cars Grid */
        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        .car-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .car-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .car-image i {
            font-size: 4rem;
            color: rgba(255,255,255,0.7);
        }

        .car-info {
            padding: 20px;
        }

        .car-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .car-brand {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 15px;
        }

        .car-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 15px;
        }

        .car-price span {
            font-size: 0.8rem;
            font-weight: normal;
            color: #666;
        }

        .car-description {
            color: #777;
            font-size: 0.8rem;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .btn-book {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.3);
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
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
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

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
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
            background: white;
            border-radius: 20px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 15px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .welcome-banner {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .cars-grid, .active-bookings-grid {
                grid-template-columns: 1fr;
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
            <h2><i class="fas fa-car"></i> Browse Cars</h2>
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
    <a href="profile.php" class="btn-nav">
        <i class="fas fa-user"></i> My Profile
    </a>
    <a href="car.php" class="btn-nav active">
        <i class="fas fa-car"></i> Browse Cars
    </a>
    <a href="../p_login/logout.php" class="btn-nav">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<div class="overlay" id="customerOverlay" onclick="closeMenuCustomer()"></div>

<div class="main-content">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-text">
            <h1>Welcome, <?= htmlspecialchars($customer['full_name'] ?? $_SESSION['username']) ?>!</h1>
            <p>Find your perfect ride and hit the road with confidence.</p>
        </div>
        <div class="stats-badge">
            <div class="number"><?= count($available_cars) ?></div>
            <div>Cars Available</div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($booking_success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($booking_success) ?></div>
    <?php endif; ?>
    
    <?php if ($booking_error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($booking_error) ?></div>
    <?php endif; ?>

    <!-- Active Bookings Section -->
    <?php if (!empty($active_bookings)): ?>
    <div class="active-bookings-section">
        <h2 class="section-title">
            <i class="fas fa-car-side"></i> Your Active Rentals
        </h2>
        <div class="active-bookings-grid">
            <?php foreach ($active_bookings as $booking): ?>
                <div class="active-booking-card">
                    <div class="card-header">
                        <span class="car-name"><?= htmlspecialchars($booking['car_name']) ?></span>
                        <span class="status-badge">Active</span>
                    </div>
                    <div class="card-body">
                        <div class="date-info">
                            <span class="date-label"><i class="fas fa-calendar"></i> Pickup:</span>
                            <span class="date-value"><?= date('M d, Y', strtotime($booking['rental_date'])) ?></span>
                        </div>
                        <div class="date-info">
                            <span class="date-label"><i class="fas fa-calendar-check"></i> Return:</span>
                            <span class="date-value"><?= date('M d, Y', strtotime($booking['return_date'])) ?></span>
                        </div>
                        <div class="date-info">
                            <span class="date-label"><i class="fas fa-clock"></i> Days Left:</span>
                            <span class="date-value"><?= max(0, ceil((strtotime($booking['return_date']) - time()) / 86400)) ?> days</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Available Cars Section -->
    <h2 class="section-title">
        <i class="fas fa-car"></i> Available Cars for Rent
    </h2>
    
    <?php if (empty($available_cars)): ?>
        <div class="empty-state">
            <i class="fas fa-car-side"></i>
            <h3>No cars available at the moment</h3>
            <p>Please check back later or contact support for assistance.</p>
        </div>
    <?php else: ?>
        <div class="cars-grid">
            <?php foreach ($available_cars as $car): ?>
                <div class="car-card">
                    <div class="car-image">
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
                        <img src="<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($car['car_name']) ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.src='../assets/images/default-car.svg'">
                    </div>
                    <div class="car-info">
                        <div class="car-name"><?= htmlspecialchars($car['car_name']) ?></div>
                        <div class="car-brand"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?> (<?= $car['year'] ?>)</div>
                        <div class="car-price">₱<?= number_format($car['price_per_day'], 2) ?> <span>/ day</span></div>
                        <div class="car-description"><?= htmlspecialchars(substr($car['description'] ?? 'No description available', 0, 100)) ?>...</div>
                        <button class="btn-book" onclick="openBookingModal(<?= $car['id'] ?>, '<?= htmlspecialchars($car['car_name']) ?>', <?= $car['price_per_day'] ?>)">
                            <i class="fas fa-calendar-check"></i> Book This Car
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Booking Modal -->
<div class="modal" id="bookingModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-calendar-plus"></i> Book a Car</h3>
            <button class="close-modal" onclick="closeBookingModal()">&times;</button>
        </div>
        <form method="POST" action="" id="bookingForm">
            <div class="modal-body">
                <input type="hidden" name="book_car" value="1">
                <input type="hidden" name="car_id" id="car_id">
                
                <div class="form-group">
                    <label>Car</label>
                    <input type="text" id="car_name_display" readonly style="background:#f5f5f5;">
                </div>
                
                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" name="customer_name" value="<?= htmlspecialchars($customer['full_name'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="customer_phone" value="<?= htmlspecialchars($customer_details['phone'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="customer_email" value="<?= htmlspecialchars($customer['email'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Pickup Date</label>
                    <input type="date" name="rental_date" id="rental_date" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                </div>
                
                <div class="form-group">
                    <label>Return Date</label>
                    <input type="date" name="return_date" id="return_date" required>
                </div>
                
                <div class="form-group">
                    <label>Price per day</label>
                    <input type="text" id="price_display" readonly style="background:#f5f5f5;">
                </div>
                
                <div class="form-group">
                    <label>Total Days</label>
                    <input type="text" id="total_days_display" readonly style="background:#f5f5f5;">
                </div>
                
                <div class="form-group">
                    <label>Total Cost</label>
                    <input type="text" id="total_cost_display" readonly style="background:#f5f5f5; font-weight:bold; color:#667eea;">
                </div>
                
                <button type="submit" class="btn-submit">Confirm Booking</button>
            </div>
        </form>
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

// Booking Modal Functions
let currentCarPrice = 0;

function openBookingModal(carId, carName, pricePerDay) {
    document.getElementById('car_id').value = carId;
    document.getElementById('car_name_display').value = carName;
    document.getElementById('price_display').value = '₱' + Number(pricePerDay).toLocaleString() + ' / day';
    currentCarPrice = pricePerDay;
    document.getElementById('bookingModal').classList.add('active');
    
    // Set minimum return date (at least 1 day after pickup)
    const rentalDateInput = document.getElementById('rental_date');
    const returnDateInput = document.getElementById('return_date');
    
    rentalDateInput.addEventListener('change', updateTotalCost);
    returnDateInput.addEventListener('change', updateTotalCost);
}

function closeBookingModal() {
    document.getElementById('bookingModal').classList.remove('active');
}

function updateTotalCost() {
    const rentalDate = document.getElementById('rental_date').value;
    const returnDate = document.getElementById('return_date').value;
    
    if (rentalDate && returnDate) {
        const start = new Date(rentalDate);
        const end = new Date(returnDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays > 0) {
            const totalCost = diffDays * currentCarPrice;
            document.getElementById('total_days_display').value = diffDays + ' days';
            document.getElementById('total_cost_display').value = '₱' + totalCost.toLocaleString();
        } else {
            document.getElementById('total_days_display').value = 'Invalid dates';
            document.getElementById('total_cost_display').value = '₱0';
        }
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMenuCustomer();
        closeBookingModal();
    }
});

// Close modal when clicking outside
document.getElementById('bookingModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBookingModal();
    }
});

// Make functions global
window.toggleMenuCustomer = toggleMenuCustomer;
window.closeMenuCustomer = closeMenuCustomer;
window.openBookingModal = openBookingModal;
window.closeBookingModal = closeBookingModal;
</script>

</body>
</html>