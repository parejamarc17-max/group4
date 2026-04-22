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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $carName = trim($_POST['car_name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $plateNumber = trim($_POST['plate_number'] ?? '');
    $pricePerDay = trim($_POST['price_per_day'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Fields that exist in your database
    $transmission = trim($_POST['transmission'] ?? '');
    $fuelType = trim($_POST['fuel_type'] ?? '');
    $seatingCapacity = trim($_POST['seating_capacity'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $insuranceInfo = trim($_POST['insurance_info'] ?? '');
    $location = trim($_POST['location'] ?? '');

    if ($carName === '' || $plateNumber === '' || $pricePerDay === '') {
        $error = "Please fill in all required fields.";
    } else {
        $imageName = null;

        // IMAGE UPLOAD
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $fileTmp = $_FILES['image']['tmp_name'];
            $fileSize = $_FILES['image']['size'];
            $fileType = mime_content_type($fileTmp);

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            if (!array_key_exists($fileType, $allowedTypes)) {
                $error = "Invalid file type. Only JPG, PNG, and WEBP are allowed.";
            } elseif ($fileSize > 2 * 1024 * 1024) {
                $error = "File too large. Max 2MB allowed.";
            } else {
                $imageName = uniqid("car_", true) . "." . $allowedTypes[$fileType];

                if (!move_uploaded_file($fileTmp, $uploadDir . $imageName)) {
                    $error = "Failed to upload image.";
                }
            }
        } else {
            $error = "Please upload a car image.";
        }

        if ($error === '') {
            // INSERT matching your 18-column table (excluding id and created_at which auto-generate)
            $stmt = $pdo->prepare("INSERT INTO car 
                (image, car_name, brand, model, year, plate_num, price_per_day, category, description, 
                 transmission, fuel_type, seating_capacity, color, insurance_info, location, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, 'available')");

            if ($stmt->execute([
                $imageName,
                $carName,
                $brand,
                $model,
                $year !== '' ? $year : null,
                $plateNumber,
                $pricePerDay,
                $category,
                $description,
                $transmission,
                $fuelType,
                $seatingCapacity !== '' ? $seatingCapacity : null,
                $color,
                $insuranceInfo,
                $location
            ])) {
                $success = "Car added successfully!";
                header("Location: manage_car.php");
                exit();
            } else {
                $error = "Failed to add car to database.";
            }
        }
    }
}

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
            background: #f5f5f5;
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
            <h2> Manage Cars</h2>
        </div>
        <div class="header-right">
            <div class="user-section">
                <span class="username">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                </span>
                <a href="../p_login/logout.php" class="logout-btn"> Logout</a>
            </div>
        </div>
    </div>
</header>

<div class="side-menu" id="adminMenu">
    <img src="../assets/images/download (4).jpg" class="profile-img" style="width:60px;height:60px;border-radius:50%;margin:10px auto;display:block;" alt="Admin">
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
        <h1> Manage Fleet</h1>

        <div class="form-container">
            <h3>Add New Car</h3>

            <?php if ($success): ?>
                <div class="alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-grid">
                    <!-- Basic Information Section -->
                    <div class="form-group full">
                        <h4 style="margin: 10px 0; color: #667eea;"> Basic Information</h4>
                    </div>
                    
                    <div class="form-group full">
                        <label for="image">Car Image *</label>
                        <div class="image-upload-box">
                            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" required>
                            <div class="image-preview-box" id="imagePreviewBox">
                                <img id="previewImage" src="" alt="Car preview" style="display:none;">
                                <span id="previewText">Image preview will appear here</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="car_name">Car Name *</label>
                        <input type="text" id="car_name" name="car_name" required value="<?= htmlspecialchars($_POST['car_name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="brand">Brand</label>
                        <input type="text" id="brand" name="brand" value="<?= htmlspecialchars($_POST['brand'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="model">Model</label>
                        <input type="text" id="model" name="model" value="<?= htmlspecialchars($_POST['model'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" id="year" name="year" value="<?= htmlspecialchars($_POST['year'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="plate_number">Plate Number *</label>
                        <input type="text" id="plate_number" name="plate_number" required value="<?= htmlspecialchars($_POST['plate_number'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="price_per_day">Price/Day ($) *</label>
                        <input type="number" step="0.01" id="price_per_day" name="price_per_day" required value="<?= htmlspecialchars($_POST['price_per_day'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="">Select Category</option>
                            <option value="Economy" <?= (isset($_POST['category']) && $_POST['category'] == 'Economy') ? 'selected' : '' ?>>Economy</option>
                            <option value="Compact" <?= (isset($_POST['category']) && $_POST['category'] == 'Compact') ? 'selected' : '' ?>>Compact</option>
                            <option value="SUV" <?= (isset($_POST['category']) && $_POST['category'] == 'SUV') ? 'selected' : '' ?>>SUV</option>
                            <option value="Luxury" <?= (isset($_POST['category']) && $_POST['category'] == 'Luxury') ? 'selected' : '' ?>>Luxury</option>
                            <option value="Sports" <?= (isset($_POST['category']) && $_POST['category'] == 'Sports') ? 'selected' : '' ?>>Sports</option>
                            <option value="Van" <?= (isset($_POST['category']) && $_POST['category'] == 'Van') ? 'selected' : '' ?>>Van</option>
                        </select>
                    </div>

                    <div class="form-group full">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Car Specifications Section -->
                    <div class="form-group full">
                        <h4 style="margin: 20px 0 10px 0; color: #667eea;"> Car Specifications</h4>
                    </div>

                    <div class="form-group">
                        <label for="transmission">Transmission Type</label>
                        <select id="transmission" name="transmission">
                            <option value="">Select Transmission</option>
                            <option value="Automatic" <?= (isset($_POST['transmission']) && $_POST['transmission'] == 'Automatic') ? 'selected' : '' ?>>Automatic</option>
                            <option value="Manual" <?= (isset($_POST['transmission']) && $_POST['transmission'] == 'Manual') ? 'selected' : '' ?>>Manual</option>
                            <option value="CVT" <?= (isset($_POST['transmission']) && $_POST['transmission'] == 'CVT') ? 'selected' : '' ?>>CVT</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fuel_type">Fuel Type</label>
                        <select id="fuel_type" name="fuel_type">
                            <option value="">Select Fuel Type</option>
                            <option value="Gasoline" <?= (isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Gasoline') ? 'selected' : '' ?>>Gasoline</option>
                            <option value="Diesel" <?= (isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Diesel') ? 'selected' : '' ?>>Diesel</option>
                            <option value="Hybrid" <?= (isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Hybrid') ? 'selected' : '' ?>>Hybrid</option>
                            <option value="Electric" <?= (isset($_POST['fuel_type']) && $_POST['fuel_type'] == 'Electric') ? 'selected' : '' ?>>Electric</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="seating_capacity">Seating Capacity</label>
                        <select id="seating_capacity" name="seating_capacity">
                            <option value="">Select Capacity</option>
                            <option value="2" <?= (isset($_POST['seating_capacity']) && $_POST['seating_capacity'] == '2') ? 'selected' : '' ?>>2 Seats</option>
                            <option value="4" <?= (isset($_POST['seating_capacity']) && $_POST['seating_capacity'] == '4') ? 'selected' : '' ?>>4 Seats</option>
                            <option value="5" <?= (isset($_POST['seating_capacity']) && $_POST['seating_capacity'] == '5') ? 'selected' : '' ?>>5 Seats</option>
                            <option value="7" <?= (isset($_POST['seating_capacity']) && $_POST['seating_capacity'] == '7') ? 'selected' : '' ?>>7 Seats</option>
                            <option value="8" <?= (isset($_POST['seating_capacity']) && $_POST['seating_capacity'] == '8') ? 'selected' : '' ?>>8 Seats</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color" placeholder="e.g., Red, Black, White" value="<?= htmlspecialchars($_POST['color'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="insurance_info">Insurance Info</label>
                        <select id="insurance_info" name="insurance_info">
                            <option value="">Select Insurance Type</option>
                            <option value="Full Coverage" <?= (isset($_POST['insurance_info']) && $_POST['insurance_info'] == 'Full Coverage') ? 'selected' : '' ?>>Full Coverage</option>
                            <option value="Limited" <?= (isset($_POST['insurance_info']) && $_POST['insurance_info'] == 'Limited') ? 'selected' : '' ?>>Limited</option>
                            <option value="Third Party" <?= (isset($_POST['insurance_info']) && $_POST['insurance_info'] == 'Third Party') ? 'selected' : '' ?>>Third Party</option>
                        </select>
                    </div>

                    <div class="form-group full">
                        <label for="location">Location / Branch</label>
                        <input type="text" id="location" name="location" placeholder="e.g., Main Branch, Airport Branch" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
                    </div>
                </div>

                <button type="submit" name="add" class="btn-submit">➕ Add Car</button>
            </form>
        </div>

        <!-- Cars List Table -->
        <div class="table-wrap">
            <h3> Current Fleet</h3>
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