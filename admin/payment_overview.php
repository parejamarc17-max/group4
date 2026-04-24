<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../p_login/login.php?error=Access denied');
    exit();
}

// Get payment statistics
$stmt = $pdo->query("SELECT 
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified,
                        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                        SUM(CASE WHEN status = 'refunded' THEN 1 ELSE 0 END) as refunded,
                        SUM(CASE WHEN status = 'verified' THEN amount ELSE 0 END) as total_revenue
                     FROM payments");
$stats = $stmt->fetch();

// Get monthly revenue
$stmt = $pdo->query("SELECT DATE_FORMAT(verified_at, '%M %Y') as month, SUM(amount) as total 
                     FROM payments 
                     WHERE status = 'verified' AND verified_at IS NOT NULL
                     GROUP BY YEAR(verified_at), MONTH(verified_at)
                     ORDER BY verified_at DESC 
                     LIMIT 6");
$monthly_revenue = $stmt->fetchAll();

// Get all payments
$stmt = $pdo->prepare("SELECT p.*, r.customer_name, r.customer_phone, r.customer_email, r.rental_date, r.return_date,
                       c.car_name, c.brand, c.model,
                       u1.username as customer_name_user, u2.username as verified_by_name
                       FROM payments p
                       JOIN rentals r ON p.rental_id = r.id
                       JOIN car c ON r.car_id = c.id
                       LEFT JOIN users u1 ON r.user_id = u1.id
                       LEFT JOIN users u2 ON p.verified_by = u2.id
                       ORDER BY p.created_at DESC
                       LIMIT 50");
$stmt->execute();
$all_payments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Overview - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-card h3 { font-size: 1.8rem; color: #1e3c72; }
        .stat-card .icon { font-size: 2rem; color: #1e3c72; }
        .section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .section h2 {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f2f5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; }
        .status-verified { background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; }
        .status-failed { background: #f8d7da; color: #721c24; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; }
        .status-refunded { background: #d1ecf1; color: #0c5460; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; }
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr { display: block; }
            tr { margin-bottom: 15px; border: 1px solid #ddd; border-radius: 10px; }
            td { display: flex; justify-content: space-between; padding: 10px; }
            td::before { content: attr(data-label); font-weight: 600; width: 40%; }
        }
    </style>
</head>
<body>
<div class="container">
    <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    
    <div class="header">
        <h1><i class="fas fa-chart-line"></i> Payment Overview</h1>
        <p>Track all payments, revenue, and payment statuses</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div><h3>₱<?= number_format($stats['total_revenue'] ?? 0, 2) ?></h3><p>Total Revenue</p></div>
            <div class="icon"><i class="fas fa-dollar-sign"></i></div>
        </div>
        <div class="stat-card">
            <div><h3><?= $stats['pending'] ?? 0 ?></h3><p>Pending</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
        <div class="stat-card">
            <div><h3><?= $stats['verified'] ?? 0 ?></h3><p>Verified</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="stat-card">
            <div><h3><?= $stats['failed'] ?? 0 ?></h3><p>Failed</p></div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>
    
    <div class="section">
        <h2><i class="fas fa-chart-bar"></i> Monthly Revenue</h2>
        <table>
            <thead><tr><th>Month</th><th>Revenue</th></tr></thead>
            <tbody>
                <?php foreach ($monthly_revenue as $month): ?>
                    <tr><td><?= $month['month'] ?></td><td>₱<?= number_format($month['total'], 2) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="section">
        <h2><i class="fas fa-list"></i> All Payments</h2>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Customer</th><th>Car</th><th>Amount</th><th>Method</th><th>Status</th><th>Verified By</th><th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_payments as $payment): ?>
                        <tr>
                            <td data-label="ID">#<?= $payment['id'] ?></td>
                            <td data-label="Customer"><?= htmlspecialchars($payment['customer_name'] ?? $payment['customer_name_user'] ?? 'N/A') ?></td>
                            <td data-label="Car"><?= htmlspecialchars($payment['car_name']) ?></td>
                            <td data-label="Amount">₱<?= number_format($payment['amount'], 2) ?></td>
                            <td data-label="Method"><?= $payment['payment_method'] ?></td>
                            <td data-label="Status"><span class="status-<?= $payment['status'] ?>"><?= ucfirst($payment['status']) ?></span></td>
                            <td data-label="Verified By"><?= htmlspecialchars($payment['verified_by_name'] ?? '-') ?></td>
                            <td data-label="Date"><?= date('M d, Y', strtotime($payment['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>