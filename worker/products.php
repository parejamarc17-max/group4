<?php
require_once '../config/database.php';
session_start();

if ($_SESSION['role'] !== 'worker') die("Access denied");

$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>

<h1>📦 Products</h1>

<table border="1">
<tr>
    <th>Name</th>
    <th>Category</th>
    <th>Price</th>
    <th>Stock</th>
</tr>

<?php foreach($products as $p): ?>
<tr>
    <td><?= $p['name'] ?></td>
    <td><?= $p['category'] ?></td>
    <td><?= $p['price'] ?></td>
    <td><?= $p['stock'] ?></td>
</tr>
<?php endforeach; ?>

</table>