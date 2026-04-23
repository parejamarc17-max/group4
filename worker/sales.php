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