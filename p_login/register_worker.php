<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply as Worker - CarRent</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/register.css">
</head>

<body>
    <div class="register-box">
    <div class="register-header">
        <h2>👷 Apply as Worker</h2>
        <p>Fill in your details to join our team</p>
    </div>

    <?php 
    $error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
    if ($error): 
    ?>
        <div class="error-message" style="color: #d32f2f; background: #ffebee; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #d32f2f;">
            ❌ <?= $error ?>
        </div>
    <?php endif; ?>

    <form action="../processor/register_worker_process.php" method="POST">

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
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        </div>

        <div class="form-group">
            <input type="text" name="phone" placeholder="Phone Number" required>
        </div>

        <div class="form-group">
            <textarea name="address" placeholder="Address" rows="2" required></textarea>
        </div>

        <div class="form-group">
            <input type="number" name="experience" placeholder="Years of Experience" required>
        </div>

        <button type="submit">Submit Application</button>
    </form>

    <div class="links">
        <a href="login.php">Already have an account? Login</a><br>
        <a href="register.php">Back to Register Options</a>
    </div>
    
</body>
</html>