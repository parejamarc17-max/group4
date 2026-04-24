<?php
require_once 'config/database.php';

echo "<h2>Investigating Worker-Uploaded Car Images</h2>";

// Check all cars in database
echo "<h3>All Cars in Database:</h3>";
$stmt = $pdo->query("SELECT id, car_name, image, status, created_at FROM car ORDER BY id DESC");
$cars = $stmt->fetchAll();

foreach ($cars as $car) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>ID: {$car['id']} - {$car['car_name']}</strong><br>";
    echo "Status: {$car['status']}<br>";
    echo "Image in DB: '{$car['image']}'<br>";
    echo "Created: {$car['created_at']}<br>";
    
    // Check if image exists in assets/images
    if (!empty($car['image'])) {
        $imagePath = 'assets/images/' . $car['image'];
        $exists = file_exists($imagePath) ? 'YES' : 'NO';
        echo "File exists in assets/images: $exists<br>";
        
        if ($exists) {
            echo "<img src='$imagePath' width='100' height='75' style='border: 1px solid green;' onerror=\"this.style.border='2px solid red';\">";
        }
    } else {
        echo "<span style='color: red;'>No image in database</span><br>";
    }
    echo "</div>";
}

// Check what files are actually in assets/images
echo "<h3>Files in assets/images directory:</h3>";
$files = scandir('assets/images/');
$imageFiles = [];
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..' && !in_array($file, ['logo.png', 'login_background.png', 'default-car.txt', 'default-car.svg'])) {
        $imageFiles[] = $file;
        echo "- $file<br>";
    }
}

// Check uploads/cars directory
echo "<h3>Files in uploads/cars directory:</h3>";
if (is_dir('uploads/cars/')) {
    $uploadFiles = scandir('uploads/cars/');
    foreach ($uploadFiles as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "- $file<br>";
        }
    }
} else {
    echo "uploads/cars directory does not exist<br>";
}

// Test the exact API call that fleet uses
echo "<h3>API Test (same as fleet):</h3>";
$stmt = $pdo->query("SELECT * FROM car WHERE status = 'available' ORDER BY id DESC LIMIT 8");
$apiCars = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h4>Raw API Results:</h4>";
foreach ($apiCars as $car) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ddd;'>";
    echo "<strong>{$car['car_name']}</strong><br>";
    echo "Raw image: '{$car['image']}'<br>";
    
    // Apply the same path logic as get_cars.php
    if (!empty($car['image'])) {
        if (strpos($car['image'], 'assets/images/') === false) {
            $finalPath = 'assets/images/' . $car['image'];
        } else {
            $finalPath = $car['image'];
        }
    } else {
        $finalPath = 'assets/images/default-car.svg';
    }
    
    echo "Final path: '$finalPath'<br>";
    echo "File exists: " . (file_exists($finalPath) ? 'YES' : 'NO') . "<br>";
    echo "<img src='$finalPath' width='80' height='60' style='border: 1px solid #ccc;' onerror=\"this.style.border='2px solid red'; this.alt='ERROR';\">";
    echo "</div>";
}

echo "<h3>Potential Issues Found:</h3>";
echo "<ul>";
echo "<li>✓ Check if worker uploaded images are stored in database</li>";
echo "<li>✓ Check if images exist in assets/images directory</li>";
echo "<li>✓ Check if images are still in uploads/cars directory</li>";
echo "<li>✓ Check if car status is 'available'</li>";
echo "</ul>";
?>
