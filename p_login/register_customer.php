<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration - CarRent</title>
    <link rel="stylesheet" href="../assets/css/register.css">
    <style>
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-button:hover {
            background: #667eea;
            color: white;
            transform: translateX(-3px);
        }
    </style>
</head>
<body>
    <a href="register.php" class="back-button">← Back</a>
    
    <div class="register-form">
        <h2>👤 Register as Customer</h2>
        <p style="text-align: center; color: #666; margin-bottom: 25px; font-size: 0.95rem;">
            Create your account to start renting cars from our fleet
        </p>
        
        <form action="../processor/register_customer_process.php" method="POST">
            <input 
                type="text" 
                name="username" 
                placeholder="Username (unique identifier)" 
                required
                pattern="[a-zA-Z0-9_]{3,20}"
                title="Username: 3-20 characters, letters, numbers, underscore only"
            >
            
            <input 
                type="text" 
                name="full_name" 
                placeholder="Full Name" 
                required
                pattern="[a-zA-Z\s]{2,}"
                title="Full name should contain only letters"
            >
            
            <input 
                type="email" 
                name="email" 
                placeholder="Email Address" 
                required
            >
            
            <input 
                type="password" 
                name="password" 
                placeholder="Password (min 6 characters)" 
                required
                minlength="6"
            >
            
            <input 
                type="text" 
                name="phone" 
                placeholder="Phone Number (optional)" 
            >
            
            <textarea 
                name="address" 
                placeholder="Delivery Address (optional)"
            ></textarea>
            
            <button type="submit">Register Now</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; color: #666;">
            Already have an account? 
            <a href="login.php" style="color: #667eea; font-weight: 600; text-decoration: none;">
                Login here
            </a>
        </p>
    </div>
</body>
</html>