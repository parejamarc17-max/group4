<?php
require_once '../config/auth.php';
require_once '../config/database.php';
requireAdmin();

if (empty(₱_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

/* ADD FEEDBACK */
if (isset($_POST['feedback'])) {
    $stmt = $pdo->prepare("INSERT INTO feedback (product_id, name, message, rating) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['product_id'],
        $_POST['name'],
        $_POST['message'],
        $_POST['rating']
    ]);
}

/* FETCH */
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
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