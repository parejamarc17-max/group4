<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['action'])) {

        // DELETE
        if ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $_SESSION['success'] = "Car deleted successfully!";
            header("Location: cars.php");
            exit();
        }

        // EDIT
        elseif ($_POST['action'] === 'edit') {
            $image_path = $_POST['existing_image'];

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
                $upload_dir = '../assets/images/';
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $unique_name = uniqid('car_', true) . '.' . $file_extension;
                $target_file = $upload_dir . $unique_name;

                if (getimagesize($_FILES['image']['tmp_name'])) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_path = 'assets/images/' . $unique_name;
                    }
                }
            }

            $stmt = $pdo->prepare("
                UPDATE cars SET 
                    car_name = ?, 
                    brand = ?, 
                    price_per_day = ?, 
                    image = ?,
                    status = ?,
                    year = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $_POST['car_name'],
                $_POST['brand'],
                $_POST['price'],
                $image_path,
                $_POST['status'],
                $_POST['year'],
                $_POST['id']
            ]);

            $_SESSION['success'] = "Car updated successfully!";
            header("Location: cars.php");
            exit();
        }

    } else {

        // ADD NEW CAR
        $image_path = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir = '../assets/images/';
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $unique_name = uniqid('car_', true) . '.' . $file_extension;
            $target_file = $upload_dir . $unique_name;

            if (getimagesize($_FILES['image']['tmp_name'])) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = 'assets/images/' . $unique_name;
                } else {
                    $_SESSION['error'] = 'Failed to upload image';
                    header("Location: cars.php");
                    exit();
                }
            } else {
                $_SESSION['error'] = 'File is not an image';
                header("Location: cars.php");
                exit();
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO cars (car_name, brand, price_per_day, image, status, year)
            VALUES (?, ?, ?, ?, 'available', ?)
        ");

        $stmt->execute([
            $_POST['car_name'],
            $_POST['brand'],
            $_POST['price'],
            $image_path,
            $_POST['year']
        ]);

        $_SESSION['success'] = "Car added successfully!";
        header("Location: cars.php");
        exit();
    }
}

$cars = $pdo->query("SELECT * FROM cars ORDER BY id DESC")->fetchAll();

// Get unique brands from existing cars for the datalist
$brandsList = $pdo->query("SELECT DISTINCT brand FROM cars ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN);
$defaultBrands = ['Toyota', 'Honda', 'Mitsubishi', 'Nissan', 'Ford', 'Hyundai', 'Kia', 'Suzuki', 'BMW', 'Mercedes-Benz'];
$allBrands = array_unique(array_merge($defaultBrands, $brandsList));
sort($allBrands);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cars - Admin</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/navigation.css">
</head>
<body>

<div class="dashboard">

<?php include 'sidebar.php'; ?>

<div class="main">
    <h1><strong>Cars Management</strong></h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- FORM -->
    <div class="form-container">
        <h3 id="form-title">+ Add New Car</h3>

        <form method="POST" enctype="multipart/form-data" class="row g-2" id="car-form">
            <input type="hidden" name="id" id="car-id">
            <input type="hidden" name="action" id="car-action">
            <input type="hidden" name="existing_image" id="existing-image">

            <div class="col-md-6">
                <input type="text" name="car_name" id="car-name" class="form-control" placeholder="Car Name" required>
            </div>

            <!-- ✅ BRAND with DATALIST (allows custom input) -->
            <div class="col-md-6">
                <input type="text" name="brand" id="brand" class="form-control" 
                       placeholder="Select or type brand" list="brand-list" required autocomplete="off">
                <datalist id="brand-list">
                    <?php foreach($allBrands as $brand): ?>
                        <option value="<?= htmlspecialchars($brand) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="col-md-6">
                <input type="number" name="price" id="price" class="form-control" placeholder="Price per day" required>
            </div>

            <div class="col-md-6">
                <input type="number" name="year" id="year" class="form-control" placeholder="Year" required>
            </div>

            <div class="col-md-6">
                <input type="file" name="image" id="image" class="form-control">
                <small class="text-muted" id="image-hint">Only required for new cars</small>
            </div>

            <div class="col-md-6">
                <select name="status" id="status" class="form-control">
                    <option value="available">Available</option>
                    <option value="rented">Rented</option>
                </select>
            </div>

            <div class="col-md-12">
                <button type="submit" class="btn btn-primary" id="submit-btn">Add Car</button>
                <button type="button" class="btn btn-secondary" id="cancel-btn" style="display:none;">Cancel</button>
            </div>
        </form>
    </div>

    <!-- TABLE -->
    <div class="panel mt-4">
        <h3>Car Inventory</h3>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Year</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach($cars as $c): ?>
                <tr>
                    <td><img src="../<?= $c['image'] ?>" width="60"></td>
                    <td><?= htmlspecialchars($c['car_name']) ?></td>
                    <td><?= htmlspecialchars($c['brand']) ?></td>
                    <td><?= $c['year'] ?></td>
                    <td>₱<?= number_format($c['price_per_day'],2) ?></td>
                    <td><?= $c['status'] ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm"
                        onclick="editCar(<?= $c['id'] ?>,'<?= htmlspecialchars($c['car_name']) ?>','<?= htmlspecialchars($c['brand']) ?>',<?= $c['price_per_day'] ?>,'<?= $c['image'] ?>','<?= $c['status'] ?>','<?= $c['year'] ?>')">
                        Edit</button>

                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this car?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                     </td>
                </tr>
                <?php endforeach; ?>
            </tbody>

         </table>
    </div>

</div>
</div>

<script>
function editCar(id, name, brand, price, image, status, year){
    document.getElementById('form-title').innerText = 'Edit Car';
    document.getElementById('car-id').value = id;
    document.getElementById('car-action').value = 'edit';
    document.getElementById('existing-image').value = image;
    document.getElementById('car-name').value = name;
    document.getElementById('brand').value = brand;
    document.getElementById('price').value = price;
    document.getElementById('year').value = year;
    document.getElementById('status').value = status;
    document.getElementById('submit-btn').innerText = 'Update Car';
    document.getElementById('cancel-btn').style.display = 'inline';
    document.getElementById('image').removeAttribute('required');
    document.getElementById('image-hint').innerText = 'Optional - leave empty to keep current image';
}

document.getElementById('cancel-btn').onclick = function(){
    location.reload();
}

// Make image required only for new cars
document.getElementById('car-form').addEventListener('submit', function(e) {
    var action = document.getElementById('car-action').value;
    var imageField = document.getElementById('image');
    
    if (!action || action === '') {
        // New car - image is required
        if (!imageField.files || imageField.files.length === 0) {
            e.preventDefault();
            alert('Please select an image for the new car');
            return false;
        }
    }
});
</script>

</body>
</html>