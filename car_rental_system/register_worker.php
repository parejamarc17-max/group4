<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply as Worker - CarRent</title>

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
    max-width: 450px;
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
    margin-top: 5px;
}

button:hover {
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

.experience-hint {
    font-size: 0.8rem;
    color: #888;
    margin-bottom: 10px;
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
    <h2>Apply as Worker</h2>
    <p class="experience-hint">Join our team and start working with us 🚗</p>
    
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

    <form action="../processor/register_process.php" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="phone" placeholder="Phone" required>
        <textarea name="address" placeholder="Address" required></textarea>
        <input type="text" name="experience" placeholder="Years of Experience" required>

        <button type="submit">Apply Now</button>
    </form>

    <a href="login.php">Already have an account? Login</a>
    <a href="register.php">← Back to Registration Options</a>
</div>

</body>
</html>