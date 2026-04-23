<?php
require_once '../config/auth.php';
require_once '../config/database.php';
checkAuth();

// Check if user is customer
if ($_SESSION['role'] !== 'customer') {
    header('Location: ../p_login/login.php?error=Access denied');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid request';
    } else {
        try {
            // Update user information
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
            $stmt->execute([$_POST['full_name'], $_POST['email'], $user_id]);
            
            // Update customer details if they exist
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $customer_exists = $stmt->fetch();
            
            if ($customer_exists) {
                $stmt = $pdo->prepare("UPDATE customers SET phone = ?, address = ? WHERE user_id = ?");
                $stmt->execute([$_POST['phone'], $_POST['address'], $user_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO customers (user_id, phone, address) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $_POST['phone'], $_POST['address']]);
            }
            
            // Handle password change
            if (!empty($_POST['new_password'])) {
                if ($_POST['new_password'] !== $_POST['confirm_password']) {
                    $error_message = 'Passwords do not match';
                } else {
                    $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $user_id]);
                }
            }
            
            if (!$error_message) {
                $success_message = 'Profile updated successfully!';
                // Refresh session data
                $_SESSION['full_name'] = $_POST['full_name'];
            }
        } catch (PDOException $e) {
            $error_message = 'Error updating profile: ' . $e->getMessage();
        }
    }
}

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get customer details
$stmt = $pdo->prepare("SELECT * FROM customers WHERE user_id = ?");
$stmt->execute([$user_id]);
$customer_details = $stmt->fetch();

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CarRent</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/customer-style.css">
</head>
<body>

<header>
    <div class="custom-header">
        <div class="header-left">
            <div class="hamburger-btn" onclick="toggleMenuCustomer()" title="Menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <h2>My Profile</h2>
        </div>
        <div class="header-right">
            <div class="user-section">
                <span class="username">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Customer'); ?>
                </span>
            </div>
        </div>
    </div>
</header>

<div class="side-menu" id="customerMenu">
    <img src="../assets/images/logo.png" class="profile-img" style="width:60px;height:60px;border-radius:50%;margin:10px auto;display:block;">
    <h2>DRIVE CUSTOMER</h2>
    <a href="dashboard.php" class="btn-nav"> Dashboard</a>
    <a href="bookings.php" class="btn-nav"> My Bookings</a>
    <a href="profile.php" class="btn-nav"> My Profile</a>
    <a href="../car.php" class="btn-nav"> Browse Cars</a>
    <a href="../p_login/logout.php" class="btn-nav"> Logout</a>
</div>

<div class="overlay" id="customerOverlay" onclick="closeMenuCustomer()"></div>

<div class="dashboard">
    <div class="main" style="margin-left: 0; width: 100%;">
        <h1>My Profile</h1>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        
        <div class="dashboard">
    <div class="main" style="margin-left: 0; width: 100%;">
        <h1>Profile Settings</h1>
        
        <div class="cards">
            <!-- Update Profile Form -->
            <div class="card">
                <h2>Update Information</h2>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                        <small>Username cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($customer_details['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?= htmlspecialchars($customer_details['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Leave blank to keep current">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            
            <!-- Account Overview -->
            <div class="card">
                <h2>Account Overview</h2>
                <div class="account-details">
                    <div class="detail-item">
                        <span class="detail-label">Username</span>
                        <span class="detail-value"><?= htmlspecialchars($user['username']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Full Name</span>
                        <span class="detail-value"><?= htmlspecialchars($user['full_name']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phone</span>
                        <span class="detail-value"><?= htmlspecialchars($customer_details['phone'] ?? 'Not provided') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Address</span>
                        <span class="detail-value"><?= htmlspecialchars($customer_details['address'] ?? 'Not provided') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Account Type</span>
                        <span class="detail-value">Customer</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Member Since</span>
                        <span class="detail-value"><?= date('M d, Y', strtotime($user['created_at'])) ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <h2>Quick Actions</h2>
                <div class="action-links">
                    <a href="bookings.php" class="action-link">
                        <span class="action-icon">📋</span>
                        <span class="action-text">My Bookings</span>
                    </a>
                    <a href="../car.php" class="action-link">
                        <span class="action-icon">🚗</span>
                        <span class="action-text">Browse Cars</span>
                    </a>
                    <a href="dashboard.php" class="action-link">
                        <span class="action-icon">🏠</span>
                        <span class="action-text">Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger-btn');
    const menu = document.getElementById("customerMenu");
    const overlay = document.getElementById("customerOverlay");

    function toggleMenuCustomer() {
        if (menu.classList.contains("active")) {
            closeMenuCustomer();
        } else {
            openMenuCustomer();
        }
    }

    function openMenuCustomer() {
        menu.classList.add("active");
        overlay.classList.add("active");
        hamburger.classList.add("active");
        document.body.style.overflow = 'hidden';
    }

    function closeMenuCustomer() {
        menu.classList.remove("active");
        overlay.classList.remove("active");
        hamburger.classList.remove("active");
        document.body.style.overflow = '';
    }

    // Attach event listeners
    if (hamburger) {
        hamburger.addEventListener('click', toggleMenuCustomer);
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeMenuCustomer);
    }

    // Close menu on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && menu.classList.contains("active")) {
            closeMenuCustomer();
        }
    });

    // Make functions global for onclick attributes
    window.toggleMenuCustomer = toggleMenuCustomer;
    window.closeMenuCustomer = closeMenuCustomer;

    // Password confirmation validation
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (newPasswordInput && confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            const newPassword = newPasswordInput.value;
            const confirmPassword = this.value;
            
            if (newPassword && confirmPassword && newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        newPasswordInput.addEventListener('input', function() {
            const confirmPassword = confirmPasswordInput.value;
            const newPassword = this.value;
            
            if (newPassword && confirmPassword && newPassword !== confirmPassword) {
                confirmPasswordInput.setCustomValidity('Passwords do not match');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        });
    }
});
</script>

</body>
</html>
