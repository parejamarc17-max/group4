<?php
session_start();
require_once '../config/database.php';

echo "<h1>Rental Table Debug</h1>";

// Get table structure
try {
    $result = $pdo->query("SHOW COLUMNS FROM rentals");
    echo "<h2>Rentals Table Columns:</h2>";
    echo "<pre>";
    while ($col = $result->fetch(PDO::FETCH_ASSOC)) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}

// Try a sample insert with all columns
echo "<h2>Testing Insert Statements:</h2>";

// First, add a test user if doesn't exist
$_SESSION['user_id'] = 1; // Fake session for testing

$test_data = [
    'car_id' => 1,
    'customer_name' => 'Test Customer',
    'customer_email' => 'test@example.com',
    'customer_phone' => '1234567890',
    'pickup_date' => date('Y-m-d'),
    'return_date' => date('Y-m-d', strtotime('+3 days')),
    'total_days' => 3,
    'total_cost' => 300.00
];

// Test 1: All columns
echo "<p><strong>Test 1: Insert with all columns</strong></p>";
try {
    $stmt = $pdo->prepare("INSERT INTO rentals (user_id, car_id, customer_name, customer_email, customer_phone, rental_date, return_date, total_days, total_cost, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    $stmt->execute([$_SESSION['user_id'], $test_data['car_id'], $test_data['customer_name'], $test_data['customer_email'], $test_data['customer_phone'], $test_data['pickup_date'], $test_data['return_date'], $test_data['total_days'], $test_data['total_cost']]);
    echo "<p style='color: green;'>✓ Successfully inserted with all columns</p>";
} catch (Exception $e) {
    echo "<p style='color: orange;'>✗ Failed with all columns: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 2: Without user_id and customer_email
echo "<p><strong>Test 2: Insert without user_id and customer_email</strong></p>";
try {
    $stmt = $pdo->prepare("INSERT INTO rentals (car_id, customer_name, customer_phone, rental_date, return_date, total_days, total_cost, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
    $stmt->execute([$test_data['car_id'], $test_data['customer_name'], $test_data['customer_phone'], $test_data['pickup_date'], $test_data['return_date'], $test_data['total_days'], $test_data['total_cost']]);
    echo "<p style='color: green;'>✓ Successfully inserted without user_id and customer_email</p>";
} catch (Exception $e) {
    echo "<p style='color: orange;'>✗ Failed without user_id and customer_email: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 3: Minimal columns
echo "<p><strong>Test 3: Insert with minimal columns</strong></p>";
try {
    $stmt = $pdo->prepare("INSERT INTO rentals (car_id, customer_name, customer_phone, rental_date, return_date, total_cost, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    $stmt->execute([$test_data['car_id'], $test_data['customer_name'], $test_data['customer_phone'], $test_data['pickup_date'], $test_data['return_date'], $test_data['total_cost']]);
    echo "<p style='color: green;'>✓ Successfully inserted with minimal columns</p>";
} catch (Exception $e) {
    echo "<p style='color: orange;'>✗ Failed with minimal columns: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='../car.php'>Back to Cars</a></p>";
?>
