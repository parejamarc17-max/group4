<?php
require_once 'header.php';

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM car");
$total_cars = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM car WHERE status = 'available'");
$available_cars = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM car WHERE status = 'rented'");
$rented_cars = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM car WHERE status = 'maintenance'");
$maintenance_cars = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM rentals WHERE status = 'active'");
$active_rentals = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM rentals WHERE approval_status = 'pending'");
$pending_approvals = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM payment_requests WHERE status = 'paid'");
$pending_verifications = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM rentals WHERE status = 'completed'");
$completed_rentals = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(total_cost) as total FROM rentals WHERE status = 'completed'");
$total_revenue = $stmt->fetch()['total'] ?? 0;

// Get today's date
$today = date('Y-m-d');

// Get today's pickups/returns
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM rentals WHERE rental_date = ? OR return_date = ?");
$stmt->execute([$today, $today]);
$todays_events = $stmt->fetch()['total'];

// Get recent rentals
$stmt = $pdo->prepare("SELECT r.*, c.car_name, c.brand, c.model, c.image 
                       FROM rentals r 
                       JOIN car c ON r.car_id = c.id 
                       ORDER BY r.created_at DESC 
                       LIMIT 8");
$stmt->execute();
$recent_rentals = $stmt->fetchAll();

// Get top rented cars
$stmt = $pdo->prepare("SELECT c.car_name, c.brand, COUNT(r.id) as rental_count 
                       FROM rentals r 
                       JOIN car c ON r.car_id = c.id 
                       GROUP BY r.car_id 
                       ORDER BY rental_count DESC 
                       LIMIT 5");
$stmt->execute();
$top_cars = $stmt->fetchAll();

// Get upcoming pickups
$stmt = $pdo->prepare("SELECT r.*, c.car_name, c.brand, c.model 
                       FROM rentals r 
                       JOIN car c ON r.car_id = c.id 
                       WHERE r.rental_date >= CURDATE() AND r.approval_status = 'approved' AND r.payment_status = 'paid'
                       ORDER BY r.rental_date ASC 
                       LIMIT 5");
$stmt->execute();
$upcoming_pickups = $stmt->fetchAll();

// Helper function to get display status
function getRentalDisplayStatus($rental) {
    if ($rental['approval_status'] === 'pending') {
        return ['text' => 'Pending Approval', 'class' => 'pending'];
    } elseif ($rental['approval_status'] === 'rejected') {
        return ['text' => 'Rejected', 'class' => 'rejected'];
    } elseif ($rental['approval_status'] === 'approved' && $rental['payment_status'] !== 'paid') {
        return ['text' => 'Payment Required', 'class' => 'payment-pending'];
    } elseif ($rental['payment_status'] === 'paid' && $rental['status'] === 'active') {
        return ['text' => 'Active', 'class' => 'active'];
    } elseif ($rental['status'] === 'completed') {
        return ['text' => 'Completed', 'class' => 'completed'];
    } elseif ($rental['status'] === 'cancelled') {
        return ['text' => 'Cancelled', 'class' => 'cancelled'];
    }
    return ['text' => ucfirst($rental['status']), 'class' => $rental['status']];
}
?>

<!-- Welcome Banner with Date -->
<div class="welcome-banner">
    <div class="welcome-text">
        <h1>Welcome back, <?= htmlspecialchars($worker['full_name'] ?? $worker['username']) ?>! 👋</h1>
        <p>Here's what's happening with your rental business today.</p>
    </div>
    <div class="date-badge">
        <div class="date"><?= date('F d, Y') ?></div>
        <div class="day"><?= date('l') ?></div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3><?= $total_cars ?></h3>
            <p>Total Fleet</p>
        </div>
        <div class="stat-icon"><i class="fas fa-car"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?= $available_cars ?></h3>
            <p>Available Now</p>
        </div>
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?= $rented_cars ?></h3>
            <p>Currently Rented</p>
        </div>
        <div class="stat-icon"><i class="fas fa-car-side"></i></div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);">
        <div class="stat-info">
            <h3><?= $pending_approvals ?></h3>
            <p>Pending Approvals</p>
        </div>
        <div class="stat-icon" style="background: #f39c12;"><i class="fas fa-clock"></i></div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #cce5ff 0%, #74b9ff 100%);">
        <div class="stat-info">
            <h3><?= $pending_verifications ?></h3>
            <p>Need Verification</p>
        </div>
        <div class="stat-icon" style="background: #0984e3;"><i class="fas fa-credit-card"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?= $active_rentals ?></h3>
            <p>Active Rentals</p>
        </div>
        <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?= $todays_events ?></h3>
            <p>Today's Pickups/Returns</p>
        </div>
        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>₱<?= number_format($total_revenue, 2) ?></h3>
            <p>Total Revenue</p>
        </div>
        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
    </div>
</div>

<!-- Quick Actions -->
<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
    </div>
    <div class="quick-actions-container">
        <?php if ($pending_approvals > 0): ?>
            <a href="approve_booking.php" class="btn-add priority-action" style="background: #f39c12;">
                <i class="fas fa-clock"></i> Approve Bookings (<?= $pending_approvals ?>)
            </a>
        <?php endif; ?>
        <?php if ($pending_verifications > 0): ?>
            <a href="verify_payment.php" class="btn-add priority-action" style="background: #0984e3;">
                <i class="fas fa-credit-card"></i> Verify Payments (<?= $pending_verifications ?>)
            </a>
        <?php endif; ?>
        <a href="manage_cars.php" class="btn-add">
            <i class="fas fa-plus"></i> Add New Car
        </a>
        <a href="manage_rentals.php" class="btn-add" style="background: #ff9800;">
            <i class="fas fa-calendar-check"></i> View Active Rentals
        </a>
        <a href="rental_history.php" class="btn-add" style="background: #6c757d;">
            <i class="fas fa-history"></i> Rental History
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
    <!-- Recent Rentals Section -->
    <div class="section" style="margin-bottom: 0;">
        <div class="section-header">
            <h2><i class="fas fa-clock"></i> Recent Rentals</h2>
            <a href="rental_history.php" class="btn-add" style="padding: 6px 12px; font-size: 0.75rem;">View All →</a>
        </div>
        
        <?php if (empty($recent_rentals)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No rentals yet.</p>
            </div>
        <?php else: ?>
            <div class="rentals-table">
                <table>
                    <thead>
                        <tr>
                            <th>Car</th>
                            <th>Customer</th>
                            <th>Period</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_rentals as $rental): ?>
                            <tr>
                                <td data-label="Car">
                                    <div class="car-info">
                                        <?php if ($rental['image']): ?>
                                            <img src="../uploads/<?= htmlspecialchars($rental['image']) ?>" class="car-thumb" alt="">
                                        <?php else: ?>
                                            <div class="car-thumb" style="display: flex; align-items: center; justify-content: center; background: #e0e0e0;">
                                                <i class="fas fa-car"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="car-name"><?= htmlspecialchars($rental['car_name']) ?></div>
                                            <div class="car-model"><?= htmlspecialchars($rental['brand']) ?></div>
                                        </div>
                                    </div>
                                 </td>
                                <td data-label="Customer">
                                    <strong><?= htmlspecialchars($rental['customer_name']) ?></strong><br>
                                    <small><?= htmlspecialchars($rental['customer_phone']) ?></small>
                                 </td>
                                <td data-label="Period">
                                    <?= date('M d', strtotime($rental['rental_date'])) ?> →<br>
                                    <?= date('M d, Y', strtotime($rental['return_date'])) ?>
                                 </td>
                                <td data-label="Status">
                                    <?php 
                                    $displayStatus = getRentalDisplayStatus($rental);
                                    $statusClass = $displayStatus['class'];
                                    $statusText = $displayStatus['text'];
                                    ?>
                                    <span class="status-badge status-<?= $statusClass ?>"><?= $statusText ?></span>
                                 </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Top Rented Cars -->
    <div class="section" style="margin-bottom: 0;">
        <div class="section-header">
            <h2><i class="fas fa-trophy"></i> Most Rented Cars</h2>
        </div>
        
        <?php if (empty($top_cars)): ?>
            <div class="empty-state">
                <i class="fas fa-chart-line"></i>
                <p>No rental data yet.</p>
            </div>
        <?php else: ?>
            <div class="top-cars-grid">
                <?php foreach ($top_cars as $index => $car): ?>
                    <div class="top-car-item">
                        <div class="top-car-icon">
                            <?php if ($index === 0): ?>
                                <i class="fas fa-crown"></i>
                            <?php else: ?>
                                <i class="fas fa-car"></i>
                            <?php endif; ?>
                        </div>
                        <div class="top-car-info">
                            <h4><?= htmlspecialchars($car['car_name']) ?></h4>
                            <p><?= htmlspecialchars($car['brand']) ?></p>
                        </div>
                        <div class="top-car-count">
                            <?= $car['rental_count'] ?> rentals
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Upcoming Pickups -->
<?php if (!empty($upcoming_pickups)): ?>
<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-calendar-alt"></i> Upcoming Pickups</h2>
    </div>
    <div class="rentals-table">
        <table>
            <thead>
                <tr>
                    <th>Car</th>
                    <th>Customer</th>
                    <th>Pickup Date</th>
                    <th>Return Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($upcoming_pickups as $pickup): ?>
                    <tr>
                        <td data-label="Car"><?= htmlspecialchars($pickup['car_name']) ?> (<?= $pickup['brand'] ?>)</td>
                        <td data-label="Customer"><?= htmlspecialchars($pickup['customer_name']) ?><br><small><?= $pickup['customer_phone'] ?></small></td>
                        <td data-label="Pickup Date"><?= date('M d, Y', strtotime($pickup['rental_date'])) ?></td>
                        <td data-label="Return Date"><?= date('M d, Y', strtotime($pickup['return_date'])) ?></td>
                        <td data-label="Action">
                            <a href="manage_rentals.php" class="btn-add" style="padding: 5px 12px; font-size: 0.7rem;">Prepare Car</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>