<?php
require_once 'config/database.php';

echo "<h1>Car Database Updater</h1>";
echo "<p>This script adds brand, model, year, and description to existing cars.</p>";

if (isset($_GET['update'])) {
    echo "<h2>Updating cars...</h2>";
    
    // Default car data by name
    $carDefaults = [
        'Toyota Camry' => ['brand' => 'Toyota', 'model' => 'Camry', 'year' => 2023, 'description' => 'Comfortable sedan for families'],
        'Honda Civic' => ['brand' => 'Honda', 'model' => 'Civic', 'year' => 2023, 'description' => 'Reliable and fuel-efficient'],
        'Ford Mustang' => ['brand' => 'Ford', 'model' => 'Mustang', 'year' => 2023, 'description' => 'Classic sports car'],
        'Tesla Model 3' => ['brand' => 'Tesla', 'model' => 'Model 3', 'year' => 2023, 'description' => 'Electric luxury vehicle'],
        'BMW 3 Series' => ['brand' => 'BMW', 'model' => '3 Series', 'year' => 2023, 'description' => 'Luxury sedan'],
        'Mercedes-Benz C-Class' => ['brand' => 'Mercedes-Benz', 'model' => 'C-Class', 'year' => 2023, 'description' => 'Premium luxury vehicle'],
        'Audi A4' => ['brand' => 'Audi', 'model' => 'A4', 'year' => 2023, 'description' => 'Sophisticated sedan'],
        'Mazda CX-5' => ['brand' => 'Mazda', 'model' => 'CX-5', 'year' => 2023, 'description' => 'Compact SUV with great handling'],
    ];
    
    try {
        // Get all cars
        $stmt = $pdo->query("SELECT id, car_name FROM car");
        $cars = $stmt->fetchAll();
        
        $updated = 0;
        foreach ($cars as $car) {
            $carName = $car['car_name'];
            
            // Find matching default or extract from name
            if (isset($carDefaults[$carName])) {
                $data = $carDefaults[$carName];
                $brand = $data['brand'];
                $model = $data['model'];
                $year = $data['year'];
                $description = $data['description'];
            } else {
                // Try to extract brand and model from car name
                $parts = explode(' ', trim($carName));
                $brand = $parts[0] ?? '';
                $model = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
                $year = 2023;
                $description = 'Premium car rental';
            }
            
            // Update car
            $updateStmt = $pdo->prepare("UPDATE car SET brand = ?, model = ?, year = ?, description = ? WHERE id = ?");
            $updateStmt->execute([$brand, $model, $year, $description, $car['id']]);
            $updated++;
            
            echo "<p>✓ Updated: <strong>$carName</strong> (Brand: $brand, Model: $model, Year: $year)</p>";
        }
        
        echo "<p style='color: green; font-weight: bold; margin-top: 20px;'>✓ Successfully updated $updated cars!</p>";
        echo "<p><a href='car.php' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Cars Page →</a></p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    // Show current car data
    try {
        $stmt = $pdo->query("SELECT * FROM car ORDER BY id DESC");
        $cars = $stmt->fetchAll();
        
        echo "<h2>Current Cars in Database:</h2>";
        echo "<table border='1' cellpadding='10' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Car Name</th>";
        echo "<th>Brand</th>";
        echo "<th>Model</th>";
        echo "<th>Year</th>";
        echo "<th>Description</th>";
        echo "</tr>";
        
        foreach ($cars as $car) {
            $brand = $car['brand'] ?: '<span style="color: red;">EMPTY</span>';
            $model = $car['model'] ?: '<span style="color: red;">EMPTY</span>';
            $year = $car['year'] ?: '<span style="color: red;">EMPTY</span>';
            $desc = $car['description'] ?: '<span style="color: red;">EMPTY</span>';
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($car['id']) . "</td>";
            echo "<td>" . htmlspecialchars($car['car_name']) . "</td>";
            echo "<td>" . $brand . "</td>";
            echo "<td>" . $model . "</td>";
            echo "<td>" . $year . "</td>";
            echo "<td>" . $desc . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Check if any fields are empty
        $hasEmpty = false;
        foreach ($cars as $car) {
            if (!$car['brand'] || !$car['model'] || !$car['year']) {
                $hasEmpty = true;
                break;
            }
        }
        
        if ($hasEmpty) {
            echo "<p style='color: orange; font-weight: bold; margin-top: 20px;'>⚠️ Some cars have empty brand/model/year fields. Search won't work for those.</p>";
            echo "<p><a href='?update=1' style='padding: 10px 20px; background: #FF9800; color: white; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Click here to auto-populate empty fields →</a></p>";
        } else {
            echo "<p style='color: green; font-weight: bold;'>✓ All cars have brand, model, and year data!</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

echo "<hr>";
echo "<p><a href='car.php'>← Back to Cars</a></p>";
?>
