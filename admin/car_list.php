<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireAdmin();

// CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Upload folder
$uploadDir = __DIR__ . "/../uploads/cars/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$success = '';
$error = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle's</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/manage_car.css">
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
            <h2>🚗 Manage Cars</h2>
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
    <a href="car_list.php" class="btn-nav">🚘 Automobile</a>
    <a href="rentals.php" class="btn-nav">📅 Rentals</a>
    <a href="products.php" class="btn-nav">📦 Products</a>
    <a href="sales.php" class="btn-nav">💰 Sales</a>
    <a href="users.php" class="btn-nav">👥 Workers</a>
    <a href="pending_workers.php" class="btn-nav">👷 Pending Workers</a>
    <a href="../p_login/logout.php" class="btn-nav">🚪 Logout</a>
</div>

<div class="overlay" id="adminOverlay" onclick="closeMenuAdmin()"></div>

<div class="dashboard">
    <div class="main">
        <h1>🚘 List of Automobile's</h1>

        <div class="form-container">
            <h3>Automobile</h3>

            <?php if ($success): ?>
                <div class="alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Car</th>
                        <th>Plate</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($cars)): ?>
                        <?php foreach ($cars as $car): ?>
                            <tr>
                                <td>
                                    <div class="table-car-image">
                                        <?php if (!empty($car['image'])): ?>
                                            <img src="../uploads/cars/<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['car_name']) ?>">
                                        <?php else: ?>
                                            <span>No Image</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="car-title"><?= htmlspecialchars($car['car_name']) ?></div>
                                    <div class="car-meta"><?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?></div>
                                </td>

                                <td><?= htmlspecialchars($car['plate_num']) ?></td>
                                <td>$<?= number_format((float) $car['price_per_day'], 2) ?></td>
                                <td>
                                    <span class="status-badge"><?= htmlspecialchars($car['status']) ?></span>
                                </td>

                                <td>
                                    <button type="button" class="btn-edit" onclick="editCar(<?= $car['id'] ?>)">Edit</button>
                                    <form method="POST" onsubmit="return confirm('Delete this car?')" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="delete" value="<?= (int) $car['id'] ?>">
                                        <button type="submit" class="btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:20px; color:#777;">No cars found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const imageInput = document.getElementById('image');
    const previewImage = document.getElementById('previewImage');
    const previewText = document.getElementById('previewText');
    const imagePreviewBox = document.getElementById('imagePreviewBox');

    imageInput.addEventListener('change', function () {
        const file = this.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                previewImage.src = e.target.result;
                previewImage.style.display = 'block';
                previewText.style.display = 'none';
                imagePreviewBox.classList.add('has-image');
            };

            reader.readAsDataURL(file);
        } else {
            previewImage.src = '';
            previewImage.style.display = 'none';
            previewText.style.display = 'block';
            imagePreviewBox.classList.remove('has-image');
        }
    });

    function editCar(carId) {
        // Placeholder for edit functionality
        alert('Edit car with ID: ' + carId);
        // You can redirect to an edit page or open a modal here
    }
</script>

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