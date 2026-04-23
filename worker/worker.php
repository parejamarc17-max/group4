<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    die("Access denied");
}

// stats
$cars = $pdo->query("SELECT COUNT(*) FROM car")->fetchColumn();
$rentals = $pdo->query("SELECT COUNT(*) FROM rentals")->fetchColumn();
$products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$sales = $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Worker Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<h1>👷 Worker Dashboard</h1>

<div class="cards">
    <div class="card">🚗 Cars: <?= $cars ?></div>
    <div class="card">📅 Rentals: <?= $rentals ?></div>
    <div class="card">📦 Products: <?= $products ?></div>
    <div class="card">💰 Sales: <?= $sales ?></div>
</div>

<a href="../p_login/logout.php">Logout</a>

</body>
</html>