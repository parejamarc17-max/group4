<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireAdmin();
require_once '../includes/NotificationHelper.php';
require_once '../includes/PaymentHelper.php';

$success_message = '';
$error_message = '';

// Approve booking
if (isset($_GET['approve'])) {
    $rental_id = (int)$_GET['approve'];
    
    try {
        // Get rental details first
        $stmt = $pdo->prepare("SELECT r.*, c.car_name, u.id as customer_id, u.email as customer_email 
                               FROM rentals r 
                               JOIN car c ON r.car_id = c.id 
                               JOIN users u ON r.user_id = u.id 
                               WHERE r.id = ?");
        $stmt->execute([$rental_id]);
        $rental = $stmt->fetch();
        
        if (!$rental) {
            throw new Exception("Rental not found");
        }
        
        // Update rental approval status
        $stmt = $pdo->prepare("UPDATE rentals SET approval_status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $rental_id]);
        
        // Create payment request
        $paymentHelper = new PaymentHelper($pdo);
        $paymentHelper->createPaymentRequest($rental_id, $rental['total_cost'], $rental['customer_id'], 'bank_transfer');
        
        // Send notification to customer
        $notificationHelper = new NotificationHelper($pdo);
        $notificationHelper->notifyCustomer(
            $rental['customer_id'],
            'Booking Approved!',
            "Your booking for {$rental['car_name']} has been approved by admin. Please complete the payment to confirm your rental.",
            'payment',
            "customer/make_payment.php?rental_id=$rental_id"
        );
        
        $success_message = "Booking approved! Payment request sent to customer.";
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Reject booking
if (isset($_GET['reject'])) {
    $rental_id = (int)$_GET['reject'];
    $reason = isset($_GET['reason']) ? $_GET['reason'] : 'No reason provided';
    
    try {
        // Update rental
        $stmt = $pdo->prepare("UPDATE rentals SET approval_status = 'rejected', status = 'cancelled' WHERE id = ?");
        $stmt->execute([$rental_id]);
        
        // Get car_id to update car status back to available
        $stmt = $pdo->prepare("SELECT car_id, user_id FROM rentals WHERE id = ?");
        $stmt->execute([$rental_id]);
        $rental = $stmt->fetch();
        
        // Update car status
        $stmt = $pdo->prepare("UPDATE car SET status = 'available' WHERE id = ?");
        $stmt->execute([$rental['car_id']]);
        
        // Notify customer
        $notificationHelper = new NotificationHelper($pdo);
        $notificationHelper->notifyCustomer(
            $rental['user_id'],
            'Booking Rejected',
            "Your booking has been rejected by admin. Reason: $reason",
            'booking',
            'customer/my_bookings.php'
        );
        
        $success_message = "Booking rejected and customer notified.";
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get pending bookings
$stmt = $pdo->prepare("SELECT r.*, c.car_name, c.brand, c.model, c.image, u.username, u.email as customer_email, u.phone as customer_phone
                       FROM rentals r 
                       JOIN car c ON r.car_id = c.id 
                       JOIN users u ON r.user_id = u.id 
                       WHERE r.approval_status = 'pending' 
                       ORDER BY r.created_at ASC");
$stmt->execute();
$pending_bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approve Bookings - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/worker.css">
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
            <h2><i class="fas fa-clock"></i> Approve Bookings</h2>
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
    <img src="../assets/images/logo.png" class="profile-img" style="width:60px;height:60px;border-radius:50%;margin:10px auto;display:block;">
    <h2> DRIVE ADMIN</h2>
    <a href="dashboard.php" class="btn-nav"> Dashboard</a>
    <a href="approve_booking.php" class="btn-nav active"> Approve Bookings</a>
    <a href="verify_payment.php" class="btn-nav"> Verify Payments</a>
    <a href="manage_car.php" class="btn-nav"> Manage Cars</a>
    <a href="rentals.php" class="btn-nav"> Rentals</a>
    <a href="products.php" class="btn-nav"> Products</a>
    <a href="sales.php" class="btn-nav"> Sales</a>
    <a href="worker_list.php" class="btn-nav"> Worker List</a>
    <a href="pending_workers.php" class="btn-nav"> Pending Workers</a>
    <a href="../p_login/logout.php" class="btn-nav"> Logout</a>
</div>

<div class="overlay" id="adminOverlay" onclick="closeMenuAdmin()"></div>

<div class="dashboard">
    <div class="main" style="margin-left: 0; width: 100%;">
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success_message ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $error_message ?>
            </div>
        <?php endif; ?>

        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-clock"></i> Pending Bookings (Need Approval)</h2>
            </div>
            
            <?php if (empty($pending_bookings)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>No pending bookings to approve.</p>
                </div>
            <?php else: ?>
                <div class="rentals-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Rental Period</th>
                                <th>Total Days</th>
                                <th>Total Cost</th>
                                <th>Booking Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_bookings as $booking): ?>
                                <tr>
                                    <td data-label="Customer">
                                        <strong><?= htmlspecialchars($booking['username']) ?></strong><br>
                                        <small><?= $booking['customer_email'] ?></small><br>
                                        <small><?= $booking['customer_phone'] ?></small>
                                    </td>
                                    <td data-label="Car">
                                        <?= htmlspecialchars($booking['car_name']) ?><br>
                                        <small><?= $booking['brand'] . ' ' . $booking['model'] ?></small>
                                    </td>
                                    <td data-label="Period">
                                        <?= date('M d, Y', strtotime($booking['rental_date'])) ?> →<br>
                                        <?= date('M d, Y', strtotime($booking['return_date'])) ?>
                                     </td>
                                    <td data-label="Days"><?= $booking['total_days'] ?> days</td>
                                    <td data-label="Total" style="color:#28a745; font-weight:700;">₱<?= number_format($booking['total_cost'], 2) ?></td>
                                    <td data-label="Booked"><?= date('M d, Y', strtotime($booking['created_at'])) ?></td>
                                    <td data-label="Actions">
                                        <button class="btn-cancel" style="background: #28a745; border-color: #28a745;" onclick="window.location.href='?approve=<?= $booking['id'] ?>'" title="Approve this booking? Payment request will be sent to customer.">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn-cancel" onclick="showRejectModal(<?= $booking['id'] ?>)">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Reject Booking</h3>
            <button class="close-modal" onclick="closeRejectModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form method="GET" action="">
                <input type="hidden" name="reject" id="reject_id">
                <div class="form-group">
                    <label>Reason for rejection</label>
                    <textarea name="reason" rows="3" required placeholder="Enter reason..."></textarea>
                </div>
                <button type="submit" class="btn-submit">Submit Rejection</button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleMenuAdmin() {
    const menu = document.getElementById("adminMenu");
    const overlay = document.getElementById("adminOverlay");
    const hamburger = document.querySelector('.hamburger-btn');

    if (!menu) return;

    if (menu.classList.contains("active")) {
        menu.classList.remove("active");
        if (overlay) overlay.classList.remove("active");
        if (hamburger) hamburger.classList.remove('active');
    } else {
        menu.classList.add("active");
        if (overlay) overlay.classList.add("active");
        if (hamburger) hamburger.classList.add('active');
    }
}

function closeMenuAdmin() {
    const menu = document.getElementById("adminMenu");
    const overlay = document.getElementById("adminOverlay");
    const hamburger = document.querySelector('.hamburger-btn');

    if (menu) menu.classList.remove("active");
    if (overlay) overlay.classList.remove("active");
    if (hamburger) hamburger.classList.remove('active');
}

function showRejectModal(rentalId) {
    document.getElementById('reject_id').value = rentalId;
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>

</body>
</html>
