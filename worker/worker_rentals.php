<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireWorker();


// =====================================
// ❌ DELETE RENTAL
// =====================================
if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM rentals WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = "Rental removed successfully!";
    header("Location: rentals.php");
    exit();
}


// =====================================
// RETURN + MESSAGE + EMAIL SYSTEM
// =====================================
if (isset($_POST['send_return'])) {

    $id = $_POST['return_id'];
    $email = $_POST['customer_email'];
    $message = $_POST['message'];

    $stmt = $pdo->prepare("SELECT * FROM rentals WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();

    if ($data) {

        $pdo->prepare("UPDATE rentals SET status = 'completed' WHERE id = ?")
            ->execute([$id]);

        $pdo->prepare("UPDATE car SET status = 'available' WHERE id = ?")=>
            ->execute([$data['car_id']]);

        $subject = "Car Return Notice";

        $fullMessage = "
Hello {$data['customer_name']},

{$message}

-------------------------
Rental Details:
Car ID: {$data['car_id']}
Rental Date: {$data['rental_date']}
Return Date: {$data['return_date']}
-------------------------

Thank you for using our service.
        ";

        @mail($email, $subject, $fullMessage);
    }

    // 🔥 mark completed for X button logic
    $_SESSION['returned_id'] = $id;

    header("Location: rentals.php");
    exit();
}


// =====================================
// OLD RETURN SYSTEM (UNCHANGED)
// =====================================
if (isset($_GET['return'])) {

    $stmt = $pdo->prepare("UPDATE rentals SET status = 'completed' WHERE id = ?");
    $stmt->execute([$_GET['return']]);

    $rental = $pdo->prepare("SELECT car_id FROM rentals WHERE id = ?");
    $rental->execute([$_GET['return']]);
    $car_id = $rental->fetch()['car_id'];

    $pdo->prepare("UPDATE car SET status = 'available' WHERE id = ?")=>
        ->execute([$car_id]);

    $_SESSION['returned_id'] = $_GET['return'];

    header("Location: rentals.php");
    exit();
}


// =====================================
// FETCH
// =====================================
$rentals = $pdo->query("
    SELECT r.*, c.car_name 
    FROM rentals r 
    LEFT JOIN cars c ON r.car_id = c.id 
    ORDER BY r.id DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Rental Management</title>

<link rel="stylesheet" href="../assets/css/style.css">

<style>
/* ===== TABLE FIX ===== */
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

th {
    background: #111827;
    color: white;
    padding: 14px;
    text-align: left;
}

td {
    padding: 14px;
    border-bottom: 1px solid #eee;
}

tr:hover {
    background: #f9fafb;
}

/* ===== ACTION CELL FIX ===== */
.action-box {
    display: flex;
    gap: 8px;
    align-items: center;
}

/* ===== X BUTTON ===== */
.btn-x {
    background: red;
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
}

/* ONLY SHOW WHEN COMPLETED */
.hidden {
    display: none;
}
</style>

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
<h1>📅 Rental Management</h1>

<div class="panel">

<table>

<thead>
<tr>
    <th>Customer</th>
    <th>Car</th>
    <th>Rental Date</th>
    <th>Return Date</th>
    <th>Total Cost</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php foreach($rentals as $rental): ?>
<tr>

<td>
    <?= htmlspecialchars($rental['customer_name']) ?><br>
    <small><?= htmlspecialchars($rental['customer_phone']) ?></small>
</td>

<td><?= htmlspecialchars($rental['car_name'] ?? 'N/A') ?></td>
<td><?= $rental['rental_date'] ?></td>
<td><?= $rental['return_date'] ?></td>
<td>$<?= number_format($rental['total_cost'], 2) ?></td>

<td>

<div class="action-box">

<?php if($rental['status'] == 'active'): ?>

    <button class="btn"
        onclick="openReturnModal(
            <?= $rental['id'] ?>,
            '<?= htmlspecialchars($rental['customer_name']) ?>',
            '<?= htmlspecialchars($rental['customer_email'] ?? '') ?>',
            '<?= htmlspecialchars($rental['car_name'] ?? '') ?>'
        )">
        Return
    </button>

<?php endif; ?>

<!-- ❌ ONLY SHOW AFTER RETURN -->
<?php if(isset($_SESSION['returned_id']) && $_SESSION['returned_id'] == $rental['id']): ?>
    <a href="?delete=<?= $rental['id'] ?>" class="btn-x"
       onclick="return confirm('Delete this record?')">
        ❌
    </a>
<?php endif; ?>

</div>

</td>

</tr>
<?php endforeach; ?>

</tbody>
</table>

</div>
</div>
</div>

<!-- =========================
MODAL
========================= -->
<div id="returnModal" class="return-modal">
<div class="return-box">

<h2>🚗 Return + Message</h2>

<form method="POST">

<input type="hidden" name="return_id" id="return_id">

<label>Email</label>
<input type="text" name="customer_email" id="r_email" readonly>

<label>Name</label>
<input type="text" id="r_customer" readonly>

<label>Car</label>
<input type="text" id="r_car" readonly>

<label>Message</label>
<textarea name="message" required></textarea>

<button type="submit" name="send_return" class="btn-confirm">
Send & Return
</button>

<button type="button" class="btn-cancel" onclick="closeReturnModal()">
Cancel
</button>

</form>

</div>
</div>

<script>
function openReturnModal(id, name, email, car) {
    document.getElementById("returnModal").style.display = "flex";

    document.getElementById("return_id").value = id;
    document.getElementById("r_email").value = email;
    document.getElementById("r_customer").value = name;
    document.getElementById("r_car").value = car;
}

function closeReturnModal() {
    document.getElementById("returnModal").style.display = "none";
}
</script>

</body>
</html>