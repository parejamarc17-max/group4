<?php
require_once 'header.php';

// Get completed/cancelled rentals
$stmt = $pdo->prepare("SELECT r.*, c.car_name, c.brand, c.model 
                       FROM rentals r 
                       JOIN car c ON r.car_id = c.id 
                       WHERE r.status IN ('completed', 'cancelled')
                       ORDER BY r.created_at DESC");
$stmt->execute();
$rental_history = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total_completed FROM rentals WHERE status = 'completed'");
$total_completed = $stmt->fetch()['total_completed'];

$stmt = $pdo->query("SELECT SUM(total_cost) as total_revenue FROM rentals WHERE status = 'completed'");
$total_revenue = $stmt->fetch()['total_revenue'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as total_cancelled FROM rentals WHERE status = 'cancelled'");
$total_cancelled = $stmt->fetch()['total_cancelled'];
?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3><?= $total_completed ?></h3>
            <p>Completed Rentals</p>
        </div>
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>₱<?= number_format($total_revenue, 2) ?></h3>
            <p>Total Revenue</p>
        </div>
        <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?= $total_cancelled ?></h3>
            <p>Cancelled Rentals</p>
        </div>
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
    </div>
</div>

<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-history"></i> Rental History</h2>
    </div>
    
    <?php if (empty($rental_history)): ?>
        <div class="empty-state">
            <i class="fas fa-history"></i>
            <p>No rental history yet.</p>
        </div>
    <?php else: ?>
        <div class="rentals-table">
            <table>
                <thead>
                    <tr>
                        <th>Car</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Rental Date</th>
                        <th>Return Date</th>
                        <th>Days</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rental_history as $rental): ?>
                        <tr>
                            <td data-label="Car"><?= htmlspecialchars($rental['car_name']) ?><br><small><?= $rental['brand'] . ' ' . $rental['model'] ?></small></td>
                            <td data-label="Customer"><?= htmlspecialchars($rental['customer_name']) ?></td>
                            <td data-label="Phone"><?= htmlspecialchars($rental['customer_phone']) ?></td>
                            <td data-label="Rental Date"><?= date('M d, Y', strtotime($rental['rental_date'])) ?></td>
                            <td data-label="Return Date"><?= date('M d, Y', strtotime($rental['return_date'])) ?></td>
                            <td data-label="Days"><?= $rental['total_days'] ?> days</td>
                            <td data-label="Total">₱<?= number_format($rental['total_cost'], 2) ?></td>
                            <td data-label="Status">
                                <span class="status-badge status-<?= $rental['status'] ?>"><?= ucfirst($rental['status']) ?></span>
                            </td>
                            <td data-label="Payment">
                                <span class="status-badge status-<?= $rental['payment_status'] ?>"><?= ucfirst($rental['payment_status']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>