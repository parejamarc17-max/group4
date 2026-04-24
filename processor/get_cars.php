<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$category = $_GET['category'] ?? 'all';

if ($category === 'all') {
    $stmt = $pdo->query("SELECT * FROM car WHERE status = 'available' ORDER BY id DESC LIMIT 8");
} else {
    $stmt = $pdo->prepare("SELECT * FROM car WHERE status = 'available' AND category = ? ORDER BY id DESC LIMIT 8");
    $stmt->execute([$category]);
}

$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($cars);
?>