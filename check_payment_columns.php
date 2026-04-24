<?php
require_once 'config/database.php';

echo "<h2>Check Payment Table Columns</h2>";

// Check payment_requests table structure
echo "<h3>Payment Requests Table:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE payment_requests");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if verified_by column exists
    $hasVerifiedBy = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'verified_by') {
            $hasVerifiedBy = true;
            break;
        }
    }
    
    if (!$hasVerifiedBy) {
        echo "<p style='color: red;'><strong>❌ verified_by column missing from payment_requests table</strong></p>";
        echo "<p>Adding verified_by column...</p>";
        
        // Add verified_by column
        $sql = "ALTER TABLE payment_requests ADD COLUMN verified_by INT NULL";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Added verified_by column to payment_requests</p>";
    } else {
        echo "<p style='color: green;'>✅ verified_by column exists in payment_requests</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error checking payment_requests: " . $e->getMessage() . "</p>";
}

// Check payments table structure
echo "<h3>Payments Table:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE payments");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if verified_by column exists
    $hasVerifiedBy = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'verified_by') {
            $hasVerifiedBy = true;
            break;
        }
    }
    
    if (!$hasVerifiedBy) {
        echo "<p style='color: red;'><strong>❌ verified_by column missing from payments table</strong></p>";
        echo "<p>Adding verified_by column...</p>";
        
        // Add verified_by column
        $sql = "ALTER TABLE payments ADD COLUMN verified_by INT NULL";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Added verified_by column to payments</p>";
    } else {
        echo "<p style='color: green;'>✅ verified_by column exists in payments</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error checking payments: " . $e->getMessage() . "</p>";
}

// Check for verified_at column too
echo "<h3>Check for verified_at columns:</h3>";

try {
    $stmt = $pdo->query("DESCRIBE payment_requests");
    $columns = $stmt->fetchAll();
    $hasVerifiedAt = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'verified_at') {
            $hasVerifiedAt = true;
            break;
        }
    }
    
    if (!$hasVerifiedAt) {
        echo "<p style='color: orange;'>Adding verified_at column to payment_requests...</p>";
        $sql = "ALTER TABLE payment_requests ADD COLUMN verified_at TIMESTAMP NULL DEFAULT NULL";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Added verified_at column to payment_requests</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error adding verified_at: " . $e->getMessage() . "</p>";
}

echo "<h3>Summary:</h3>";
echo "<ul>";
echo "<li>✅ Checked payment_requests table structure</li>";
echo "<li>✅ Checked payments table structure</li>";
echo "<li>✅ Added missing verified_by columns if needed</li>";
echo "<li>✅ Added missing verified_at columns if needed</li>";
echo "<li>✅ Database should now support payment verification</li>";
echo "</ul>";

echo "<p><strong>Try payment verification again!</strong></p>";
?>
