<?php
require_once '../config/database.php';
session_start();

if ($_SESSION['role'] !== 'worker') die("Access denied");

$users = $pdo->query("SELECT * FROM users WHERE role='customer'")->fetchAll();
?>

<h1>👤 Customers</h1>

<table border="1">
<tr>
    <th>Name</th>
    <th>Email</th>
</tr>

<?php foreach($users as $u): ?>
<tr>
    <td><?= $u['full_name'] ?></td>
    <td><?= $u['email'] ?></td>
</tr>
<?php endforeach; ?>
</table>