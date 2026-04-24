<?php
require_once 'header.php';
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
            "Your booking for {$rental['car_name']} has been approved. Please complete the payment to confirm your rental.",
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
            "Your booking has been rejected. Reason: $reason",
            'booking',
            'customer/my_bookings.php'
        );
        
        $success_message = "Booking rejected and customer notified.";
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get pending bookings
$stmt = $pdo->prepare("SELECT r.*, c.car_name, c.brand, c.model, c.image, u.username 
                       FROM rentals r 
                       JOIN car c ON r.car_id = c.id 
                       JOIN users u ON r.user_id = u.id 
                       WHERE r.approval_status = 'pending' 
                       ORDER BY r.created_at ASC");
$stmt->execute();
$pending_bookings = $stmt->fetchAll();
?>

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
                                <strong><?= htmlspecialchars($booking['customer_name']) ?></strong><br>
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
                                <a href="?approve=<?= $booking['id'] ?>" class="btn-return" onclick="return confirm('Approve this booking? Payment request will be sent to customer.')">
                                    <i class="fas fa-check"></i> Approve
                                </a>
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
function showRejectModal(rentalId) {
    document.getElementById('reject_id').value = rentalId;
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>

<?php require_once 'footer.php'; ?>