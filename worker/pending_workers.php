<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireAdmin();

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $application_id = $_POST['application_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Get worker application details
        $app_stmt = $pdo->prepare("SELECT * FROM worker_applications WHERE id = ?");
        $app_stmt->execute([$application_id]);
        $application = $app_stmt->fetch();

        if ($application) {
            // Create a user account for the approved worker
            $username = strtolower(str_replace(' ', '_', $application['full_name'])) . '_' . uniqid();
            $temp_password = bin2hex(random_bytes(4)); // Temporary password
            $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

            try {
                $user_stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, role) VALUES (?, ?, ?, ?, ?, 'worker')");
                $user_stmt->execute([$username, $hashed_password, $application['full_name'], $application['full_name'] . '@worker.local', $application['phone']]);
                
                $user_id = $pdo->lastInsertId();

                // Update worker_applications with user_id and approved status
                $update_stmt = $pdo->prepare("UPDATE worker_applications SET status = 'approved', user_id = ? WHERE id = ?");
                $update_stmt->execute([$user_id, $application_id]);

                header('Location: worker_list.php?approved=1');
                exit();
            } catch (PDOException $e) {
                $error = "Error creating worker account: " . $e->getMessage();
            }
        }
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE worker_applications SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$application_id]);
        
        header('Location: pending_workers.php?rejected=1');
        exit();
    }
}

// Get pending applications
$stmt = $pdo->query("SELECT * FROM worker_applications WHERE status = 'pending' ORDER BY created_at DESC");
$applications = $stmt->fetchAll();

// Check for success/error messages
$rejected_msg = isset($_GET['rejected']) ? "Application rejected." : null;
$error_msg = isset($_GET['error']) ? $_GET['error'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pending Worker Applications - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<header>
    <div class="custom-header">
        <div class="header-left">
            <div class="hamburger-btn" onclick="toggleMenuAdmin()" title="Menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <h2>🚗 Pending Workers</h2>
        </div>
        <div class="header-right">
            <div class="user-section">
                <span class="username">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                </span>
                <a href="../p_login/logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </div>
    </div>
</header>

<div class="side-menu" id="adminMenu">
    <img src="../assets/images/download (4).jpg" class="profile-img" style="width:60px;height:60px;border-radius:50%;margin:10px auto;display:block;" alt="Admin">
    <h2>🚗 DRIVE ADMIN</h2>
    <a href="dashboard.php" class="btn-nav">📊 Dashboard</a>
    <a href="manage_car.php" class="btn-nav">🚘 Manage Cars</a>
    <a href="rentals.php" class="btn-nav">📅 Rentals</a>
    <a href="products.php" class="btn-nav">📦 Products</a>
    <a href="sales.php" class="btn-nav">💰 Sales</a>
    <a href="worker_list.php" class="btn-nav">👷 Worker List</a>
    <a href="pending_workers.php" class="btn-nav">⏳ Pending Workers</a>
    <a href="../p_login/logout.php" class="btn-nav">🚪 Logout</a>
</div>

<div class="overlay" id="adminOverlay" onclick="closeMenuAdmin()"></div>

<div class="dashboard">

    <div class="main">
        <h1>Pending Worker Applications</h1>

        <?php if(isset($rejected_msg)): ?>
            <p style="color:#ff9800; background:#ffe0cc; padding:10px; border-radius:5px; margin-bottom:15px;">
                ℹ️ <?= htmlspecialchars($rejected_msg) ?>
            </p>
        <?php endif; ?>

        <?php if(isset($error_msg)): ?>
            <p style="color:#d32f2f; background:#ffebee; padding:10px; border-radius:5px; margin-bottom:15px;">
                ❌ <?= htmlspecialchars($error_msg) ?>
            </p>
        <?php endif; ?>

        <?php if (empty($applications)): ?>
            <p class="no-applications">No pending applications at the moment.</p>
        <?php else: ?>
            <div class="applications-list">
                <?php foreach ($applications as $app): ?>
                    <div class="application-card">
                        <h3><?php echo htmlspecialchars($app['full_name']); ?></h3>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($app['phone']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($app['address']); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($app['status']); ?></p>
                        <p><strong>Applied:</strong> <?php echo $app['created_at']; ?></p>

                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                            <button type="submit" name="action" value="approve" class="btn-approve">Approve</button>
                            <button type="submit" name="action" value="reject" class="btn-reject">Reject</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.applications-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.application-card {
    border: 1px solid #e0e0e0;
    padding: 25px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f9f9f9 0%, #ffffff 100%);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
    overflow: hidden;
}

.application-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ff6b00, #ff8533);
}

.application-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
}

.application-card h3 {
    margin-top: 0;
    color: #333;
    font-size: 1.4em;
    margin-bottom: 15px;
}

.application-card p {
    margin: 8px 0;
    color: #666;
    font-size: 0.95em;
}

.application-card strong {
    color: #333;
}

.btn-approve, .btn-reject {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
    margin-right: 10px;
    margin-top: 15px;
}

.btn-approve {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
}

.btn-approve:hover {
    background: linear-gradient(135deg, #45a049, #3d8b40);
    transform: scale(1.05);
}

.btn-reject {
    background: linear-gradient(135deg, #f44336, #d32f2f);
    color: white;
}

.btn-reject:hover {
    background: linear-gradient(135deg, #d32f2f, #b71c1c);
    transform: scale(1.05);
}

.no-applications {
    text-align: center;
    color: #666;
    font-size: 1.2em;
    margin-top: 50px;
}
</style>

<script>
function toggleMenuAdmin() {
    const menu = document.getElementById("adminMenu");
    const overlay = document.getElementById("adminOverlay");
    const hamburger = document.querySelector('.hamburger-btn');

    if (!menu) return;

    if (menu.classList.contains("active")) {
        menu.classList.remove("active");
        if (overlay) overlay.classList.remove("active");
        if (hamburger) hamburger.classList.remove('active');
    } else {
        menu.classList.add("active");
        if (overlay) overlay.classList.add("active");
        if (hamburger) hamburger.classList.add('active');
    }
}

function closeMenuAdmin() {
    const menu = document.getElementById("adminMenu");
    const overlay = document.getElementById("adminOverlay");
    const hamburger = document.querySelector('.hamburger-btn');

    if (menu) menu.classList.remove("active");
    if (overlay) overlay.classList.remove("active");
    if (hamburger) hamburger.classList.remove('active');
}
</script>

</body>
</html>