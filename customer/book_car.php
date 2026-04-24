<?php
require_once '../config/auth.php';
require_once '../config/database.php';
checkAuth();

if ($_SESSION['role'] !== 'customer') {
    header('Location: ../p_login/login.php');
    exit();
}

$car_id = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;

// Get car details
$stmt = $pdo->prepare("SELECT * FROM car WHERE id = ? AND status = 'available'");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    header('Location: ../index.php?error=Car not available');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get customer details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$customer = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM customers WHERE user_id = ?");
$stmt->execute([$user_id]);
$customer_details = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Car - DriveGo</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            padding: 40px 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .booking-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .car-details-card, .booking-form-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }
        .card-body { padding: 25px; }
        .car-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .car-price {
            font-size: 1.8rem;
            font-weight: 700;
            color: #667eea;
            margin: 15px 0;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
        }
        .date-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .total-cost {
            background: #f0f2ff;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
        }
        .total-cost h3 { color: #667eea; font-size: 1.5rem; }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-info { background: #d1ecf1; color: #0c5460; }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
        }
        @media (max-width: 768px) {
            .booking-wrapper { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Cars</a>
    
    <div class="booking-wrapper">
        <!-- Car Details -->
        <div class="car-details-card">
            <img src="<?= !empty($car['image']) ? '../uploads/cars/' . $car['image'] : '../assets/images/default-car.jpg' ?>" class="car-image" alt="<?= $car['car_name'] ?>">
            <div class="card-body">
                <h2><?= htmlspecialchars($car['car_name']) ?></h2>
                <p class="car-price">₱<?= number_format($car['price_per_day'], 2) ?> <span style="font-size: 0.9rem;">/ day</span></p>
                <p><strong>Brand:</strong> <?= htmlspecialchars($car['brand']) ?></p>
                <p><strong>Model:</strong> <?= htmlspecialchars($car['model']) ?></p>
                <p><strong>Transmission:</strong> <?= htmlspecialchars($car['transmission'] ?? 'N/A') ?></p>
                <p><strong>Fuel Type:</strong> <?= htmlspecialchars($car['fuel_type'] ?? 'N/A') ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($car['location'] ?? 'Main Branch') ?></p>
            </div>
        </div>
        
        <!-- Booking Form -->
        <div class="booking-form-card">
            <div class="card-header">
                <h2><i class="fas fa-calendar-check"></i> Complete Your Booking</h2>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> After booking, our team will review and send you payment instructions within 24 hours.
                </div>
                
                <form method="POST" action="process_booking.php">
                    <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                    <input type="hidden" name="price_per_day" value="<?= $car['price_per_day'] ?>">
                    
                    <div class="form-group">
                        <label>Your Name</label>
                        <input type="text" name="customer_name" value="<?= htmlspecialchars($customer['full_name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="customer_email" value="<?= htmlspecialchars($customer['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="customer_phone" value="<?= htmlspecialchars($customer_details['phone'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Pick-up Location</label>
                        <input type="text" name="location" value="<?= htmlspecialchars($car['location'] ?? 'Main Branch') ?>" required>
                    </div>
                    
                    <div class="date-range">
                        <div class="form-group">
                            <label>Pick-up Date</label>
                            <input type="date" name="rental_date" id="rental_date" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                        <div class="form-group">
                            <label>Return Date</label>
                            <input type="date" name="return_date" id="return_date" required>
                        </div>
                    </div>
                    
                    <div class="total-cost" id="totalCostDisplay">
                        <h3>₱0.00</h3>
                        <p>Total for 0 days</p>
                    </div>
                    
                    <button type="submit" class="btn-submit">Submit Booking Request</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const rentalDate = document.getElementById('rental_date');
    const returnDate = document.getElementById('return_date');
    const pricePerDay = <?= $car['price_per_day'] ?>;
    const totalDisplay = document.getElementById('totalCostDisplay');
    
    function calculateTotal() {
        if (rentalDate.value && returnDate.value) {
            const start = new Date(rentalDate.value);
            const end = new Date(returnDate.value);
            const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
            
            if (days > 0) {
                const total = days * pricePerDay;
                totalDisplay.innerHTML = `<h3>₱${total.toLocaleString()}</h3><p>Total for ${days} days (₱${pricePerDay.toLocaleString()}/day)</p>`;
            } else {
                totalDisplay.innerHTML = `<h3>₱0.00</h3><p>Select valid dates</p>`;
            }
        }
    }
    
    rentalDate.addEventListener('change', calculateTotal);
    returnDate.addEventListener('change', calculateTotal);
</script>
</body>
</html>