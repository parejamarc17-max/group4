<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - CarRent</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                url('assets/images/register-bg.jpg') center/cover no-repeat;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.register-box {
    background: white;
    padding: 2.5rem;
    border-radius: 20px;
    width: 100%;
    max-width: 700px;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.register-title {
    font-size: 2rem;
    margin-bottom: 20px;
    color: #333;
}

.register-container {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.register-option {
    flex: 1;
    min-width: 220px;
    padding: 25px;
    border-radius: 15px;
    text-decoration: none;
    color: #333;
    border: 1px solid #eee;
    transition: 0.3s;
    background: #fafafa;
}

.register-option:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 25px rgba(0,0,0,0.15);
    border-color: #ff6b00;
}

.register-option h3 {
    margin-bottom: 10px;
    color: #ff6b00;
}

.register-option p {
    font-size: 0.9rem;
    color: #666;
}

.back-link {
    display: block;
    margin-top: 20px;
    font-size: 0.9rem;
}

.back-link a {
    text-decoration: none;
    color: #ff6b00;
}

@media (max-width: 600px) {
    .register-container {
        flex-direction: column;
    }
}
</style>

</head>

<body>

<div class="register-box">
    <h2 class="register-title">Register As</h2>

    <div class="register-container">
        <a href="register_admin.php" class="register-option">
            <h3>Admin</h3>
            <p>Register as an admin to manage the system</p>
        </a>

        <a href="register_worker.php" class="register-option">
            <h3>Worker</h3>
            <p>Join our team as a worker</p>
        </a>
    </div>

    <div class="back-link">
        <a href="login.php">← Back to Login</a>
    </div>
</div>

</body>
</html>