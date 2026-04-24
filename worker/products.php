<?php
require_once '../config/database.php';
session_start();

function peso($amount) {
    return '₱' . number_format((float)$amount, 2, '.', ',');
}

/* ADD PRODUCT */
if (isset($_POST['add'])) {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF");
    }

    $imageName = time() . '_' . $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $imageName);

    $stmt = $pdo->prepare("INSERT INTO products 
        (name, category, price, stock, description, image) 
        VALUES (?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $_POST['name'],
        $_POST['category'],
        $_POST['price'],
        $_POST['stock'],
        $_POST['description'],
        $imageName
    ]);
}

/* DELETE */
if (isset($_POST['delete'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF");
    }
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$_POST['delete']]);
}
if ($_SESSION['role'] !== 'worker') die("Access denied");

$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
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
            <h2> Product Management</h2>
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
    <h2> DRIVE </h2>
    <a href="worker_dashboard.php" class="btn-nav"> Dashboard</a>
    <a href="manage_car.php" class="btn-nav"> Manage Cars</a>
    <a href="rentals.php" class="btn-nav"> Rentals</a>
    <a href="products.php" class="btn-nav"> Products</a>
    <a href="sales.php" class="btn-nav"> Sales</a>
    <a href="customer_list.php" class="btn-nav"> Customer List</a>
    <a href="../p_login/logout.php" class="btn-nav"> Logout</a>
</div>

<div class="overlay" id="adminOverlay" onclick="closeMenuAdmin()"></div>

<div class="dashboard">

    <div class="main">
        <h1> Product Management</h1>
        
        <div class="panel">
            <h3>Add Product</h3>
            <?php if(isset($success)): ?><p style="color:green; font-weight: bold; margin-bottom: 15px;"><?php echo $success; ?></p><?php endif; ?>
            <form method="POST" class="add-product-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter product name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" placeholder="Enter category">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (₱)</label>
                        <input type="number" id="price" name="price" step="0.01" placeholder="0.00" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock Quantity</label>
                        <input type="number" id="stock" name="stock" placeholder="0" required>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Enter product description" rows="3"></textarea>
                </div>
                
                <button type="submit" name="add" class="btn-add">Add Product</button>
            </form>
        </div>
        
        <div class="panel">
            <h3>Product List</h3>
            <table>
                <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach($products as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['category']) ?></td>
                        <td>$<?= number_format($p['price'], 2) ?></td>
                        <td><?= $p['stock'] ?></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="delete" value="<?= $p['id'] ?>">
                                <button type="submit" style="color:red; background:none; border:none; cursor:pointer;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<title>Products</title>

<style>
body {font-family: Arial;}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(230px,1fr));
    gap: 20px;
}

.card {
    border:1px solid #ddd;
    padding:15px;
    border-radius:10px;
    text-align:center;
}

.card img {
    width:100%;
    height:150px;
    object-fit:cover;
}

.price {
    color:#ff6b00;
    font-weight:bold;
}

.details {
    display:none;
    margin-top:10px;
    text-align:left;
    background:#f9f9f9;
    padding:10px;
    border-radius:8px;
}

button {
    padding:6px 10px;
    margin-top:5px;
    cursor:pointer;
}
</style>

<script>
function toggleDetails(id){
    let el = document.getElementById("details-"+id);
    el.style.display = el.style.display === "block" ? "none" : "block";
}
</script>

</head>
<body>

<h2>Add Product</h2>
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<input name="name" placeholder="Name" required>
<input name="category" placeholder="Category">
<input name="price" type="number" step="0.01" placeholder="Price" required>
<input name="stock" type="number" placeholder="Stock" required>
<input name="description" placeholder="Description">

<input type="file" name="image" required>

<button name="add">Add</button>
</form>

<hr>

<div class="product-grid">

<?php foreach($products as $p): ?>

<div class="card">

<img src="../uploads/<?= $p['image'] ?>">

<h4><?= $p['name'] ?></h4>
<div class="price">$<?= number_format($p['price'],2) ?></div>

<button onclick="toggleDetails(<?= $p['id'] ?>)">View</button>

<form method="POST">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="delete" value="<?= $p['id'] ?>">
<button>Delete</button>
</form>

<div class="details" id="details-<?= $p['id'] ?>">

<p><?= $p['description'] ?></p>

<?php
$fb = $pdo->prepare("SELECT * FROM feedback WHERE product_id=?");
$fb->execute([$p['id']]);
foreach($fb as $f):
?>

<div>
<strong><?= htmlspecialchars($f['name']) ?></strong>
⭐ <?= $f['rating'] ?>/5
<p><?= htmlspecialchars($f['message']) ?></p>
</div>

<?php endforeach; ?>

<h4>Add Feedback</h4>
<form method="POST">

<input type="hidden" name="product_id" value="<?= $p['id'] ?>">

<input name="name" placeholder="Your name" required>
<textarea name="message" placeholder="Feedback" required></textarea>

<select name="rating">
<option value="5">5 ⭐</option>
<option value="4">4 ⭐</option>
<option value="3">3 ⭐</option>
<option value="2">2 ⭐</option>
<option value="1">1 ⭐</option>
</select>

<button name="feedback">Submit</button>

</form>

</div>
</div>

<?php endforeach; ?>

</div>

</body>
</html>
<h1>📦 Products</h1>

<table border="1">
<tr>
    <th>Name</th>
    <th>Category</th>
    <th>Price</th>
    <th>Stock</th>
</tr>

<?php foreach($products as $p): ?>
<tr>
    <td><?= $p['name'] ?></td>
    <td><?= $p['category'] ?></td>
    <td><?= $p['price'] ?></td>
    <td><?= $p['stock'] ?></td>
</tr>
<?php endforeach; ?>

</table>
