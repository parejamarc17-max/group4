<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireWorker();

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['category'], $_POST['price'], $_POST['stock'], $_POST['description']]);
    $success = "Product added!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$_POST['delete']]);
    header("Location: products.php");
    exit();
}

$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="dashboard">
<div class="sidebar" id="sideMenu">
    <img src="assets/images/download (4).jpg" class="profile-img" style="width:60px;height:60px;border-radius:50%;margin:10px auto;display:block;">
    <h2>🚗 DRIVE WORKER</h2>
    <a href="worker.php" class="btn-nav" onclick="closeMenus()">← Home</a>
    <a href="worker_dashboard.php" class="btn-nav" onclick="closeMenus()">📊 Dashboard</a>
    <a href="worker_manage_car.php" class="btn-nav" onclick="closeMenus()">🚘 Manage Cars</a>
    <a href="worker_rentals.php" class="btn-nav" onclick="closeMenus()">📅 Rentals</a>
    <a href="worker_products.php" class="btn-nav" onclick="closeMenus()">📦 Products</a>
    <a href="worker_sales.php" class="btn-nav" onclick="closeMenus()">💰 Sales</a>
    <a href="p_login/logout.php" class="btn-nav" onclick="closeMenus()">🚪 Logout</a>
</div>

    <div class="main">
        <h1>📦 Product Management</h1>
        
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
                        <label for="price">Price ($)</label>
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

<style>
.add-product-form {
    max-width: 600px;
    margin: 0 auto;
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.form-group {
    flex: 1;
}

.form-group.full-width {
    width: 100%;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #ff6b00;
    box-shadow: 0 0 5px rgba(255, 107, 0, 0.3);
}

.btn-add {
    background: #ff6b00;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: background 0.3s;
    width: 100%;
}

.btn-add:hover {
    background: #e55a00;
}
</style>
</body>
</html>
