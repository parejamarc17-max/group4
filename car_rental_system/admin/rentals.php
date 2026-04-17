<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireAdmin();

if (isset($_GET['return'])) {
    $stmt = $pdo->prepare("UPDATE rentals SET status = 'completed' WHERE id = ?");
    $stmt->execute([$_GET['return']]);

    $rental = $pdo->prepare("SELECT car_id FROM rentals WHERE id = ?");
    $rental->execute([$_GET['return']]);
    $car_id = $rental->fetch()['car_id'];

    $pdo->prepare("UPDATE cars SET status = 'available' WHERE id = ?")->execute([$car_id]);

    header("Location: rentals.php");
    exit();
}

// Auto-complete overdue rentals
$current_date = date('Y-m-d');
$overdue = $pdo->prepare("SELECT id, car_id FROM rentals WHERE status = 'active' AND return_date < ?");
$overdue->execute([$current_date]);
$overdue_rentals = $overdue->fetchAll();

foreach ($overdue_rentals as $rental) {
    $pdo->prepare("UPDATE rentals SET status = 'completed' WHERE id = ?")->execute([$rental['id']]);
    $pdo->prepare("UPDATE cars SET status = 'available' WHERE id = ?")->execute([$rental['car_id']]);
}

$rentals = $pdo->query("
    SELECT r.*, c.car_name 
    FROM rentals r 
    LEFT JOIN cars c ON r.car_id = c.id 
    WHERE r.status = 'active'
    ORDER BY r.id DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rentals</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/navigation.css">
</head>
<body>

<div class="dashboard">

    <?php include 'sidebar.php'; ?>

    <div class="main">
        <h1> Rental Management</h1>

        <div class="panel">
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Car</th>
                        <th>Rental Date</th>
                        <th>Return Date</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($rentals as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['customer_name']) ?></td>
                        <td><?= htmlspecialchars($r['car_name']) ?></td>
                        <td><?= $r['rental_date'] ?></td>
                        <td><?= $r['return_date'] ?></td>
                        <td>$<?= number_format($r['total_cost'],2) ?></td>
                        <td>
                            <?php if($r['status'] == 'active'): ?>
                                <a href="?return=<?= $r['id'] ?>" class="btn-nav">Return</a>
                            <?php else: ?>
                                <span class="status-completed">Completed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>