<?php
require_once 'header.php';

$success_message = '';
$error_message = '';

// Handle Process Return
if (isset($_GET['return_rental'])) {
    $rental_id = $_GET['return_rental'];
    try {
        // Get car_id from rental
        $stmt = $pdo->prepare("SELECT car_id FROM rentals WHERE id = ?");
        $stmt->execute([$rental_id]);
        $rental = $stmt->fetch();
        
        // Update rental status to completed
        $stmt = $pdo->prepare("UPDATE rentals SET status = 'completed', payment_status = 'paid' WHERE id = ?");
        $stmt->execute([$rental_id]);
        
        // Update car status back to available
        $stmt = $pdo->prepare("UPDATE car SET status = 'available' WHERE id = ?");
        $stmt->execute([$rental['car_id']]);
        
        $success_message = "Rental marked as completed! Car is now available.";
    } catch (PDOException $e) {
        $error_message = "Error processing return: " . $e->getMessage();
    }
}

// Handle Cancel Rental
if (isset($_GET['cancel_rental'])) {
    $rental_id = $_GET['cancel_rental'];
    try {
        // Get car_id from rental
        $stmt = $pdo->prepare("SELECT car_id FROM rentals WHERE id = ?");
        $stmt->execute([$rental_id]);
        $rental = $stmt->fetch();
        
        // Update rental status to cancelled
        $stmt = $pdo->prepare("UPDATE rentals SET status = 'cancelled', payment_status = 'refunded' WHERE id = ?");
        $stmt->execute([$rental_id]);
        
        // Update car status back to available
        $stmt = $pdo->prepare("UPDATE car SET status = 'available' WHERE id = ?");
        $stmt->execute([$rental['car_id']]);
        
        $success_message = "Rental cancelled successfully!";
    } catch (PDOException $e) {
        $error_message = "Error cancelling rental: " . $e->getMessage();
    }
}

// Get active rentals with customer info
$stmt = $pdo->prepare("SELECT r.*, c.car_name, c.brand, c.model, c.image 
                       FROM rentals r 
                       JOIN car c ON r.car_id = c.id 
                       WHERE r.status = 'active' 
                       ORDER BY r.rental_date ASC");
$stmt->execute();
$active_rentals = $stmt->fetchAll();
?>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-calendar-check"></i> Active Rentals</h2>
    </div>
    
    <?php if (empty($active_rentals)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p>No active rentals at the moment.</p>
        </div>
    <?php else: ?>
        <div class="rentals-table">
            <table>
                <thead>
                    <tr>
                        <th>Car</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Pickup Date</th>
                        <th>Return Date</th>
                        <th>Days Left</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_rentals as $rental): 
                        $days_left = max(0, ceil((strtotime($rental['return_date']) - time()) / 86400));
                    ?>
                        <tr>
                            <td data-label="Car"><?= htmlspecialchars($rental['car_name']) ?><br><small><?= $rental['brand'] . ' ' . $rental['model'] ?></small></td>
                            <td data-label="Customer"><?= htmlspecialchars($rental['customer_name']) ?></td>
                            <td data-label="Phone"><?= htmlspecialchars($rental['customer_phone']) ?></td>
                            <td data-label="Email"><?= htmlspecialchars($rental['customer_email']) ?></td>
                            <td data-label="Pickup"><?= date('M d, Y', strtotime($rental['rental_date'])) ?></td>
                            <td data-label="Return"><?= date('M d, Y', strtotime($rental['return_date'])) ?></td>
                            <td data-label="Days Left"><?= $days_left ?> days</td>
                            <td data-label="Total">₱<?= number_format($rental['total_cost'], 2) ?></td>
                            <td data-label="Actions">
                                <a href="?return_rental=<?= $rental['id'] ?>" class="btn-return" onclick="return confirm('Mark this rental as completed?')">Return</a>
                                <a href="?cancel_rental=<?= $rental['id'] ?>" class="btn-cancel" onclick="return confirm('Cancel this rental?')">Cancel</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>