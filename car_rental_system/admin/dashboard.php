<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireAdmin();

/* =========================
   RESET REVENUE ACTION
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_revenue'])) {

    // ⚠️ WARNING: This resets revenue by deleting completed rentals
    $stmt = $pdo->prepare("DELETE FROM rentals WHERE status = 'completed'");
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}

/* =========================
   DASHBOARD QUERIES
========================= */
$stmt = $pdo->query("SELECT COUNT(*) as count FROM cars");
$total_cars = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM rentals WHERE status = 'active'");
$active_rentals = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT SUM(total_cost) as total FROM rentals WHERE status = 'completed'");
$revenue = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch()['count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/navigation.css">
</head>
<body>

<div class="dashboard">

    <?php include 'sidebar.php'; ?>

    <div class="main">
        <h1>Dashboard Overview</h1>

        <div class="cards">

            <div class="card">
                Total Cars
                <h2><?= $total_cars ?></h2>
            </div>

            <div class="card">
                Active Rentals
                <h2><?= $active_rentals ?></h2>
            </div>

            <!-- ===================== -->
            <!-- REVENUE CARD -->
            <!-- ===================== -->
            <div class="card">
                Revenue
                <h2>₱<?= number_format($revenue, 2) ?></h2>

                <form method="POST"
                      onsubmit="return confirm('Are you sure you want to reset revenue? This will remove completed rentals!');">

                    <button type="submit" name="reset_revenue"
                            style="margin-top:10px; padding:6px 10px; background:red; color:white; border:none; cursor:pointer; border-radius:5px;">
                        Reset
                    </button>

                </form>
            </div>

            <div class="card">
                Users
                <h2><?= $total_users ?></h2>
            </div>

        </div>
    </div>

</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>