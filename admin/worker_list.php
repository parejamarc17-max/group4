<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireAdmin();

// Handle fire worker action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fire_worker'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    
    $worker_id = $_POST['fire_worker'];
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'worker'")->execute([$worker_id]);
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check for success messages
$approved_msg = isset($_GET['approved']) ? "Worker successfully approved and added to the list!" : null;

// Get only workers (role = 'worker') and not fired
$stmt = $pdo->query("
    SELECT u.id, u.username, u.full_name, u.email, u.phone, u.created_at,
           wa.address, wa.phone as app_phone
    FROM users u
    LEFT JOIN worker_applications wa ON u.id = wa.user_id
    WHERE u.role = 'worker'
    ORDER BY u.created_at DESC
");
$workers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Worker List Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .worker-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            align-items: center;
        }

        .worker-info h4 {
            margin: 5px 0;
            color: #333;
            font-size: 1.1rem;
        }

        .worker-info p {
            margin: 3px 0;
            font-size: 0.9rem;
            color: #666;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-fire {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .btn-fire:hover {
            background: #c82333;
        }

        .no-workers {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        @media (max-width: 1024px) {
            .worker-card {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .worker-card {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
            <h2> Worker List</h2>
        </div>
        <div class="header-right">
            <div class="user-section">
                <span class="username">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                </span>
                
            </div>
        </div>
    </div>
</header>

<div class="side-menu" id="adminMenu">
    <img src="../assets/images/logo.png" class="profile-img" style="width:60px;height:60px;border-radius:50%;margin:10px auto;display:block;" alt="Admin">
    <h2>DRIVE ADMIN</h2>
    <a href="dashboard.php" class="btn-nav"> Dashboard</a>
    <a href="manage_car.php" class="btn-nav"> Manage Cars</a>
    <a href="rentals.php" class="btn-nav">Rentals</a>
    <a href="products.php" class="btn-nav">Products</a>
    <a href="sales.php" class="btn-nav"> Sales</a>
    <a href="worker_list.php" class="btn-nav"> Worker List</a>
    <a href="pending_workers.php" class="btn-nav"> Pending Workers</a>
    <a href="../p_login/logout.php" class="btn-nav"> Logout</a>
</div>

<div class="overlay" id="adminOverlay" onclick="closeMenuAdmin()"></div>

<div class="dashboard">
    <div class="main">
        <h1> Worker List Management</h1>
        
        <?php if(isset($approved_msg)): ?>
            <p style="color:green; background:#e0ffe0; padding:10px; border-radius:5px; margin-bottom:15px;">
                ✅ <?= htmlspecialchars($approved_msg) ?>
            </p>
        <?php endif; ?>

        <div class="panel">
            <h3>Active Workers (<?= count($workers) ?>)</h3>
            
            <?php if (empty($workers)): ?>
                <p class="no-workers">No active workers at the moment.</p>
            <?php else: ?>
                <?php foreach ($workers as $worker): ?>
                    <div class="worker-card">
                        <div class="worker-info">
                            <h4><?= htmlspecialchars($worker['full_name']) ?></h4>
                            <p><strong>Username:</strong> <?= htmlspecialchars($worker['username']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($worker['email'] ?? 'N/A') ?></p>
                        </div>

                        <div class="worker-info">
                            <p><strong>Phone:</strong> <?= htmlspecialchars($worker['phone'] ?? 'N/A') ?></p>
                            <p><strong>Address:</strong> <?= htmlspecialchars($worker['address'] ?? 'N/A') ?></p>
                            <p><strong>Joined:</strong> <?= date('M d, Y', strtotime($worker['created_at'])) ?></p>
                        </div>

                        <div class="action-buttons">
                            <form method="POST" style="display:inline;" onsubmit="return confirmFire('<?= htmlspecialchars($worker['full_name']) ?>')">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="fire_worker" value="<?= $worker['id'] ?>">
                                <button type="submit" class="btn-fire"> Fire Worker</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

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

function confirmFire(workerName) {
    return confirm(`⚠️ WARNING!\n\nAre you sure you want to fire ${workerName}?\n\nThis action cannot be undone!`);
}
</script>

</body>
</html>
