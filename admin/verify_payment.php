<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireAdmin();
require_once '../includes/NotificationHelper.php';

$success_message = '';
$error_message = '';

// Ensure database columns exist
function ensureDatabaseColumns($pdo) {
    try {
        // Add verified_by and verified_at to payments table if missing
        $pdo->exec("ALTER TABLE payments ADD COLUMN IF NOT EXISTS verified_by INT NULL AFTER status");
        $pdo->exec("ALTER TABLE payments ADD COLUMN IF NOT EXISTS verified_at TIMESTAMP NULL AFTER verified_by");
        
        // Add verified_by and verified_at to payment_requests table if missing
        $pdo->exec("ALTER TABLE payment_requests ADD COLUMN IF NOT EXISTS verified_by INT NULL AFTER status");
        $pdo->exec("ALTER TABLE payment_requests ADD COLUMN IF NOT EXISTS verified_at TIMESTAMP NULL AFTER verified_by");
        
        return true;
    } catch (Exception $e) {
        // Continue even if columns already exist
        return true;
    }
}

// Ensure columns exist before proceeding
ensureDatabaseColumns($pdo);

// Verify payment
if (isset($_GET['verify'])) {
    $payment_id = (int)$_GET['verify'];
    $source_table = $_GET['source'] ?? 'payment_requests';
    
    try {
        if ($source_table === 'payment_requests') {
            // Check if verified_by column exists before using it
            $stmt = $pdo->query("SHOW COLUMNS FROM payment_requests LIKE 'verified_by'");
            $has_verified_by = $stmt->fetch() !== false;
            
            // Get payment details from payment_requests
            $stmt = $pdo->prepare("SELECT pr.*, r.car_id, r.user_id, r.rental_date, r.return_date, c.car_name, u.email as customer_email 
                                   FROM payment_requests pr 
                                   JOIN rentals r ON pr.rental_id = r.id 
                                   JOIN car c ON r.car_id = c.id 
                                   JOIN users u ON r.user_id = u.id 
                                   WHERE pr.id = ? AND pr.status = 'paid'");
            $stmt->execute([$payment_id]);
            $payment = $stmt->fetch();
            
            if (!$payment) {
                throw new Exception("Payment request not found or already processed");
            }
            
            // Update payment status - only use verified_by if column exists
            if ($has_verified_by) {
                $stmt = $pdo->prepare("UPDATE payment_requests SET status = 'verified', verified_by = ?, verified_at = NOW() WHERE id = ?");
                $stmt->execute([$_SESSION['user_id'], $payment_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE payment_requests SET status = 'verified' WHERE id = ?");
                $stmt->execute([$payment_id]);
            }
            
        } elseif ($source_table === 'payments') {
            // Check if verified_by column exists before using it
            $stmt = $pdo->query("SHOW COLUMNS FROM payments LIKE 'verified_by'");
            $has_verified_by = $stmt->fetch() !== false;
            
            // Get payment details from payments
            $stmt = $pdo->prepare("SELECT p.*, r.car_id, r.user_id, r.rental_date, r.return_date, c.car_name, u.email as customer_email 
                                   FROM payments p 
                                   JOIN rentals r ON p.rental_id = r.id 
                                   JOIN car c ON r.car_id = c.id 
                                   JOIN users u ON r.user_id = u.id 
                                   WHERE p.id = ? AND p.status = 'pending'");
            $stmt->execute([$payment_id]);
            $payment = $stmt->fetch();
            
            if (!$payment) {
                throw new Exception("Payment not found or already processed");
            }
            
            // Update payment status - only use verified_by if column exists
            if ($has_verified_by) {
                $stmt = $pdo->prepare("UPDATE payments SET status = 'verified', verified_by = ?, verified_at = NOW() WHERE id = ?");
                $stmt->execute([$_SESSION['user_id'], $payment_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE payments SET status = 'verified' WHERE id = ?");
                $stmt->execute([$payment_id]);
            }
        }
        
        // Update rental status to active
        $stmt = $pdo->prepare("UPDATE rentals SET status = 'active', payment_status = 'verified' WHERE id = ?");
        $stmt->execute([$payment['rental_id']]);
        
        // Update car status to rented
        $stmt = $pdo->prepare("UPDATE car SET status = 'rented' WHERE id = ?");
        $stmt->execute([$payment['car_id']]);
        
        // Send notification to customer
        $notificationHelper = new NotificationHelper($pdo);
        $notificationHelper->notifyCustomer(
            $payment['user_id'],
            'Payment Verified!',
            "Your payment for {$payment['car_name']} has been verified. Your rental is now active!",
            'booking',
            'customer/my_bookings.php'
        );
        
        $success_message = "Payment verified successfully! Rental is now active.";
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Reject payment
if (isset($_GET['reject'])) {
    $payment_id = (int)$_GET['reject'];
    $source_table = $_GET['source'] ?? 'payment_requests';
    $reason = isset($_GET['reason']) ? $_GET['reason'] : 'Invalid payment';
    
    try {
        if ($source_table === 'payment_requests') {
            // Get payment details from payment_requests
            $stmt = $pdo->prepare("SELECT pr.*, r.car_id, r.user_id, c.car_name, u.email as customer_email 
                                   FROM payment_requests pr 
                                   JOIN rentals r ON pr.rental_id = r.id 
                                   JOIN car c ON r.car_id = c.id 
                                   JOIN users u ON r.user_id = u.id 
                                   WHERE pr.id = ?");
            $stmt->execute([$payment_id]);
            $payment = $stmt->fetch();
            
            if (!$payment) {
                throw new Exception("Payment request not found");
            }
            
            // Update payment status
            $stmt = $pdo->prepare("UPDATE payment_requests SET status = 'failed', notes = ? WHERE id = ?");
            $stmt->execute([$reason, $payment_id]);
            
        } elseif ($source_table === 'payments') {
            // Get payment details from payments
            $stmt = $pdo->prepare("SELECT p.*, r.car_id, r.user_id, c.car_name, u.email as customer_email 
                                   FROM payments p 
                                   JOIN rentals r ON p.rental_id = r.id 
                                   JOIN car c ON r.car_id = c.id 
                                   JOIN users u ON r.user_id = u.id 
                                   WHERE p.id = ?");
            $stmt->execute([$payment_id]);
            $payment = $stmt->fetch();
            
            if (!$payment) {
                throw new Exception("Payment not found");
            }
            
            // Update payment status
            $stmt = $pdo->prepare("UPDATE payments SET status = 'rejected', notes = ? WHERE id = ?");
            $stmt->execute([$reason, $payment_id]);
        }
        
        // Update rental status to cancelled
        $stmt = $pdo->prepare("UPDATE rentals SET status = 'cancelled', payment_status = 'failed' WHERE id = ?");
        $stmt->execute([$payment['rental_id']]);
        
        // Update car status back to available
        $stmt = $pdo->prepare("UPDATE car SET status = 'available' WHERE id = ?");
        $stmt->execute([$payment['car_id']]);
        
        // Send notification to customer
        $notificationHelper = new NotificationHelper($pdo);
        $notificationHelper->notifyCustomer(
            $payment['user_id'],
            'Payment Rejected',
            "Your payment for {$payment['car_name']} was rejected. Reason: $reason",
            'payment',
            'customer/my_bookings.php'
        );
        
        $success_message = "Payment rejected and customer notified.";
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get pending payment verifications - check both tables
$pending_payments = [];

// Check payment_requests table
$stmt = $pdo->prepare("SELECT pr.*, r.car_id, r.user_id, r.rental_date, r.return_date, c.car_name, c.brand, c.model, u.username, u.email as customer_email, u.phone as customer_phone,
                       'payment_requests' as source_table, pr.id as payment_id
                       FROM payment_requests pr 
                       JOIN rentals r ON pr.rental_id = r.id 
                       JOIN car c ON r.car_id = c.id 
                       JOIN users u ON r.user_id = u.id 
                       WHERE pr.status = 'paid' 
                       ORDER BY pr.sent_at ASC");
$stmt->execute();
$payment_requests = $stmt->fetchAll();

// Check payments table
$stmt = $pdo->prepare("SELECT p.*, r.car_id, r.user_id, r.rental_date, r.return_date, c.car_name, c.brand, c.model, u.username, u.email as customer_email, u.phone as customer_phone,
                       'payments' as source_table, p.id as payment_id
                       FROM payments p
                       JOIN rentals r ON p.rental_id = r.id
                       JOIN car c ON r.car_id = c.id
                       JOIN users u ON r.user_id = u.id
                       WHERE p.status = 'pending'
                       ORDER BY p.created_at ASC");
$stmt->execute();
$payments = $stmt->fetchAll();

// Merge both results
$pending_payments = array_merge($payment_requests, $payments);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Payments - Admin</title>
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
            <h2><i class="fas fa-credit-card"></i> Verify Payments</h2>
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
    <a href="approve_booking.php" class="btn-nav"> Approve Bookings</a>
    <a href="verify_payment.php" class="btn-nav active"> Verify Payments</a>
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
                <h2><i class="fas fa-credit-card"></i> Pending Payment Verifications</h2>
            </div>
            
            <?php if (empty($pending_payments)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>No pending payment verifications.</p>
                </div>
            <?php else: ?>
                <div class="rentals-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_payments as $payment): ?>
                                <tr>
                                    <td data-label="Customer">
                                        <strong><?= htmlspecialchars($payment['username']) ?></strong><br>
                                        <small><?= $payment['customer_email'] ?></small><br>
                                        <small><?= $payment['customer_phone'] ?></small>
                                    </td>
                                    <td data-label="Car">
                                        <?= htmlspecialchars($payment['car_name']) ?><br>
                                        <small><?= $payment['brand'] . ' ' . $payment['model'] ?></small>
                                    </td>
                                    <td data-label="Amount" style="color:#28a745; font-weight:700;">₱<?= number_format($payment['amount'], 2) ?></td>
                                    <td data-label="Method">
                                        <?= ucfirst($payment['payment_method'] ?? 'Bank Transfer') ?>
                                    </td>
                                    <td data-label="Submitted"><?= date('M d, Y H:i', strtotime($payment['sent_at'] ?? $payment['created_at'])) ?></td>
                                    <td data-label="Actions">
                                        <button class="btn-cancel" style="background: #28a745; border-color: #28a745;" onclick="window.location.href='?verify=<?= $payment['payment_id'] ?>&source=<?= $payment['source_table'] ?>'" title="Verify this payment? This will activate the rental.">
                                            <i class="fas fa-check"></i> Verify
                                        </button>
                                        <button class="btn-cancel" onclick="showRejectModal(<?= $payment['payment_id'] ?>, '<?= $payment['source_table'] ?>')">
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
            <h3>Reject Payment</h3>
            <button class="close-modal" onclick="closeRejectModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form method="GET" action="">
                <input type="hidden" name="reject" id="reject_id">
                <input type="hidden" name="source" id="reject_source">
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

function showRejectModal(paymentId, sourceTable) {
    document.getElementById('reject_id').value = paymentId;
    document.getElementById('reject_source').value = sourceTable;
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>

</body>
</html>
