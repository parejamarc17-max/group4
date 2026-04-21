<?php
require_once 'config/database.php';

echo "<h2>Database Debug Information</h2>";

// Check if rentals table exists
echo "<h3>Checking rentals table structure:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE rentals");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Check if cars table exists
echo "<h3>Checking cars table structure:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE cars");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Check sample cars
echo "<h3>Cars in database:</h3>";
try {
    $stmt = $pdo->query("SELECT id, car_name, price_per_day, status FROM car LIMIT 5");
    $cars = $stmt->fetchAll();
    
    if (count($cars) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Price/Day</th><th>Status</th></tr>";
        foreach ($cars as $car) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($car['id']) . "</td>";
            echo "<td>" . htmlspecialchars($car['car_name']) . "</td>";
            echo "<td>" . htmlspecialchars($car['price_per_day']) . "</td>";
            echo "<td>" . htmlspecialchars($car['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No cars found in database</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Check sample rentals
echo "<h3>Rentals in database:</h3>";
try {
    $stmt = $pdo->query("SELECT * FROM rentals LIMIT 5");
    $rentals = $stmt->fetchAll();
    
    if (count($rentals) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>";
        foreach (array_keys($rentals[0]) as $key) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";
        foreach ($rentals as $rental) {
            echo "<tr>";
            foreach ($rental as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No rentals found in database</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='car.php'>Back to Cars</a></p>";
?>
