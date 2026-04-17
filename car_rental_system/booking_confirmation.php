<?php
// booking_confirmation.php
require_once 'config/database.php';
require_once 'includes/auth.php';
checkAuth();

if (!isset($_SESSION['booking_id'])) {
    header('Location: car.php');
    exit();
}

$booking_id = $_SESSION['booking_id'];

$stmt = $pdo->prepare("
    SELECT r.*, c.car_name, c.price_per_day, c.image 
    FROM rentals r 
    JOIN cars c ON r.car_id = c.id 
    WHERE r.id = ? AND r.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    unset($_SESSION['booking_id']);
    header('Location: car.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - CarRent</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .confirmation-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .success-icon {
            text-align: center;
            color: #28a745;
            font-size: 60px;
        }
        .booking-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .detail-label { font-weight: bold; color: #495057; }
        .total-price { font-size: 24px; color: #28a745; font-weight: bold; }
        .btn-pay { 
            background: #28a745; 
            color: white; 
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-pay:hover { background: #218838; }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-icon">✓</div>
        <h2 style="text-align: center; color: #28a745;">Booking Confirmed!</h2>
        <p style="text-align: center;">Your rental request has been successfully submitted.</p>
        
        <div class="booking-details">
            <h3 style="margin-top: 0;">Booking Details</h3>
            <div class="detail-row">
                <span class="detail-label">Booking ID:</span>
                <span>#<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Car:</span>
                <span><?= htmlspecialchars($booking['car_name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Customer Name:</span>
                <span><?= htmlspecialchars($booking['customer_name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Pickup Date:</span>
                <span><?= date('F j, Y', strtotime($booking['rental_date'])) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Return Date:</span>
                <span><?= date('F j, Y', strtotime($booking['return_date'])) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Days:</span>
                <span><?= $booking['total_days'] ?> days</span>
            </div>
            <div class="detail-row">
                <span class="detail-label total-price">Total Cost:</span>
                <span class="total-price">$<?= number_format($booking['total_cost'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span style="color: #ffc107;"><?= ucfirst($booking['status']) ?></span>
            </div>
        </div>
        
        <div style="text-align: center;">
            <br><br>
            <a href="car.php" style="color: #007bff;">← Back to Cars</a>
        </div>
    </div>
</body>
</html>

<?php
// Clear the session booking_id after displaying once
unset($_SESSION['booking_id']);
?>