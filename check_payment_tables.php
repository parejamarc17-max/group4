<?php
require_once 'config/database.php';

echo "<h2>Payment Tables Analysis</h2>";

// Check payment_requests table
echo "<h3>Payment Requests Table (Admin uses):</h3>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payment_requests");
    $count = $stmt->fetch();
    echo "Total records: " . $count['count'] . "<br>";
    
    if ($count['count'] > 0) {
        $stmt = $pdo->query("SELECT id, rental_id, status, amount, sent_at FROM payment_requests ORDER BY id DESC LIMIT 5");
        $requests = $stmt->fetchAll();
        
        echo "<table border='1' style='width: 100%; margin-top: 10px;'>";
        echo "<tr><th>ID</th><th>Rental ID</th><th>Status</th><th>Amount</th><th>Sent At</th></tr>";
        foreach ($requests as $req) {
            echo "<tr>";
            echo "<td>{$req['id']}</td>";
            echo "<td>{$req['rental_id']}</td>";
            echo "<td>{$req['status']}</td>";
            echo "<td>{$req['amount']}</td>";
            echo "<td>{$req['sent_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<span style='color: red;'>Error: " . $e->getMessage() . "</span><br>";
}

// Check payments table
echo "<h3>Payments Table (Worker uses):</h3>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payments");
    $count = $stmt->fetch();
    echo "Total records: " . $count['count'] . "<br>";
    
    if ($count['count'] > 0) {
        $stmt = $pdo->query("SELECT id, rental_id, status, amount, created_at, verified_at FROM payments ORDER BY id DESC LIMIT 5");
        $payments = $stmt->fetchAll();
        
        echo "<table border='1' style='width: 100%; margin-top: 10px;'>";
        echo "<tr><th>ID</th><th>Rental ID</th><th>Status</th><th>Amount</th><th>Created</th><th>Verified</th></tr>";
        foreach ($payments as $pay) {
            echo "<tr>";
            echo "<td>{$pay['id']}</td>";
            echo "<td>{$pay['rental_id']}</td>";
            echo "<td>{$pay['status']}</td>";
            echo "<td>{$pay['amount']}</td>";
            echo "<td>{$pay['created_at']}</td>";
            echo "<td>{$pay['verified_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<span style='color: red;'>Error: " . $e->getMessage() . "</span><br>";
}

// Check rentals table for payment_status
echo "<h3>Rentals Table Payment Status:</h3>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM rentals WHERE payment_status IS NOT NULL");
    $count = $stmt->fetch();
    echo "Rentals with payment status: " . $count['count'] . "<br>";
    
    if ($count['count'] > 0) {
        $stmt = $pdo->query("SELECT id, payment_status, total_cost FROM rentals WHERE payment_status IS NOT NULL ORDER BY id DESC LIMIT 5");
        $rentals = $stmt->fetchAll();
        
        echo "<table border='1' style='width: 100%; margin-top: 10px;'>";
        echo "<tr><th>Rental ID</th><th>Payment Status</th><th>Total Cost</th></tr>";
        foreach ($rentals as $rental) {
            echo "<tr>";
            echo "<td>{$rental['id']}</td>";
            echo "<td>{$rental['payment_status']}</td>";
            echo "<td>{$rental['total_cost']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<span style='color: red;'>Error: " . $e->getMessage() . "</span><br>";
}

echo "<h3>Issue Analysis:</h3>";
echo "<ul>";
echo "<li><strong>Admin uses:</strong> payment_requests table (status = 'paid' needs verification)</li>";
echo "<li><strong>Worker uses:</strong> payments table (status = 'pending' needs verification)</li>";
echo "<li><strong>Problem:</strong> Two different systems for the same functionality</li>";
echo "<li><strong>Solution:</strong> Need to unify the verification process</li>";
echo "</ul>";
?>
