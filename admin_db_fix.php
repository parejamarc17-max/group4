<?php
// Admin verification database fix
require_once 'config/database.php';

echo "<h2>ADMIN VERIFICATION DATABASE FIX</h2>";

try {
    echo "<h3>Step 1: Fix payments table</h3>";
    
    // Add verified_by to payments if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE payments ADD COLUMN verified_by INT NULL AFTER status");
        echo "<p style='color: green;'>✅ Added verified_by to payments table</p>";
    } catch (Exception $e) {
        echo "<p style='color: blue;'>ℹ️ verified_by already exists in payments</p>";
    }
    
    // Add verified_at to payments if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE payments ADD COLUMN verified_at TIMESTAMP NULL AFTER verified_by");
        echo "<p style='color: green;'>✅ Added verified_at to payments table</p>";
    } catch (Exception $e) {
        echo "<p style='color: blue;'>ℹ️ verified_at already exists in payments</p>";
    }
    
    echo "<h3>Step 2: Fix payment_requests table</h3>";
    
    // Add verified_by to payment_requests if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE payment_requests ADD COLUMN verified_by INT NULL AFTER status");
        echo "<p style='color: green;'>✅ Added verified_by to payment_requests table</p>";
    } catch (Exception $e) {
        echo "<p style='color: blue;'>ℹ️ verified_by already exists in payment_requests</p>";
    }
    
    // Add verified_at to payment_requests if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE payment_requests ADD COLUMN verified_at TIMESTAMP NULL AFTER verified_by");
        echo "<p style='color: green;'>✅ Added verified_at to payment_requests table</p>";
    } catch (Exception $e) {
        echo "<p style='color: blue;'>ℹ️ verified_at already exists in payment_requests</p>";
    }
    
    echo "<h3>Step 3: Verify table structures</h3>";
    
    // Check payments table
    $stmt = $pdo->query("DESCRIBE payments");
    $payments_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $payments_has_verified_by = false;
    $payments_has_verified_at = false;
    
    echo "<h4>Payments table:</h4>";
    foreach ($payments_columns as $col) {
        if ($col['Field'] === 'verified_by') $payments_has_verified_by = true;
        if ($col['Field'] === 'verified_at') $payments_has_verified_at = true;
        echo "<p>{$col['Field']} - {$col['Type']}</p>";
    }
    
    // Check payment_requests table
    $stmt = $pdo->query("DESCRIBE payment_requests");
    $pr_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $pr_has_verified_by = false;
    $pr_has_verified_at = false;
    
    echo "<h4>Payment_requests table:</h4>";
    foreach ($pr_columns as $col) {
        if ($col['Field'] === 'verified_by') $pr_has_verified_by = true;
        if ($col['Field'] === 'verified_at') $pr_has_verified_at = true;
        echo "<p>{$col['Field']} - {$col['Type']}</p>";
    }
    
    echo "<h3>Step 4: Test admin verification queries</h3>";
    
    // Test payment_requests query (used in admin verification)
    try {
        $stmt = $pdo->prepare("SELECT pr.*, u.username as verified_by_name 
                               FROM payment_requests pr 
                               LEFT JOIN users u ON pr.verified_by = u.id 
                               WHERE pr.status = 'verified' 
                               LIMIT 5");
        $stmt->execute();
        $results = $stmt->fetchAll();
        echo "<p style='color: green;'>✅ Admin payment_requests query works! Found " . count($results) . " verified payments</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Admin payment_requests query failed: " . $e->getMessage() . "</p>";
    }
    
    // Test payments query
    try {
        $stmt = $pdo->prepare("SELECT p.*, u.username as verified_by_name 
                               FROM payments p 
                               LEFT JOIN users u ON p.verified_by = u.id 
                               WHERE p.status = 'verified' 
                               LIMIT 5");
        $stmt->execute();
        $results = $stmt->fetchAll();
        echo "<p style='color: green;'>✅ Admin payments query works! Found " . count($results) . " verified payments</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Admin payments query failed: " . $e->getMessage() . "</p>";
    }
    
    // Check final status
    echo "<h3>Final Status:</h3>";
    
    $all_good = true;
    
    if (!$payments_has_verified_by || !$payments_has_verified_at) {
        echo "<p style='color: red;'>❌ Payments table missing columns</p>";
        $all_good = false;
    }
    
    if (!$pr_has_verified_by || !$pr_has_verified_at) {
        echo "<p style='color: red;'>❌ Payment_requests table missing columns</p>";
        $all_good = false;
    }
    
    if ($all_good) {
        echo "<h2 style='color: green;'>✅ ADMIN VERIFICATION FIX COMPLETE!</h2>";
        echo "<p>All database columns are now present. The admin verification should work properly.</p>";
        echo "<p><a href='admin/verify_payment.php'>Test Admin Verification</a></p>";
    } else {
        echo "<h2 style='color: red;'>❌ FIX INCOMPLETE</h2>";
        echo "<p>Some columns are still missing. Please check the errors above.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}
?>
