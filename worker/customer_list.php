<?php
require_once '../config/database.php';
session_start();

if ($_SESSION['role'] !== 'worker') die("Access denied");

$customers = $pdo->query("SELECT * FROM users WHERE role='customer' ORDER BY id DESC")->fetchAll();
?>

<h1>👥 Customer List</h1>

<ul>
<?php foreach($customers as $c): ?>
    <li><?= $c['full_name'] ?> - <?= $c['email'] ?></li>
<?php endforeach; ?>
</ul>