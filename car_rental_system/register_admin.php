<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register Admin - CarRent</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                url('../assets/images/register-bg.jpg') center/cover no-repeat;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.register-form {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    text-align: center;
}

.register-form h2 {
    margin-bottom: 20px;
    color: #333;
}

.register-form input,
.register-form textarea {
    width: 100%;
    padding: 12px;
    margin-bottom: 12px;
    border-radius: 10px;
    border: 1px solid #ddd;
    outline: none;
    transition: 0.3s;
    font-size: 0.95rem;
}

.register-form textarea {
    resize: none;
    height: 80px;
}

.register-form input:focus,
.register-form textarea:focus {
    border-color: #ff6b00;
    box-shadow: 0 0 6px rgba(255,107,0,0.3);
}

/* Password wrapper for better positioning */
.password-wrapper {
    position: relative;
    margin-bottom: 12px;
}

.password-wrapper input {
    margin-bottom: 0;
    padding-right: 45px;
}

/* Show password checkbox container */
.show-password-container {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    margin-bottom: 12px;
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

/* Eye button style (alternative) */
.toggle-password-eye {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    background: none;
    border: none;
    font-size: 1.2rem;
    padding: 0;
    width: auto;
    color: #666;
}

.toggle-password-eye:hover {
    background: none;
    color: #ff6b00;
    transform: translateY(-50%) scale(1.1);
}

button[type="submit"] {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 30px;
    background: #ff6b00;
    color: white;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 5px;
}

button[type="submit"]:hover {
    background: #e65c00;
}

.register-form a {
    display: block;
    margin-top: 15px;
    font-size: 0.9rem;
    color: #ff6b00;
    text-decoration: none;
}

.register-form a:hover {
    text-decoration: underline;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 0.9rem;
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 0.9rem;
}

@media (max-width: 480px) {
    .register-form {
        margin: 1rem;
        padding: 1.5rem;
    }
}
</style>

</head>

<body>

<div class="register-form">
    <h2>Register as Admin</h2>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="error-message">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="success-message">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form action="register_admin_process.php" method="POST">
        <input type="text" name="username" placeholder="Username" required autocomplete="username">
        <input type="text" name="full_name" placeholder="Full Name" required autocomplete="name">
        <input type="email" name="email" placeholder="Email" autocomplete="email">
        
        <!-- Password field with show/hide option -->
        <div class="password-wrapper">
            <input type="password" name="password" id="password" placeholder="Password" required autocomplete="new-password">
        </div>
        
        <!-- Checkbox to show/hide password -->
        <div class="show-password-container">
            <input type="checkbox" id="showPasswordCheckbox" onclick="togglePassword()">
            <label for="showPasswordCheckbox">Show Password</label>
        </div>
        
        <input type="text" name="phone" placeholder="Phone" autocomplete="tel">
        <textarea name="address" placeholder="Address"></textarea>

        <button type="submit">Register as Admin</button>
    </form>

    <a href="login.php">Already have an account? Login</a>
    <a href="register.php">← Back to Registration Options</a>
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