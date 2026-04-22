<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as Customer - CarRent</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>
    <div class="register-form">
        <h2>Register as Customer</h2>
        <form action="../processor/register_customer_process.php" method="POST">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="text" name="full_name" placeholder="Full Name" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <input type="text" name="phone" placeholder="Phone">
            </div>
            <div class="form-group">
                <textarea name="address" placeholder="Address"></textarea>
            </div>
            <button type="submit">Register</button>
        </form>
        <div class="links">
            <a href="login.php">Already have an account? Login</a><br>
            <a href="register.php">Back to Register Options</a>
        </div>
    </div>
</body>
</html>