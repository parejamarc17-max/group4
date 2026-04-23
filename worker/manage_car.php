<?php
require_once '../config/database.php';
session_start();

if ($_SESSION['role'] !== 'worker') die("Access denied");

$cars = $pdo->query("SELECT * FROM car ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cars (Worker View)</title>
</head>
<body>

<h1>🚗 Cars List</h1>

<table border="1">
<tr>
    <th>Name</th>
    <th>Brand</th>
    <th>Price/Day</th>
    <th>Status</th>
</tr>

<?php foreach($cars as $c): ?>
<tr>
    <td><?= $c['car_name'] ?></td>
    <td><?= $c['brand'] ?></td>
    <td><?= $c['price_per_day'] ?></td>
    <td><?= $c['status'] ?></td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>