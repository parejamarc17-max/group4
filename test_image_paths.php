<?php
require_once 'config/database.php';

echo "<h3>Testing Image Path Generation</h3>";

// Get sample cars
$stmt = $pdo->query("SELECT id, car_name, image FROM car WHERE status = 'available' LIMIT 3");
$cars = $stmt->fetchAll();

foreach ($cars as $car) {
    echo "<h4>Car: {$car['car_name']} (ID: {$car['id']})</h4>";
    echo "Database Image: '{$car['image']}'<br>";
    
    // Test the path logic from get_cars.php
    if (!empty($car['image'])) {
        if (strpos($car['image'], 'uploads/cars/') === false) {
            $finalPath = 'uploads/cars/' . $car['image'];
        } else {
            $finalPath = $car['image'];
        }
    } else {
        $finalPath = 'assets/images/default-car.svg';
    }
    
    echo "Final Path: '$finalPath'<br>";
    echo "File Exists: " . (file_exists($finalPath) ? 'YES' : 'NO') . "<br>";
    
    // Test if it's accessible via web
    echo "<img src='$finalPath' width='150' height='100' style='border:1px solid #ccc;margin:5px;' onerror=\"this.style.border='2px solid red'; this.alt='ERROR: ' + this.src;\"><br>";
    echo "<hr>";
}

echo "<h3>Default Image Test:</h3>";
echo "<img src='assets/images/default-car.svg' width='150' height='100' style='border:1px solid #ccc;' onerror=\"this.style.border='2px solid red'; this.alt='ERROR: ' + this.src;\"><br>";
?>
