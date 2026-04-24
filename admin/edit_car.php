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

$uploadDir = __DIR__ . "/../assets/images/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$success = '';
$error = '';
$car = null;

// Get car ID from URL
$carId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($carId <= 0) {
    header("Location: manage_car.php");
    exit();
}

// Fetch car data
$stmt = $pdo->prepare("SELECT * FROM car WHERE id = ?");
$stmt->execute([$carId]);
$car = $stmt->fetch();

if (!$car) {
    header("Location: manage_car.php");
    exit();
}

// UPDATE CAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_car'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $car_name = trim($_POST['car_name']);
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $plate_num = trim($_POST['plate_num']);
    $year = (int)$_POST['year'];
    $price_per_day = (float)$_POST['price_per_day'];
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    
    // Handle image upload
    $imagePath = $car['image']; // Keep existing image by default
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $error = "Only JPG, PNG, and GIF images are allowed.";
        } elseif ($_FILES['image']['size'] > $maxSize) {
            $error = "Image size must be less than 5MB.";
        } else {
            // Generate unique filename
            $filename = uniqid('car_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $destination = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                // Delete old image if exists
                if (!empty($car['image']) && file_exists($uploadDir . $car['image'])) {
                    unlink($uploadDir . $car['image']);
                }
                $imagePath = $filename;
            } else {
                $error = "Failed to upload image.";
            }
        }
    }
    
    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("UPDATE car SET car_name = ?, brand = ?, model = ?, plate_num = ?, year = ?, price_per_day = ?, category = ?, description = ?, status = ?, image = ? WHERE id = ?");
            $stmt->execute([$car_name, $brand, $model, $plate_num, $year, $price_per_day, $category, $description, $status, $imagePath, $carId]);
            
            $success = "Car updated successfully!";
            
            // Refresh car data
            $stmt = $pdo->prepare("SELECT * FROM car WHERE id = ?");
            $stmt->execute([$carId]);
            $car = $stmt->fetch();
            
        } catch (PDOException $e) {
            $error = "Error updating car: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car - CarRent System</title>

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
            margin-bottom: 15px;
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
            box-sizing: border-box;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .image-upload-box {
            border: 2px dashed #ccc;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            grid-column: span 2;
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
            max-height: 200px;
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
        
        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 20px;
            margin-left: 10px;
            text-decoration: none;
            display: inline-block;
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
        
        .form-actions {
            text-align: center;
            grid-column: span 2;
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
            <h2>Edit Car</h2>
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
        <?php if ($success): ?>
            <div class="alert-success">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-error">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="car_name">Car Name *</label>
                    <input type="text" id="car_name" name="car_name" value="<?= htmlspecialchars($car['car_name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="brand">Brand *</label>
                    <input type="text" id="brand" name="brand" value="<?= htmlspecialchars($car['brand']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="model">Model *</label>
                    <input type="text" id="model" name="model" value="<?= htmlspecialchars($car['model']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="plate_num">Plate Number *</label>
                    <input type="text" id="plate_num" name="plate_num" value="<?= htmlspecialchars($car['plate_num']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="year">Year *</label>
                    <input type="number" id="year" name="year" value="<?= htmlspecialchars($car['year']) ?>" min="2000" max="<?= date('Y') + 1 ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="price_per_day">Price per Day (₱) *</label>
                    <input type="number" id="price_per_day" name="price_per_day" value="<?= htmlspecialchars($car['price_per_day']) ?>" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="luxury" <?= $car['category'] === 'luxury' ? 'selected' : '' ?>>Luxury</option>
                        <option value="sports" <?= $car['category'] === 'sports' ? 'selected' : '' ?>>Sports</option>
                        <option value="suv" <?= $car['category'] === 'suv' ? 'selected' : '' ?>>SUV</option>
                        <option value="economy" <?= $car['category'] === 'economy' ? 'selected' : '' ?>>Economy</option>
                        <option value="sedan" <?= $car['category'] === 'sedan' ? 'selected' : '' ?>>Sedan</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="available" <?= $car['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="rented" <?= $car['status'] === 'rented' ? 'selected' : '' ?>>Rented</option>
                        <option value="maintenance" <?= $car['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    </select>
                </div>
                
                <div class="form-group full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?= htmlspecialchars($car['description']) ?></textarea>
                </div>
                
                <div class="form-group full">
                    <label for="image">Car Image (Leave empty to keep current image)</label>
                    <div class="image-upload-box">
                        <input type="file" id="image" name="image" accept="image/*">
                        <div class="image-preview-box">
                            <?php if (!empty($car['image'])): ?>
                                <img id="previewImage" src="../assets/images/<?= htmlspecialchars($car['image']) ?>" alt="Current car image">
                            <?php else: ?>
                                <img id="previewImage" src="" alt="Car preview" style="display: none;">
                                <p id="previewText">No image uploaded</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update_car" class="btn-submit">Update Car</button>
                    <a href="manage_car.php" class="btn-cancel">Cancel</a>
                </div>
            </div>
        </form>
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
                if (previewText) previewText.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });

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
