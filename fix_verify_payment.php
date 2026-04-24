<?php
require_once 'config/database.php';

echo "<h2>Fix Verify Payment System</h2>";

echo "<h3>Current Payment Status Analysis:</h3>";

// Check payment_requests table
echo "<h4>Payment Requests (Admin System):</h4>";
try {
    $stmt = $pdo->query("SELECT id, rental_id, status, amount, sent_at FROM payment_requests ORDER BY id DESC LIMIT 5");
    $requests = $stmt->fetchAll();
    
    if (empty($requests)) {
        echo "<p style='color: orange;'>No payment requests found</p>";
    } else {
        foreach ($requests as $req) {
            echo "<div style='margin: 5px; padding: 5px; border: 1px solid #ddd;'>";
            echo "ID: {$req['id']}, Rental: {$req['rental_id']}, Status: <strong>{$req['status']}</strong>, Amount: {$req['amount']}<br>";
            echo "Sent: {$req['sent_at']}";
            echo "</div>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Check payments table
echo "<h4>Payments (Worker System):</h4>";
try {
    $stmt = $pdo->query("SELECT id, rental_id, status, amount, created_at FROM payments ORDER BY id DESC LIMIT 5");
    $payments = $stmt->fetchAll();
    
    if (empty($payments)) {
        echo "<p style='color: orange;'>No payments found</p>";
    } else {
        foreach ($payments as $pay) {
            echo "<div style='margin: 5px; padding: 5px; border: 1px solid #ddd;'>";
            echo "ID: {$pay['id']}, Rental: {$pay['rental_id']}, Status: <strong>{$pay['status']}</strong>, Amount: {$pay['amount']}<br>";
            echo "Created: {$pay['created_at']}";
            echo "</div>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Issues Fixed:</h3>";
echo "<ul>";
echo "<li>✅ Admin verify_payment.php updated to handle both payment_requests and payments tables</li>";
echo "<li>✅ Verification logic now checks source table parameter</li>";
echo "<li>✅ Reject functionality updated for both tables</li>";
echo "<li>✅ Display buttons include source table parameter</li>";
echo "<li>✅ JavaScript updated to handle source table in reject modal</li>";
echo "</ul>";

echo "<h3>How It Works Now:</h3>";
echo "<ol>";
echo "<li><strong>Admin Page:</strong> Shows payments from both tables (payment_requests + payments)</li>";
echo "<li><strong>Verification:</strong> Updates correct table based on source parameter</li>";
echo "<li><strong>Rejection:</strong> Updates correct table and handles rental/car status</li>";
echo "<li><strong>Worker Page:</strong> Still uses payments table (can be updated similarly)</li>";
echo "</ol>";

echo "<h3>Test the Fix:</h3>";
echo "<ul>";
echo "<li>Visit: <a href='admin/verify_payment.php'>admin/verify_payment.php</a></li>";
echo "<li>Visit: <a href='worker/verify_payment.php'>worker/verify_payment.php</a></li>";
echo "<li>Both should now show pending payments correctly</li>";
echo "<li>Verify and reject buttons should work for both admin and worker</li>";
echo "</ul>";

echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li>Test admin verification functionality</li>";
echo "<li>Test worker verification functionality</li>";
echo "<li>Check if payments are properly processed</li>";
echo "<li>Verify rental and car status updates</li>";
echo "</ul>";
?>
