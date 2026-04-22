<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireWorker();

$sales = $pdo->query("SELECT * FROM sales ORDER BY id DESC LIMIT 10")->fetchAll();
$products = $pdo->query("SELECT * FROM products WHERE stock > 0")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sale'])) {
    $invoice_no = 'INV-' . time();
    $stmt = $pdo->prepare("INSERT INTO sales (invoice_no, customer_name, grand_total, payment_method, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$invoice_no, $_POST['customer_name'], $_POST['grand_total'], $_POST['payment_method'], $_SESSION['user_id']]);
    $success = "Sale completed! Invoice: $invoice_no";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard">
<div class="sidebar" id="sideMenu">
    <img src="assets/images/download (4).jpg" class="profile-img" style="width:60px;height:60px;border-radius:50%;margin:10px auto;display:block;">
    <h2>🚗 DRIVE WORKER</h2>
    <a href="worker.php" class="btn-nav" onclick="closeMenus()">← Home</a>
    <a href="worker_dashboard.php" class="btn-nav" onclick="closeMenus()">📊 Dashboard</a>
    <a href="worker_manage_car.php" class="btn-nav" onclick="closeMenus()">🚘 Manage Cars</a>
    <a href="worker_rentals.php" class="btn-nav" onclick="closeMenus()">📅 Rentals</a>
    <a href="worker_products.php" class="btn-nav" onclick="closeMenus()">📦 Products</a>
    <a href="worker_sales.php" class="btn-nav" onclick="closeMenus()">💰 Sales</a>
    <a href="p_login/logout.php" class="btn-nav" onclick="closeMenus()">🚪 Logout</a>
</div>
    <div class="main">
        <h1>💰 Sales Transactions</h1>
        
        <div class="panel">
            <h3>Recent Sales</h3>
            <table>
                <thead><tr><th>Invoice</th><th>Customer</th><th>Total</th><th>Payment</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach($sales as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['invoice_no']) ?></td>
                        <td><?= htmlspecialchars($s['customer_name']) ?></td>
                        <td>$<?= number_format($s['grand_total'], 2) ?></td>
                        <td><?= $s['payment_method'] ?></td>
                        <td><?= $s['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
