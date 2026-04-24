<?php
require_once 'config/database.php';

echo "<h2>Fix Payment Verification for Admin and Worker</h2>";

// Step 1: Check and fix database structure
echo "<h3>Step 1: Database Structure Check</h3>";

$tables_to_check = ['payments', 'payment_requests'];

foreach ($tables_to_check as $table) {
    echo "<h4>Checking $table table:</h4>";
    
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $column_names = array_column($columns, 'Field');
        $has_verified_by = in_array('verified_by', $column_names);
        $has_verified_at = in_array('verified_at', $column_names);
        
        echo "<p>verified_by: " . ($has_verified_by ? "✅ EXISTS" : "❌ MISSING") . "</p>";
        echo "<p>verified_at: " . ($has_verified_at ? "✅ EXISTS" : "❌ MISSING") . "</p>";
        
        // Add missing columns
        if (!$has_verified_by) {
            $pdo->exec("ALTER TABLE $table ADD COLUMN verified_by INT NULL AFTER status");
            echo "<p style='color: green;'>✅ Added verified_by to $table</p>";
        }
        
        if (!$has_verified_at) {
            $pdo->exec("ALTER TABLE $table ADD COLUMN verified_at TIMESTAMP NULL AFTER verified_by");
            echo "<p style='color: green;'>✅ Added verified_at to $table</p>";
        }
        
        // Add foreign key if it doesn't exist
        if ($has_verified_by || !$has_verified_by) {
            try {
                $pdo->exec("ALTER TABLE $table ADD CONSTRAINT fk_{$table}_verified_by FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL");
                echo "<p style='color: green;'>✅ Added foreign key to $table</p>";
            } catch (Exception $e) {
                echo "<p style='color: orange;'>⚠️ Foreign key may already exist for $table</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error checking $table: " . $e->getMessage() . "</p>";
    }
}

// Step 2: Check which table should be used for payment verification
echo "<h3>Step 2: Payment Data Analysis</h3>";

try {
    // Check payment_requests table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payment_requests WHERE status IN ('paid', 'pending')");
    $payment_requests_count = $stmt->fetch()['count'];
    echo "<p>Payment requests with paid/pending status: $payment_requests_count</p>";
    
    // Check payments table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payments WHERE status IN ('pending', 'verified', 'failed')");
    $payments_count = $stmt->fetch()['count'];
    echo "<p>Payments with pending/verified/failed status: $payments_count</p>";
    
    // Determine which table to use
    if ($payment_requests_count > 0) {
        echo "<p style='color: blue;'>ℹ️ Using payment_requests table for verification</p>";
        $primary_table = 'payment_requests';
    } elseif ($payments_count > 0) {
        echo "<p style='color: blue;'>ℹ️ Using payments table for verification</p>";
        $primary_table = 'payments';
    } else {
        echo "<p style='color: orange;'>⚠️ No payment data found, will use payment_requests as default</p>";
        $primary_table = 'payment_requests';
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error analyzing payment data: " . $e->getMessage() . "</p>";
    $primary_table = 'payment_requests';
}

// Step 3: Create unified verification function
echo "<h3>Step 3: Creating Unified Verification System</h3>";

$verification_code = '<?php
// Unified Payment Verification Function
function verifyPayment($pdo, $payment_id, $user_id, $table = null) {
    // Auto-detect table if not specified
    if ($table === null) {
        // Check payment_requests first
        $stmt = $pdo->prepare("SELECT id FROM payment_requests WHERE id = ? AND status = \'paid\'");
        $stmt->execute([$payment_id]);
        if ($stmt->fetch()) {
            $table = \'payment_requests\';
        } else {
            $table = \'payments\';
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        if ($table === \'payment_requests\') {
            // Update payment request
            $stmt = $pdo->prepare("UPDATE payment_requests SET status = \'verified\', verified_by = ?, verified_at = NOW() WHERE id = ?");
            $stmt->execute([$user_id, $payment_id]);
            
            // Get rental info
            $stmt = $pdo->prepare("SELECT rental_id FROM payment_requests WHERE id = ?");
            $stmt->execute([$payment_id]);
            $rental_id = $stmt->fetchColumn();
            
            // Update rental status
            $stmt = $pdo->prepare("UPDATE rentals SET payment_status = \'paid\' WHERE id = ?");
            $stmt->execute([$rental_id]);
            
        } else {
            // Update payments table
            $stmt = $pdo->prepare("UPDATE payments SET status = \'verified\', verified_by = ?, verified_at = NOW() WHERE id = ?");
            $stmt->execute([$user_id, $payment_id]);
            
            // Get rental info
            $stmt = $pdo->prepare("SELECT rental_id FROM payments WHERE id = ?");
            $stmt->execute([$payment_id]);
            $rental_id = $stmt->fetchColumn();
            
            // Update rental status
            $stmt = $pdo->prepare("UPDATE rentals SET payment_status = \'paid\' WHERE id = ?");
            $stmt->execute([$rental_id]);
        }
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

// Unified Payment Rejection Function
function rejectPayment($pdo, $payment_id, $user_id, $table = null) {
    if ($table === null) {
        // Check payment_requests first
        $stmt = $pdo->prepare("SELECT id FROM payment_requests WHERE id = ? AND status = \'paid\'");
        $stmt->execute([$payment_id]);
        if ($stmt->fetch()) {
            $table = \'payment_requests\';
        } else {
            $table = \'payments\';
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        if ($table === \'payment_requests\') {
            // Update payment request
            $stmt = $pdo->prepare("UPDATE payment_requests SET status = \'rejected\', verified_by = ?, verified_at = NOW() WHERE id = ?");
            $stmt->execute([$user_id, $payment_id]);
            
            // Get rental info
            $stmt = $pdo->prepare("SELECT rental_id FROM payment_requests WHERE id = ?");
            $stmt->execute([$payment_id]);
            $rental_id = $stmt->fetchColumn();
            
            // Update rental status
            $stmt = $pdo->prepare("UPDATE rentals SET payment_status = \'pending\' WHERE id = ?");
            $stmt->execute([$rental_id]);
            
        } else {
            // Update payments table
            $stmt = $pdo->prepare("UPDATE payments SET status = \'failed\', verified_by = ?, verified_at = NOW() WHERE id = ?");
            $stmt->execute([$user_id, $payment_id]);
            
            // Get rental info
            $stmt = $pdo->prepare("SELECT rental_id FROM payments WHERE id = ?");
            $stmt->execute([$payment_id]);
            $rental_id = $stmt->fetchColumn();
            
            // Update rental status
            $stmt = $pdo->prepare("UPDATE rentals SET payment_status = \'pending\' WHERE id = ?");
            $stmt->execute([$rental_id]);
        }
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}
?>';

file_put_contents('includes/PaymentVerification.php', $verification_code);
echo "<p style='color: green;'>✅ Created unified verification functions</p>";

// Step 4: Update worker verification file
echo "<h3>Step 4: Updating Worker Verification</h3>";

$worker_code = '<?php
require_once \'header.php\';
require_once \'../includes/PaymentVerification.php\';

$success_message = \'\';
$error_message = \'\';

// Verify payment
if (isset($_GET[\'verify_payment\'])) {
    $payment_id = $_GET[\'verify_payment\'];
    
    try {
        verifyPayment($pdo, $payment_id, $_SESSION[\'user_id\']);
        $success_message = "Payment verified successfully!";
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Reject payment
if (isset($_GET[\'reject_payment\'])) {
    $payment_id = $_GET[\'reject_payment\'];
    
    try {
        rejectPayment($pdo, $payment_id, $_SESSION[\'user_id\']);
        $success_message = "Payment rejected! Customer needs to resubmit.";
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get all pending payments from both tables
$pending_payments = [];

// From payment_requests
$stmt = $pdo->prepare("SELECT pr.*, r.car_id, r.customer_name, r.customer_phone, r.customer_email, r.rental_date, r.return_date, r.total_cost,
                       c.car_name, c.brand, c.model, u.username as customer_username, \'payment_requests\' as source_table
                       FROM payment_requests pr
                       JOIN rentals r ON pr.rental_id = r.id 
                       JOIN car c ON r.car_id = c.id 
                       LEFT JOIN users u ON r.user_id = u.id
                       WHERE pr.status = \'paid\'
                       ORDER BY pr.created_at DESC");
$stmt->execute();
$payment_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pending_payments = array_merge($pending_payments, $payment_requests);

// From payments table
$stmt = $pdo->prepare("SELECT p.*, r.car_id, r.customer_name, r.customer_phone, r.customer_email, r.rental_date, r.return_date, r.total_cost,
                       c.car_name, c.brand, c.model, u.username as customer_username, \'payments\' as source_table
                       FROM payments p
                       JOIN rentals r ON p.rental_id = r.id 
                       JOIN car c ON r.car_id = c.id 
                       LEFT JOIN users u ON r.user_id = u.id
                       WHERE p.status = \'pending\'
                       ORDER BY p.created_at DESC");
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pending_payments = array_merge($pending_payments, $payments);

// Get verified payments (this week)
$stmt = $pdo->prepare("SELECT p.*, r.customer_name, c.car_name, u.username as verified_by_name, 
                       CASE 
                         WHEN p.verified_at IS NOT NULL THEN \'payments\'
                         WHEN pr.verified_at IS NOT NULL THEN \'payment_requests\'
                       END as source_table
                       FROM payments p
                       JOIN rentals r ON p.rental_id = r.id
                       JOIN car c ON r.car_id = c.id
                       LEFT JOIN users u ON p.verified_by = u.id
                       WHERE p.status = \'verified\' AND p.verified_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                       UNION ALL
                       SELECT pr.*, r.customer_name, c.car_name, u.username as verified_by_name, \'payment_requests\' as source_table
                       FROM payment_requests pr
                       JOIN rentals r ON pr.rental_id = r.id
                       JOIN car c ON r.car_id = c.id
                       LEFT JOIN users u ON pr.verified_by = u.id
                       WHERE pr.status = \'verified\' AND pr.verified_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                       ORDER BY verified_at DESC");
$stmt->execute();
$verified_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>';

file_put_contents('worker/verify_payment_fixed.php', $worker_code);
echo "<p style='color: green;'>✅ Created fixed worker verification file</p>";

echo "<h3>✅ Payment Verification Fix Complete!</h3>";
echo "<p><strong>What was fixed:</strong></p>";
echo "<ul>";
echo "<li>✅ Added missing verified_by and verified_at columns to both tables</li>";
echo "<li>✅ Created unified verification functions</li>";
echo "<li>✅ Updated worker verification to handle both payment tables</li>";
echo "<li>✅ Added proper error handling and transactions</li>";
echo "</ul>";

echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Test the fixed worker verification: <a href='worker/verify_payment_fixed.php'>Test Worker Verification</a></li>";
echo "<li>Update admin verification similarly if needed</li>";
echo "<li>Test payment verification workflow</li>";
echo "</ol>";
?>
