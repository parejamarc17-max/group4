<?php
require_once '../config/database.php';
session_start();

if ($_SESSION['role'] !== 'worker') die("Access denied");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoice = 'INV-' . time();

    $stmt = $pdo->prepare("INSERT INTO sales (invoice_no, customer_name, grand_total, payment_method, user_id)
                           VALUES (?, ?, ?, ?, ?)");

    $stmt->execute([
        $invoice,
        $_POST['customer_name'],
        $_POST['grand_total'],
        $_POST['payment_method'],
        $_SESSION['user_id']
    ]);

    header("Location: sale.php");
    exit();
}

$sales = $pdo->query("SELECT * FROM sales ORDER BY id DESC")->fetchAll();
?>

<h1>💰 Sales</h1>

<header>
    <div class="custom-header">
        <div class="header-left">
            <div class="hamburger-btn" onclick="toggleMenuAdmin()" title="Menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <h2> Sales Transactions</h2>
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
    <img src="../assets/images/logo.png" class="profile-img" style="width:60px;height:60px;border-radius:50%;margin:10px auto;display:block;" alt="Admin">
    <h2> DRIVE ADMIN</h2>
    <a href="worker_dashboard.php" class="btn-nav"> Dashboard</a>
    <a href="manage_car.php" class="btn-nav"> Manage Cars</a>
    <a href="rentals.php" class="btn-nav"> Rentals</a>
    <a href="products.php" class="btn-nav"> Products</a>
    <a href="sales.php" class="btn-nav"> Sales</a>
    <a href="customer_list.php" class="btn-nav"> Customer List</a>
    <a href="../p_login/logout.php" class="btn-nav"> Logout</a>
</div>

<div class="overlay" id="adminOverlay" onclick="closeMenuAdmin()"></div>

<div class="dashboard">
    <div class="main">
        <h1> Sales Transactions</h1>
        
        <div class="panel">
            <h3>Recent Sales</h3>
            <table>
                <thead><tr><th>Invoice</th><th>Customer</th><th>Total</th><th>Payment</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach($sales as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['invoice_no']) ?></td>
                        <td><?= htmlspecialchars($s['customer_name']) ?></td>
                        <td>₱<?= number_format($s['grand_total'], 2) ?></td>
                        <td><?= $s['payment_method'] ?></td>
                        <td><?= $s['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
</script>

</body>
</html>
<form method="POST">
    <input name="customer_name" placeholder="Customer" required>
    <input name="grand_total" placeholder="Total" required>
    <select name="payment_method">
        <option>Cash</option>
        <option>Card</option>
    </select>
    <button type="submit">Add Sale</button>
</form>

<table border="1">
<?php foreach($sales as $s): ?>
<tr>
    <td><?= $s['invoice_no'] ?></td>
    <td><?= $s['customer_name'] ?></td>
    <td><?= $s['grand_total'] ?></td>
</tr>
<?php endforeach; ?>
</table>
</table>