<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply as Worker - CarRent</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #f093fb, #f5576c);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

/* Container */
.register-box {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

/* Header */
.register-header {
    text-align: center;
    margin-bottom: 20px;
}

.register-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: #333;
}

.register-header p {
    font-size: 0.85rem;
    color: #777;
}

/* Inputs */
.form-group {
    margin-bottom: 15px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ddd;
    font-size: 0.9rem;
    transition: 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #f5576c;
    outline: none;
}

/* Button */
button {
    width: 100%;
    padding: 10px;
    border: none;
    background: #f5576c;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.95rem;
}

button:hover {
    background: #e94a5a;
}

/* Footer links */
.links {
    text-align: center;
    margin-top: 15px;
    font-size: 0.85rem;
}

.links a {
    color: #f5576c;
    text-decoration: none;
    font-weight: 500;
}

.links a:hover {
    text-decoration: underline;
}
</style>
</head>

<body>

<div class="register-box">
    <div class="register-header">
        <h2>👷 Apply as Worker</h2>
        <p>Fill in your details to join our team</p>
    </div>

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
        <a href="login.php">← Already have an account? Login</a><br>
        <a href="register.php">Back to Register Options</a>
    </div>
</div>

</body>
</html>