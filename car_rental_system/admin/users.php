<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireAdmin();

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ✅ ADD USER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['full_name'])) {
        $_SESSION['error'] = "Username, password, and full name are required!";
        header("Location: users.php");
        exit();
    }

    $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->execute([$_POST['username'], $_POST['email']]);

    if ($check->rowCount() > 0) {
        $_SESSION['error'] = "Username or email already exists!";
        header("Location: users.php");
        exit();
    }

    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, full_name, email, phone, role)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['username'],
        $hashed,
        $_POST['full_name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['role']
    ]);

    $_SESSION['success'] = "User added successfully!";
    header("Location: users.php");
    exit();
}

// ✅ EDIT USER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $stmt = $pdo->prepare("
        UPDATE users 
        SET username=?, full_name=?, email=?, phone=?, role=? 
        WHERE id=?
    ");

    $stmt->execute([
        $_POST['edit_username'],
        $_POST['edit_full_name'],
        $_POST['edit_email'],
        $_POST['edit_phone'],
        $_POST['edit_role'],
        $_POST['edit_id']
    ]);

    $_SESSION['success'] = "User updated successfully!";
    header("Location: users.php");
    exit();
}

// ✅ DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    if ($_POST['delete'] == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete your own account!";
        header("Location: users.php");
        exit();
    }

    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$_POST['delete']]);

    $_SESSION['success'] = "User deleted successfully!";
    header("Location: users.php");
    exit();
}

// FETCH USERS
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Users</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/navigation.css">
</head>
<body>

<div class="dashboard">
<?php include 'sidebar.php'; ?>

<div class="main">
<h1> User Management</h1>

<?php if(isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
<?php unset($_SESSION['success']); endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
<?php unset($_SESSION['error']); endif; ?>

<!-- ADD USER -->
<div class="panel form-panel">
<h3>Add User</h3>

<form method="POST" class="form-grid">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<input type="text" name="full_name" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email">
<input type="text" name="phone" placeholder="Phone Number">

<select name="role">
<option value="customer">Customer</option>
<option value="worker">Worker</option>
<option value="admin">Admin</option>
</select>

<button type="submit" name="add_user" class="btn-submit">Add User</button>
</form>
</div>

<!-- EDIT USER -->
<div class="panel form-panel" id="editForm" style="display:none;">
<h3>Edit User</h3>

<form method="POST" class="form-grid">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="edit_id" id="edit_id">

<input type="text" name="edit_username" id="edit_username" required>
<input type="text" name="edit_full_name" id="edit_full_name" required>
<input type="email" name="edit_email" id="edit_email">
<input type="text" name="edit_phone" id="edit_phone" placeholder="Phone Number">

<select name="edit_role" id="edit_role">
<option value="customer">Customer</option>
<option value="worker">Worker</option>
<option value="admin">Admin</option>
</select>

<div class="form-actions">
<button type="submit" name="edit_user" class="btn-submit">Update User</button>
<button type="button" onclick="hideEdit()" class="btn-cancel">Cancel</button>
</div>
</form>
</div>

<!-- USER LIST -->
<div class="panel">
<h3>User List (Total: <?= count($users) ?>)</h3>

<table class="table table-bordered">
<thead>
<tr>
<th>Username</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Role</th>
<th>Created At</th>
<th>Action</th>
</tr>
</thead>

<tbody>
<?php foreach($users as $u): ?>
<tr>
<td><?= htmlspecialchars($u['username']) ?></td>
<td><?= htmlspecialchars($u['full_name']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
<td><?= htmlspecialchars($u['phone']) ?></td>
<td>
<span class="badge bg-<?= $u['role']=='admin'?'danger':($u['role']=='worker'?'warning':'info') ?>">
<?= $u['role'] ?>
</span>
</td>
<td><?= $u['created_at'] ?></td>

<td>
<?php if($u['id'] != $_SESSION['user_id']): ?>
<div class="table-actions">

<button type="button" class="btn btn-sm btn-primary" onclick="editUser(
<?= $u['id'] ?>,
'<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>',
'<?= htmlspecialchars($u['full_name'], ENT_QUOTES) ?>',
'<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>',
'<?= htmlspecialchars($u['phone'], ENT_QUOTES) ?>',
'<?= $u['role'] ?>'
)">Edit</button>

<form method="POST" style="display:inline;">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<input type="hidden" name="delete" value="<?= $u['id'] ?>">
<button class="btn btn-sm btn-danger">Delete</button>
</form>

</div>
<?php else: ?>
<span class="badge bg-success">Current User</span>
<?php endif; ?>
</td>

</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

</div>
</div>

<script>
function editUser(id, username, fullName, email, phone, role) {
document.getElementById('edit_id').value = id;
document.getElementById('edit_username').value = username;
document.getElementById('edit_full_name').value = fullName;
document.getElementById('edit_email').value = email;
document.getElementById('edit_phone').value = phone;
document.getElementById('edit_role').value = role;

document.getElementById('editForm').style.display = 'block';
window.scrollTo({ top: document.getElementById('editForm').offsetTop, behavior: 'smooth' });
}

function hideEdit() {
document.getElementById('editForm').style.display = 'none';
}
</script>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>