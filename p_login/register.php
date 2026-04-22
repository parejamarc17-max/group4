<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - CarRent</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: #000000;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* Container */
.register-box {
    background: #fff;
    padding: 40px;
    border-radius: 15px;
    width: 100%;
    max-width: 420px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

/* Title */
.register-title {
    font-size: 1.6rem;
    margin-bottom: 25px;
    color: #333;
}

/* Options */
.register-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

/* Card */
.register-option {
    text-decoration: none;
    background: #f9fafb;
    padding: 20px;
    border-radius: 10px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    color: #333;
}

.register-option:hover {
    border-color: #667eea;
    background: #eef2ff;
    transform: translateY(-3px);
}

/* Text */
.register-option h3 {
    margin: 0;
    font-size: 1.2rem;
}

.register-option p {
    font-size: 0.85rem;
    color: #666;
    margin-top: 5px;
}

/* Back link */
.back-link {
    margin-top: 20px;
    font-size: 0.85rem;
}

.back-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.back-link a:hover {
    text-decoration: underline;
}
</style>
</head>

<body>

<div class="register-box">
    <h2 class="register-title">Create an Account</h2>

    <div class="register-container">
        <a href="register_customer.php" class="register-option">
            <h3> Customer</h3>
            <p>Register to browse and rent cars</p>
        </a>

        <a href="register_worker.php" class="register-option">
            <h3> Apply for Work</h3>
            <p>Join our team and manage vehicles</p>
        </a>
    </div>

    <div class="back-link">
        <a href="login.php">← Back to Login</a>
    </div>
</div>

</body>
</html>