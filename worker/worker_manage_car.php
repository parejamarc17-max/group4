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
    $description = trim($_POST['description'] ?? '');

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
            $stmt = $pdo->prepare("INSERT INTO car 
                (image, car_name, brand, model, year, plate_num, price_per_day, description, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available')");

            if ($stmt->execute([
                $imageName,
                $carName,
                $brand,
                $model,
                $year !== '' ? $year : null,
                $plateNumber,
                $pricePerDay,
                $description
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
    <title>Manage Cars</title>

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
    <a href="manage_car.php" class="btn-nav">🚘 Manage Cars</a>
    <a href="rentals.php" class="btn-nav">📅 Rentals</a>
    <a href="products.php" class="btn-nav">📦 Products</a>
    <a href="sales.php" class="btn-nav">💰 Sales</a>
    <a href="users.php" class="btn-nav">👥 Users</a>
    <a href="pending_workers.php" class="btn-nav">👷 Pending Workers</a>
    <a href="../p_login/logout.php" class="btn-nav">🚪 Logout</a>
</div>

<div class="overlay" id="adminOverlay" onclick="closeMenuAdmin()"></div>

    <div class="dashboard">
    <div class="main">
        <h1>🚘 Manage Fleet</h1>

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
                    <div class="form-group full">
                        <label for="image">Car Image</label>

                        <div class="image-upload-box">
                            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" required>

                            <div class="image-preview-box" id="imagePreviewBox">
                                <img id="previewImage" src="" alt="Car preview" style="display:none;">
                                <span id="previewText">Image preview will appear here</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="car_name">Car Name</label>
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
                        <label for="plate_number">Plate Number</label>
                        <input type="text" id="plate_number" name="plate_number" required value="<?= htmlspecialchars($_POST['plate_number'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="price_per_day">Price/Day ($)</label>
                        <input type="number" step="0.01" id="price_per_day" name="price_per_day" required value="<?= htmlspecialchars($_POST['price_per_day'] ?? '') ?>">
                    </div>

                    <div class="form-group full">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <button type="submit" name="add" class="btn-submit">Add Car</button>
            </form>
        </div>

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