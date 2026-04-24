<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireAdmin();
require_once '../includes/NotificationHelper.php';

$success_message = '';
$error_message = '';

// First, ensure database columns exist
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
        return false;
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

// Get pending payments from payment_requests
$payment_requests = [];
try {
    // Check if verified_by column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM payment_requests LIKE 'verified_by'");
    $has_verified_by = $stmt->fetch() !== false;
    
    if ($has_verified_by) {
        $stmt = $pdo->prepare("SELECT pr.*, r.car_id, r.customer_name, r.customer_phone, r.customer_email, r.rental_date, r.return_date, r.total_cost,
                               c.car_name, c.brand, c.model, u.username as customer_username
                               FROM payment_requests pr
                               JOIN rentals r ON pr.rental_id = r.id 
                               JOIN car c ON r.car_id = c.id 
                               LEFT JOIN users u ON r.user_id = u.id
                               WHERE pr.status = 'paid'
                               ORDER BY pr.created_at DESC");
    } else {
        $stmt = $pdo->prepare("SELECT pr.*, r.car_id, r.customer_name, r.customer_phone, r.customer_email, r.rental_date, r.return_date, r.total_cost,
                               c.car_name, c.brand, c.model, u.username as customer_username
                               FROM payment_requests pr
                               JOIN rentals r ON pr.rental_id = r.id 
                               JOIN car c ON r.car_id = c.id 
                               LEFT JOIN users u ON r.user_id = u.id
                               WHERE pr.status = 'paid'
                               ORDER BY pr.created_at DESC");
    }
    $stmt->execute();
    $payment_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_message = "Error loading payment requests: " . $e->getMessage();
}

// Get pending payments from payments table
$payments = [];
try {
    // Check if verified_by column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM payments LIKE 'verified_by'");
    $has_verified_by = $stmt->fetch() !== false;
    
    if ($has_verified_by) {
        $stmt = $pdo->prepare("SELECT p.*, r.car_id, r.customer_name, r.customer_phone, r.customer_email, r.rental_date, r.return_date, r.total_cost,
                               c.car_name, c.brand, c.model, u.username as customer_username
                               FROM payments p
                               JOIN rentals r ON p.rental_id = r.id 
                               JOIN car c ON r.car_id = c.id 
                               LEFT JOIN users u ON r.user_id = u.id
                               WHERE p.status = 'pending'
                               ORDER BY p.created_at DESC");
    } else {
        $stmt = $pdo->prepare("SELECT p.*, r.car_id, r.customer_name, r.customer_phone, r.customer_email, r.rental_date, r.return_date, r.total_cost,
                               c.car_name, c.brand, c.model, u.username as customer_username
                               FROM payments p
                               JOIN rentals r ON p.rental_id = r.id 
                               JOIN car c ON r.car_id = c.id 
                               LEFT JOIN users u ON r.user_id = u.id
                               WHERE p.status = 'pending'
                               ORDER BY p.created_at DESC");
    }
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_message = "Error loading payments: " . $e->getMessage();
}

// Get verified payments (this week)
$verified_payments = [];
try {
    // Try payment_requests first
    $stmt = $pdo->prepare("SELECT pr.*, r.customer_name, c.car_name, u.username as verified_by_name
                           FROM payment_requests pr
                           JOIN rentals r ON pr.rental_id = r.id
                           JOIN car c ON r.car_id = c.id
                           LEFT JOIN users u ON pr.verified_by = u.id
                           WHERE pr.status = 'verified' AND pr.verified_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                           ORDER BY pr.verified_at DESC");
    $stmt->execute();
    $verified_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If that fails, try without verified_by
    try {
        $stmt = $pdo->prepare("SELECT pr.*, r.customer_name, c.car_name
                               FROM payment_requests pr
                               JOIN rentals r ON pr.rental_id = r.id
                               JOIN car c ON r.car_id = c.id
                               WHERE pr.status = 'verified'
                               ORDER BY pr.id DESC LIMIT 10");
        $stmt->execute();
        $verified_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e2) {
        $error_message = "Error loading verified payments: " . $e2->getMessage();
    }
}

require_once 'header.php';
?>

<div class="container-fluid">
    <h1><i class="fas fa-check-circle"></i> Payment Verification</h1>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Pending Payment Requests -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Pending Payment Requests</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($payment_requests)): ?>
                        <p class="text-muted">No pending payment requests</p>
                    <?php else: ?>
                        <?php foreach ($payment_requests as $payment): ?>
                            <div class="payment-item mb-3 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5><?= htmlspecialchars($payment['car_name']) ?></h5>
                                        <p><strong>Customer:</strong> <?= htmlspecialchars($payment['customer_name']) ?></p>
                                        <p><strong>Amount:</strong> ₱<?= number_format($payment['total_cost'], 2) ?></p>
                                        <p><strong>Method:</strong> <?= htmlspecialchars($payment['payment_method']) ?></p>
                                        <p><strong>Date:</strong> <?= date('M d, Y', strtotime($payment['rental_date'])) ?></p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <a href="?verify=<?= $payment['id'] ?>&source=payment_requests" 
                                           class="btn btn-success btn-sm" 
                                           onclick="return confirm('Verify this payment?')">
                                            <i class="fas fa-check"></i> Verify
                                        </a>
                                        <a href="?reject=<?= $payment['id'] ?>&source=payment_requests&reason=Invalid" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Reject this payment?')">
                                            <i class="fas fa-times"></i> Reject
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Pending Payments -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Pending Payments</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                        <p class="text-muted">No pending payments</p>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <div class="payment-item mb-3 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5><?= htmlspecialchars($payment['car_name']) ?></h5>
                                        <p><strong>Customer:</strong> <?= htmlspecialchars($payment['customer_name']) ?></p>
                                        <p><strong>Amount:</strong> ₱<?= number_format($payment['total_cost'], 2) ?></p>
                                        <p><strong>Method:</strong> <?= htmlspecialchars($payment['payment_method']) ?></p>
                                        <p><strong>Date:</strong> <?= date('M d, Y', strtotime($payment['rental_date'])) ?></p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <a href="?verify=<?= $payment['id'] ?>&source=payments" 
                                           class="btn btn-success btn-sm" 
                                           onclick="return confirm('Verify this payment?')">
                                            <i class="fas fa-check"></i> Verify
                                        </a>
                                        <a href="?reject=<?= $payment['id'] ?>&source=payments&reason=Invalid" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Reject this payment?')">
                                            <i class="fas fa-times"></i> Reject
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recently Verified -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-check-double"></i> Recently Verified (This Week)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($verified_payments)): ?>
                        <p class="text-muted">No verified payments this week</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>Customer</th>
                                        <th>Car</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Verified By</th>
                                        <th>Verified At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($verified_payments as $payment): ?>
                                        <tr>
                                            <td><?= $payment['id'] ?></td>
                                            <td><?= htmlspecialchars($payment['customer_name']) ?></td>
                                            <td><?= htmlspecialchars($payment['car_name']) ?></td>
                                            <td>₱<?= number_format($payment['total_cost'], 2) ?></td>
                                            <td><?= htmlspecialchars($payment['payment_method']) ?></td>
                                            <td><?= htmlspecialchars($payment['verified_by_name'] ?? 'Admin') ?></td>
                                            <td><?= isset($payment['verified_at']) ? date('M d, Y h:i A', strtotime($payment['verified_at'])) : 'N/A' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
