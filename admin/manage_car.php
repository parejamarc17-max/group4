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

// ADD CAR


// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $carId = (int) $_POST['delete'];

    // get image before deleting
    $stmt = $pdo->prepare("SELECT image FROM car WHERE id = ?");
    $stmt->execute([$carId]);
    $carToDelete = $stmt->fetch();

    $stmt = $pdo->prepare("DELETE FROM car WHERE id = ?");
    $stmt->execute([$carId]);

    if ($carToDelete && !empty($carToDelete['image'])) {
        $imagePath = $uploadDir . $carToDelete['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    header("Location: manage_car.php");
    exit();
}

$cars = $pdo->query("SELECT * FROM car ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars - CarRent System</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-group.full {
            grid-column: span 2;
        }
        
        .form-group {
            margin-bottom: 5px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        
        .image-upload-box {
            border: 2px dashed #ccc;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .image-preview-box {
            margin-top: 15px;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .image-preview-box img {
            max-width: 100%;
            max-height: 150px;
            border-radius: 8px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 20px;
        }
        
        .table-wrap {
            overflow-x: auto;
            margin-top: 30px;
            color: #000000;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #242421;
            font-weight: 600;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-badge.available { background: #d4edda; color: #155724; }
        .status-badge.rented { background: #fff3cd; color: #856404; }
        .status-badge.maintenance { background: #f8d7da; color: #721c24; }
        
        .btn-edit, .btn-delete {
            padding: 5px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 3px;
        }
        
        .btn-edit {
            background: #4CAF50;
            color: white;
        }
        
        .btn-delete {
            background: #f44336;
            color: white;
        }
        
        .car-specs {
            font-size: 0.75rem;
            color: #666;
            margin-top: 5px;
        }
        
        .spec-badge {
            display: inline-block;
            padding: 2px 8px;
            background: #e0e0e0;
            border-radius: 12px;
            font-size: 0.7rem;
            margin-right: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
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
            <h2> Automobile</h2>
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
    <h2> DRIVE ADMIN</h2>
    <a href="dashboard.php" class="btn-nav"> Dashboard</a>
    <a href="manage_car.php" class="btn-nav"> Manage Cars</a>
    <a href="rentals.php" class="btn-nav"> Rentals</a>
    <a href="products.php" class="btn-nav"> Products</a>
    <a href="sales.php" class="btn-nav"> Sales</a>
    <a href="worker_list.php" class="btn-nav"> Worker List</a>
    <a href="pending_workers.php" class="btn-nav"> Pending Workers</a>
    <a href="../p_login/logout.php" class="btn-nav"> Logout</a>
</div>

<div class="overlay" id="adminOverlay" onclick="closeMenuAdmin()"></div>

<div class="dashboard">
    <div class="main">
       

        

                    
        <!-- Cars List Table -->
        <div class="table-wrap">
            <h3> Car list</h3>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Car Details</th>
                        <th>Specs</th>
                        <th>Price/Day</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cars)): ?>
                        <?php foreach ($cars as $car): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($car['image'])): ?>
                                        <img src="../uploads/cars/<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['car_name']) ?>" style="width: 80px; height: 60px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <span>No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($car['car_name']) ?></strong><br>
                                    <small><?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?> (<?= htmlspecialchars($car['year']) ?>)</small><br>
                                    <small> <?= htmlspecialchars($car['plate_num']) ?></small><br>
                                    <small> <?= htmlspecialchars($car['location'] ?? 'N/A') ?></small>
                                </td>
                                <td>
                                    <div class="car-specs">
                                        <?php if (!empty($car['transmission'])): ?>
                                            <span class="spec-badge"> <?= htmlspecialchars($car['transmission']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($car['fuel_type'])): ?>
                                            <span class="spec-badge"> <?= htmlspecialchars($car['fuel_type']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($car['seating_capacity'])): ?>
                                            <span class="spec-badge"> <?= htmlspecialchars($car['seating_capacity']) ?> seats</span>
                                        <?php endif; ?>
                                        <?php if (!empty($car['category'])): ?>
                                            <span class="spec-badge"> <?= htmlspecialchars($car['category']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($car['color'])): ?>
                                            <span class="spec-badge"> <?= htmlspecialchars($car['color']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><strong>$<?= number_format((float) $car['price_per_day'], 2) ?></strong>/day</td>
                                <td>
                                    <span class="status-badge <?= htmlspecialchars($car['status']) ?>">
                                        <?= htmlspecialchars($car['status']) ?>
                                    </span>
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
                            <td colspan="6" style="text-align:center; padding:20px; color:#777;">No cars found. Add your first car above!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Image preview
    const imageInput = document.getElementById('image');
    const previewImage = document.getElementById('previewImage');
    const previewText = document.getElementById('previewText');

    imageInput.addEventListener('change', function () {
        const file = this.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImage.src = e.target.result;
                previewImage.style.display = 'block';
                previewText.style.display = 'none';
            };
            reader.readAsDataURL(file);
        } else {
            previewImage.src = '';
            previewImage.style.display = 'none';
            previewText.style.display = 'block';
        }
    });

    function editCar(carId) {
        window.location.href = 'edit_car.php?id=' + carId;
    }

    function toggleMenuAdmin() {
        const menu = document.getElementById("adminMenu");
        const overlay = document.getElementById("adminOverlay");
        const hamburger = document.querySelector('.hamburger-btn');

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