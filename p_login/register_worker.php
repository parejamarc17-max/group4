<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as Worker - CarRent</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>
    <div class="register-form">
        <h2>Apply as Worker</h2>
        <form action="../processor/register_worker_process.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="phone" placeholder="Phone" required>
            <textarea name="address" placeholder="Address" required></textarea>
            <input type="text" name="experience" placeholder="Years of Experience" required>
            <button type="submit">Apply</button>
        </form>
        <a href="login.php">Already have an account? Login</a>
    </div>
</body>
</html>