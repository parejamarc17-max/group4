<?php
require_once 'config/database.php';

header('Content-Type: application/json');

// Simulate the same query as get_cars.php
$stmt = $pdo->query("SELECT * FROM car WHERE status = 'available' ORDER BY id DESC LIMIT 8");
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Raw Database Results:</h3>";
echo "<pre>";
print_r($cars);
echo "</pre>";

// Apply the same formatting logic
foreach ($cars as &$car) {
    if (!empty($car['image'])) {
        // Check if image path already includes uploads/cars/
        if (strpos($car['image'], 'uploads/cars/') === false) {
            $car['image'] = 'uploads/cars/' . $car['image'];
        }
    } else {
        $car['image'] = 'assets/images/default-car.svg';
    }
}

echo "<h3>After Path Formatting:</h3>";
echo "<pre>";
print_r($cars);
echo "</pre>";

echo "<h3>Checking if files exist:</h3>";
foreach ($cars as $car) {
    $imagePath = $car['image'];
    $fullPath = $imagePath;
    $exists = file_exists($fullPath) ? 'YES' : 'NO';
    echo "Car ID {$car['id']}: $imagePath - Exists: $exists<br>";
    
    if ($exists === 'YES') {
        echo "<img src='$imagePath' width='100' height='75' style='border:1px solid #ccc;margin:5px;'>";
    }
}
?>
