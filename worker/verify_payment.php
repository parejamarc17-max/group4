<?php
require_once 'header.php';

$success_message = '';
$error_message = '';

// Verify payment
if (isset($_GET['verify_payment'])) {
    $payment_id = $_GET['verify_payment'];
    $rental_id = $_GET['rental_id'];
    
    try {
        // Update payment status to verified
        $stmt = $pdo->prepare("UPDATE payments SET status = 'verified', verified_by = ?, verified_at = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $payment_id]);
        
        // Update rental payment status to paid
        $stmt = $pdo->prepare("UPDATE rentals SET payment_status = 'paid' WHERE id = ?");
        $stmt->execute([$rental_id]);
        
        $success_message = "Payment verified successfully!";
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Reject payment
if (isset($_GET['reject_payment'])) {
    $payment_id = $_GET['reject_payment'];
    $rental_id = $_GET['rental_id'];
    
    try {
        // Update payment status to failed
        $stmt = $pdo->prepare("UPDATE payments SET status = 'failed', notes = CONCAT(notes, ' - Payment rejected by worker') WHERE id = ?");
        $stmt->execute([$payment_id]);
        
        // Update rental payment status back to pending
        $stmt = $pdo->prepare("UPDATE rentals SET payment_status = 'pending' WHERE id = ?");
        $stmt->execute([$rental_id]);
        
        $success_message = "Payment rejected! Customer needs to resubmit.";
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get pending payments
$stmt = $pdo->prepare("SELECT p.*, r.car_id, r.customer_name, r.customer_phone, r.customer_email, r.rental_date, r.return_date, r.total_cost,
                       c.car_name, c.brand, c.model, u.username as customer_username
                       FROM payments p
                       JOIN rentals r ON p.rental_id = r.id
                       JOIN car c ON r.car_id = c.id
                       LEFT JOIN users u ON r.user_id = u.id
                       WHERE p.status = 'pending'
                       ORDER BY p.created_at ASC");
$stmt->execute();
$pending_payments = $stmt->fetchAll();

// Get verified payments (this week)
$stmt = $pdo->prepare("SELECT p.*, r.customer_name, c.car_name, u.username as verified_by_name
                       FROM payments p
                       JOIN rentals r ON p.rental_id = r.id
                       JOIN car c ON r.car_id = c.id
                       LEFT JOIN users u ON p.verified_by = u.id
                       WHERE p.status = 'verified' AND p.verified_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                       ORDER BY p.verified_at DESC");
$stmt->execute();
$verified_payments = $stmt->fetchAll();

// Get payment statistics
$stmt = $pdo->query("SELECT COUNT(*) as pending FROM payments WHERE status = 'pending'");
$pending_count = $stmt->fetch()['pending'];

$stmt = $pdo->query("SELECT SUM(amount) as total_verified FROM payments WHERE status = 'verified' AND verified_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$total_verified = $stmt->fetch()['total_verified'] ?? 0;
?>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?= $success_message ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-error"><?= $error_message ?></div>
<?php endif; ?>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3><?= $pending_count ?></h3>
            <p>Pending Payments</p>
        </div>
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>₱<?= number_format($total_verified, 2) ?></h3>
            <p>Monthly Revenue</p>
        </div>
        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
    </div>
</div>

<!-- Pending Payments Section -->
<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-hourglass-half"></i> Pending Payments (Need Verification)</h2>
    </div>
    
    <?php if (empty($pending_payments)): ?>
        <div class="empty-state">
            <i class="fas fa-check-circle"></i>
            <p>No pending payments to verify.</p>
        </div>
    <?php else: ?>
        <div class="rentals-table">
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Car</th>
                        <th>Rental Period</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Reference</th>
                        <th>Receipt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_payments as $payment): ?>
                        <tr>
                            <td data-label="Customer">
                                <strong><?= htmlspecialchars($payment['customer_name']) ?></strong><br>
                                <small><?= $payment['customer_phone'] ?></small>
                            </td>
                            <td data-label="Car"><?= htmlspecialchars($payment['car_name']) ?><br><small><?= $payment['brand'] . ' ' . $payment['model'] ?></small></td>
                            <td data-label="Period">
                                <?= date('M d', strtotime($payment['rental_date'])) ?> →<br>
                                <?= date('M d, Y', strtotime($payment['return_date'])) ?>
                            </td>
                            <td data-label="Amount" style="color:#28a745; font-weight:700;">₱<?= number_format($payment['amount'], 2) ?></td>
                            <td data-label="Method">
                                <?php
                                $method_icons = [
                                    'cash' => '💰 Cash',
                                    'gcash' => '📱 GCash',
                                    'bank_transfer' => '🏦 Bank Transfer',
                                    'credit_card' => '💳 Credit Card'
                                ];
                                echo $method_icons[$payment['payment_method']] ?? $payment['payment_method'];
                                ?>
                            </td>
                            <td data-label="Reference"><?= htmlspecialchars($payment['reference_number'] ?? 'N/A') ?></td>
                            <td data-label="Receipt">
                                <?php if ($payment['receipt_image']): ?>
                                    <a href="../<?= $payment['receipt_image'] ?>" target="_blank" class="btn-return" style="background:#2196f3;">View Receipt</a>
                                <?php else: ?>
                                    No receipt
                                <?php endif; ?>
                            </td>
                            <td data-label="Actions">
                                <a href="?verify_payment=<?= $payment['id'] ?>&rental_id=<?= $payment['rental_id'] ?>" class="btn-return" onclick="return confirm('Verify this payment?')">✓ Verify</a>
                                <a href="?reject_payment=<?= $payment['id'] ?>&rental_id=<?= $payment['rental_id'] ?>" class="btn-cancel" onclick="return confirm('Reject this payment? Customer will need to resubmit.')">✗ Reject</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Recently Verified Payments -->
<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-check-circle"></i> Recently Verified Payments</h2>
    </div>
    
    <?php if (empty($verified_payments)): ?>
        <div class="empty-state">
            <i class="fas fa-clock"></i>
            <p>No verified payments this week.</p>
        </div>
    <?php else: ?>
        <div class="rentals-table">
            <table>
                <thead>
                    <tr>
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
                            <td data-label="Customer"><?= htmlspecialchars($payment['customer_name']) ?></td>
                            <td data-label="Car"><?= htmlspecialchars($payment['car_name']) ?></td>
                            <td data-label="Amount" style="color:#28a745;">₱<?= number_format($payment['amount'], 2) ?></td>
                            <td data-label="Method"><?= $payment['payment_method'] ?></td>
                            <td data-label="Verified By"><?= htmlspecialchars($payment['verified_by_name'] ?? 'Worker') ?></td>
                            <td data-label="Verified At"><?= date('M d, Y h:i A', strtotime($payment['verified_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>