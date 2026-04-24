<?php
require_once 'header.php';

$success_message = '';
$error_message = '';

// Handle Add Car
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_car'])) {
    $car_name = trim($_POST['car_name']);
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $plate_num = trim($_POST['plate_num']);
    $year = (int)$_POST['year'];
    $price_per_day = (float)$_POST['price_per_day'];
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $transmission = $_POST['transmission'];
    $fuel_type = $_POST['fuel_type'];
    $seating_capacity = (int)$_POST['seating_capacity'];
    $color = trim($_POST['color']);
    $insurance_info = trim($_POST['insurance_info']);
    $location = trim($_POST['location']);
    $status = $_POST['status'];
    
    // Validation
    $errors = [];
    if (empty($car_name)) $errors[] = "Car name is required";
    if (empty($brand)) $errors[] = "Brand is required";
    if (empty($plate_num)) $errors[] = "Plate number is required";
    if ($price_per_day <= 0) $errors[] = "Price per day must be greater than 0";
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . uniqid() . '.' . $file_extension;
            $image_path = $upload_dir . $filename;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $errors[] = "Failed to upload image";
                $image_path = null;
            }
        } else {
            $errors[] = "Invalid file type. Only JPG, PNG, GIF, WEBP allowed.";
        }
    } else {
        $errors[] = "Please upload a car image";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO car (car_name, brand, model, plate_num, year, price_per_day, category, description, transmission, fuel_type, seating_capacity, color, insurance_info, location, status, image) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$car_name, $brand, $model, $plate_num, $year, $price_per_day, $category, $description, $transmission, $fuel_type, $seating_capacity, $color, $insurance_info, $location, $status, $image_path]);
            $success_message = "Car added successfully!";
            
            echo "<script>setTimeout(function(){ window.location.href = 'manage_cars.php'; }, 1500);</script>";
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error_message = "Plate number already exists!";
            } else {
                $error_message = "Error adding car: " . $e->getMessage();
            }
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Handle Edit Car
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_car'])) {
    $car_id = (int)$_POST['car_id'];
    $car_name = trim($_POST['car_name']);
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $plate_num = trim($_POST['plate_num']);
    $year = (int)$_POST['year'];
    $price_per_day = (float)$_POST['price_per_day'];
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $transmission = $_POST['transmission'];
    $fuel_type = $_POST['fuel_type'];
    $seating_capacity = (int)$_POST['seating_capacity'];
    $color = trim($_POST['color']);
    $insurance_info = trim($_POST['insurance_info']);
    $location = trim($_POST['location']);
    $status = $_POST['status'];
    
    $errors = [];
    if (empty($car_name)) $errors[] = "Car name is required";
    if (empty($brand)) $errors[] = "Brand is required";
    if (empty($plate_num)) $errors[] = "Plate number is required";
    if ($price_per_day <= 0) $errors[] = "Price per day must be greater than 0";
    
    // Handle image upload for edit (optional)
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . uniqid() . '.' . $file_extension;
            $image_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                // Get old image to delete
                $stmt = $pdo->prepare("SELECT image FROM car WHERE id = ?");
                $stmt->execute([$car_id]);
                $old_car = $stmt->fetch();
                if ($old_car && $old_car['image'] && file_exists($old_car['image'])) {
                    unlink($old_car['image']);
                }
            } else {
                $errors[] = "Failed to upload image";
                $image_path = null;
            }
        } else {
            $errors[] = "Invalid file type";
        }
    }
    
    if (empty($errors)) {
        try {
            if ($image_path) {
                $stmt = $pdo->prepare("UPDATE car SET car_name = ?, brand = ?, model = ?, plate_num = ?, year = ?, price_per_day = ?, category = ?, description = ?, transmission = ?, fuel_type = ?, seating_capacity = ?, color = ?, insurance_info = ?, location = ?, status = ?, image = ? WHERE id = ?");
                $stmt->execute([$car_name, $brand, $model, $plate_num, $year, $price_per_day, $category, $description, $transmission, $fuel_type, $seating_capacity, $color, $insurance_info, $location, $status, $image_path, $car_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE car SET car_name = ?, brand = ?, model = ?, plate_num = ?, year = ?, price_per_day = ?, category = ?, description = ?, transmission = ?, fuel_type = ?, seating_capacity = ?, color = ?, insurance_info = ?, location = ?, status = ? WHERE id = ?");
                $stmt->execute([$car_name, $brand, $model, $plate_num, $year, $price_per_day, $category, $description, $transmission, $fuel_type, $seating_capacity, $color, $insurance_info, $location, $status, $car_id]);
            }
            $success_message = "Car updated successfully!";
            echo "<script>setTimeout(function(){ window.location.href = 'manage_cars.php'; }, 1500);</script>";
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error_message = "Plate number already exists!";
            } else {
                $error_message = "Error updating car: " . $e->getMessage();
            }
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Handle Delete Car
if (isset($_GET['delete_car'])) {
    $car_id = (int)$_GET['delete_car'];
    try {
        // Check if car has active rentals
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE car_id = ? AND status = 'active'");
        $stmt->execute([$car_id]);
        $active_rentals = $stmt->fetchColumn();
        
        if ($active_rentals > 0) {
            $error_message = "Cannot delete car with active rentals!";
        } else {
            // Get image path to delete
            $stmt = $pdo->prepare("SELECT image FROM car WHERE id = ?");
            $stmt->execute([$car_id]);
            $car = $stmt->fetch();
            
            // Delete the car
            $stmt = $pdo->prepare("DELETE FROM car WHERE id = ?");
            $stmt->execute([$car_id]);
            
            // Delete image file if exists
            if ($car && $car['image'] && file_exists($car['image'])) {
                unlink($car['image']);
            }
            
            $success_message = "Car deleted successfully!";
            echo "<script>setTimeout(function(){ window.location.href = 'manage_cars.php'; }, 1000);</script>";
        }
    } catch (PDOException $e) {
        $error_message = "Error deleting car: " . $e->getMessage();
    }
}

// Get all cars
$stmt = $pdo->query("SELECT * FROM car ORDER BY created_at DESC");
$cars = $stmt->fetchAll();

// Get car for editing
$edit_car = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM car WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_car = $stmt->fetch();
}
?>

<?php if ($success_message): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Car Modal -->
<div class="modal <?= $edit_car ? 'active' : '' ?>" id="carModal">
    <div class="modal-content" style="max-width: 750px;">
        <div class="modal-header">
            <h3><i class="fas <?= $edit_car ? 'fa-edit' : 'fa-plus' ?>"></i> <?= $edit_car ? 'Edit Car' : 'Add New Car' ?></h3>
            <button class="close-modal" onclick="closeCarModal()">&times;</button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <input type="hidden" name="<?= $edit_car ? 'edit_car' : 'add_car' ?>" value="1">
                <?php if ($edit_car): ?>
                    <input type="hidden" name="car_id" value="<?= $edit_car['id'] ?>">
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <!-- Left Column -->
                    <div>
                        <div class="form-group">
                            <label>Car Name <span style="color:red;">*</span></label>
                            <input type="text" name="car_name" value="<?= $edit_car ? htmlspecialchars($edit_car['car_name']) : '' ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Brand <span style="color:red;">*</span></label>
                            <input type="text" name="brand" value="<?= $edit_car ? htmlspecialchars($edit_car['brand']) : '' ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Model</label>
                            <input type="text" name="model" value="<?= $edit_car ? htmlspecialchars($edit_car['model']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Plate Number <span style="color:red;">*</span></label>
                            <input type="text" name="plate_num" value="<?= $edit_car ? htmlspecialchars($edit_car['plate_num']) : '' ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Year</label>
                            <input type="number" name="year" value="<?= $edit_car ? $edit_car['year'] : date('Y') ?>" min="1990" max="<?= date('Y') + 1 ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Price Per Day (₱) <span style="color:red;">*</span></label>
                            <input type="number" step="0.01" name="price_per_day" value="<?= $edit_car ? $edit_car['price_per_day'] : '' ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category">
                                <option value="">Select Category</option>
                                <option value="Economy" <?= $edit_car && $edit_car['category'] == 'Economy' ? 'selected' : '' ?>>Economy</option>
                                <option value="Compact" <?= $edit_car && $edit_car['category'] == 'Compact' ? 'selected' : '' ?>>Compact</option>
                                <option value="Sedan" <?= $edit_car && $edit_car['category'] == 'Sedan' ? 'selected' : '' ?>>Sedan</option>
                                <option value="SUV" <?= $edit_car && $edit_car['category'] == 'SUV' ? 'selected' : '' ?>>SUV</option>
                                <option value="Luxury" <?= $edit_car && $edit_car['category'] == 'Luxury' ? 'selected' : '' ?>>Luxury</option>
                                <option value="Sports" <?= $edit_car && $edit_car['category'] == 'Sports' ? 'selected' : '' ?>>Sports</option>
                                <option value="Van" <?= $edit_car && $edit_car['category'] == 'Van' ? 'selected' : '' ?>>Van</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div>
                        <div class="form-group">
                            <label>Transmission</label>
                            <select name="transmission">
                                <option value="">Select Transmission</option>
                                <option value="Automatic" <?= $edit_car && $edit_car['transmission'] == 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                                <option value="Manual" <?= $edit_car && $edit_car['transmission'] == 'Manual' ? 'selected' : '' ?>>Manual</option>
                                <option value="CVT" <?= $edit_car && $edit_car['transmission'] == 'CVT' ? 'selected' : '' ?>>CVT</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Fuel Type</label>
                            <select name="fuel_type">
                                <option value="">Select Fuel Type</option>
                                <option value="Gasoline" <?= $edit_car && $edit_car['fuel_type'] == 'Gasoline' ? 'selected' : '' ?>>Gasoline</option>
                                <option value="Diesel" <?= $edit_car && $edit_car['fuel_type'] == 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                                <option value="Electric" <?= $edit_car && $edit_car['fuel_type'] == 'Electric' ? 'selected' : '' ?>>Electric</option>
                                <option value="Hybrid" <?= $edit_car && $edit_car['fuel_type'] == 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Seating Capacity</label>
                            <select name="seating_capacity">
                                <option value="">Select Capacity</option>
                                <option value="2" <?= $edit_car && $edit_car['seating_capacity'] == '2' ? 'selected' : '' ?>>2 Seats</option>
                                <option value="4" <?= $edit_car && $edit_car['seating_capacity'] == '4' ? 'selected' : '' ?>>4 Seats</option>
                                <option value="5" <?= $edit_car && $edit_car['seating_capacity'] == '5' ? 'selected' : '' ?>>5 Seats</option>
                                <option value="7" <?= $edit_car && $edit_car['seating_capacity'] == '7' ? 'selected' : '' ?>>7 Seats</option>
                                <option value="8" <?= $edit_car && $edit_car['seating_capacity'] == '8' ? 'selected' : '' ?>>8 Seats</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Color</label>
                            <input type="text" name="color" value="<?= $edit_car ? htmlspecialchars($edit_car['color']) : '' ?>" placeholder="e.g., Red, Black, White">
                        </div>
                        
                        <div class="form-group">
                            <label>Insurance Info</label>
                            <select name="insurance_info">
                                <option value="">Select Insurance Type</option>
                                <option value="Full Coverage" <?= $edit_car && $edit_car['insurance_info'] == 'Full Coverage' ? 'selected' : '' ?>>Full Coverage</option>
                                <option value="Limited" <?= $edit_car && $edit_car['insurance_info'] == 'Limited' ? 'selected' : '' ?>>Limited</option>
                                <option value="Third Party" <?= $edit_car && $edit_car['insurance_info'] == 'Third Party' ? 'selected' : '' ?>>Third Party</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location" value="<?= $edit_car ? htmlspecialchars($edit_car['location']) : '' ?>" placeholder="Pickup location">
                        </div>
                        
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" required>
                                <option value="available" <?= $edit_car && $edit_car['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                                <option value="rented" <?= $edit_car && $edit_car['status'] == 'rented' ? 'selected' : '' ?>>Rented</option>
                                <option value="maintenance" <?= $edit_car && $edit_car['status'] == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Enter car description..."><?= $edit_car ? htmlspecialchars($edit_car['description']) : '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Car Image <?= !$edit_car ? '<span style="color:red;">*</span>' : '' ?></label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" <?= !$edit_car ? 'required' : '' ?>>
                    <?php if ($edit_car && $edit_car['image'] && file_exists($edit_car['image'])): ?>
                        <small style="display: block; margin-top: 5px;">
                            Current: <a href="<?= $edit_car['image'] ?>" target="_blank">View Image</a>
                        </small>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn-submit"><?= $edit_car ? 'Update Car' : 'Add Car' ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Cars List -->
<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-car"></i> All Cars</h2>
        <button class="btn-add" onclick="openAddCarModal()">
            <i class="fas fa-plus"></i> Add New Car
        </button>
    </div>
    
    <?php if (empty($cars)): ?>
        <div class="empty-state">
            <i class="fas fa-car"></i>
            <p>No cars in inventory. Click "Add New Car" to get started.</p>
        </div>
    <?php else: ?>
        <div class="cars-grid">
            <?php foreach ($cars as $car): ?>
                <div class="car-card">
                    <div class="car-image">
                        <?php if ($car['image'] && file_exists($car['image'])): ?>
                            <img src="<?= $car['image'] ?>" alt="<?= htmlspecialchars($car['car_name']) ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <i class="fas fa-car"></i>
                        <?php endif; ?>
                    </div>
                    <div class="car-details">
                        <h3><?= htmlspecialchars($car['car_name']) ?></h3>
                        <p><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?> (<?= $car['year'] ?>)</p>
                        <p><strong>Plate:</strong> <?= htmlspecialchars($car['plate_num']) ?></p>
                        <p><strong>Category:</strong> <?= htmlspecialchars($car['category'] ?? 'N/A') ?></p>
                        <p><strong>Transmission:</strong> <?= htmlspecialchars($car['transmission'] ?? 'N/A') ?></p>
                        <p><strong>Fuel:</strong> <?= htmlspecialchars($car['fuel_type'] ?? 'N/A') ?></p>
                        <p><strong>Seats:</strong> <?= $car['seating_capacity'] ?? 'N/A' ?></p>
                        <p><strong>Color:</strong> <?= htmlspecialchars($car['color'] ?? 'N/A') ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($car['location'] ?? 'N/A') ?></p>
                        <div class="car-price">₱<?= number_format($car['price_per_day'], 2) ?> <span style="font-size:0.7rem;">/ day</span></div>
                        <span class="status-badge status-<?= $car['status'] ?>">
                            <?= ucfirst($car['status']) ?>
                        </span>
                        <div class="car-actions">
                            <a href="?edit=<?= $car['id'] ?>" class="btn-edit">Edit</a>
                            <a href="?delete_car=<?= $car['id'] ?>" class="btn-delete" onclick="return confirm('Delete <?= htmlspecialchars($car['car_name']) ?>? This will also delete all associated rentals.')">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function openAddCarModal() {
    document.getElementById('carModal').classList.add('active');
    document.getElementById('overlay').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeCarModal() {
    document.getElementById('carModal').classList.remove('active');
    document.getElementById('overlay').style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Clear form and redirect to remove edit parameter
    if (window.location.search.includes('edit=')) {
        window.location.href = 'manage_cars.php';
    }
}
</script>

<?php require_once 'footer.php'; ?>