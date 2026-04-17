<?php
session_start();
require_once 'config/database.php';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (!empty($username) && !empty($password)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid username or password!';
            }
        } else {
            $error = 'Please fill in all fields!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - CarRent System</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', sans-serif;

    /* BACKGROUND IMAGE */
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                url('assets/images/login_background.png') center/cover no-repeat;

    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* CONTAINER */
.login-container {
    background: white;
    padding: 2.5rem;
    border-radius: 20px;
    width: 100%;
    max-width: 380px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    text-align: center;
}

/* HEADER */
.logo {
    width: 70px;
    margin-bottom: 10px;
}

h1 {
    margin-bottom: 5px;
}

.login-container p {
    color: #666;
    margin-bottom: 20px;
}

/* ALERT */
.alert {
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 0.9rem;
}

.error {
    background: #ffe5e5;
    color: #d8000c;
}

.success {
    background: #e6ffed;
    color: #2e7d32;
}

/* FORM */
.form-group {
    text-align: left;
    margin-bottom: 15px;
}

.form-group label {
    font-size: 0.9rem;
    margin-bottom: 5px;
    display: block;
}

.form-group input {
    width: 100%;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #ddd;
    outline: none;
    transition: 0.3s;
}

.form-group input:focus {
    border-color: #ff6b00;
    box-shadow: 0 0 5px rgba(255,107,0,0.3);
}

/* BUTTON */
button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 30px;
    background: #ff6b00;
    color: white;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: #e65c00;
}

/* REGISTER LINK */
.register-link {
    margin-top: 15px;
    font-size: 0.9rem;
}

.register-link a {
    color: #ff6b00;
    text-decoration: none;
}

.register-link a:hover {
    text-decoration: underline;
}

/* SHOW PASSWORD CHECKBOX STYLES */
.show-password-container {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    cursor: pointer;
}

.show-password-container input {
    width: auto;
    cursor: pointer;
    margin: 0;
}

.show-password-container label {
    margin: 0;
    cursor: pointer;
    font-size: 0.85rem;
    color: #555;
    font-weight: normal;
}

.show-password-container label:hover {
    color: #ff6b00;
}
</style>

</head>

<body>

<div class="login-container">
    
    <img src="assets/images/logo.png" alt="Logo" class="logo">
    <h1>CarRent</h1>
    <p>Drive your dream car</p>

    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required autocomplete="username">
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" id="password" required autocomplete="current-password">
            
            <!-- Checkbox to show/hide password -->
            <div class="show-password-container">
                <input type="checkbox" id="showPasswordCheckbox" onclick="togglePassword()">
                <label for="showPasswordCheckbox">Show Password</label>
            </div>
        </div>
        
        <button type="submit">Login</button>
    </form>

    <p class="register-link">
        <a href="register.php">Don't have an account? Register</a>
    </p>

</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById("password");
    const checkbox = document.getElementById("showPasswordCheckbox");
    
    if (checkbox.checked) {
        passwordInput.type = "text";
    } else {
        passwordInput.type = "password";
    }
}
</script>

</body>
</html>