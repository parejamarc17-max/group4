<?php
require_once '../config/database.php';
session_start();

if ($_SESSION['role'] !== 'worker') die("Access denied");

if (isset($_GET['return'])) {
    $id = $_GET['return'];

    $r = $pdo->prepare("SELECT car_id FROM rentals WHERE id=?");
    $r->execute([$id]);
    $car = $r->fetch();

    $pdo->prepare("UPDATE rentals SET status='completed' WHERE id=?")->execute([$id]);
    $pdo->prepare("UPDATE car SET status='available' WHERE id=?")->execute([$car['car_id']]);

    header("Location: rentals.php");
    exit();
}

$rentals = $pdo->query("SELECT * FROM rentals ORDER BY id DESC")->fetchAll();
?>

<h1>📅 Rentals (Worker)</h1>

<table border="1">
<tr>
    <th>Customer</th>
    <th>Car ID</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php foreach($rentals as $r): ?>
<tr>
    <td><?= $r['customer_name'] ?></td>
    <td><?= $r['car_id'] ?></td>
    <td><?= $r['status'] ?></td>
    <td>
        <?php if($r['status'] == 'active'): ?>
            <a href="?return=<?= $r['id'] ?>">Return</a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>

</table>