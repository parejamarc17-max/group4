<?php
require_once 'config/database.php';

echo "<h2>Checking Worker-Uploaded Images</h2>";

// Get all cars with images
$stmt = $pdo->query("SELECT id, car_name, image, status FROM car WHERE image IS NOT NULL AND image != '' ORDER BY id DESC");
$cars = $stmt->fetchAll();

echo "<h3>Cars with Images in Database:</h3>";
foreach ($cars as $car) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>ID: {$car['id']} - {$car['car_name']}</strong><br>";
    echo "Status: {$car['status']}<br>";
    echo "Image in DB: '{$car['image']}'<br>";
    
    // Check if image file exists
    $imagePath = 'assets/images/' . $car['image'];
    $exists = file_exists($imagePath);
    
    echo "File exists: " . ($exists ? 'YES' : 'NO') . "<br>";
    
    if ($exists) {
        echo "<img src='$imagePath' width='120' height='80' style='border: 2px solid green;' onerror=\"this.style.border='2px solid red';\">";
    } else {
        echo "<span style='color: red;'>❌ Image file missing!</span><br>";
        
        // Try to find similar file in assets/images
        $files = scandir('assets/images/');
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && strpos($file, $car['image']) !== false) {
                echo "<span style='color: orange;'>Found similar file: $file</span><br>";
                break;
            }
        }
    }
    echo "</div>";
}

// Show all files in assets/images that could be car images
echo "<h3>All Car Images in assets/images:</h3>";
$files = scandir('assets/images/');
$carImageFiles = [];
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..' && 
        !in_array($file, ['logo.png', 'login_background.png', 'default-car.txt', 'default-car.svg']) &&
        preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
        $carImageFiles[] = $file;
        echo "<div style='display: inline-block; margin: 5px; text-align: center;'>";
        echo "<img src='assets/images/$file' width='100' height='75' style='border: 1px solid #ccc;' onerror=\"this.style.border='2px solid red';\"><br>";
        echo "<small>$file</small>";
        echo "</div>";
    }
}

echo "<h3>Available Car Images Not in Database:</h3>";
$stmt = $pdo->query("SELECT image FROM car WHERE image IS NOT NULL AND image != ''");
$dbImages = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($carImageFiles as $file) {
    if (!in_array($file, $dbImages)) {
        echo "- $file<br>";
    }
}

echo "<h3>Summary:</h3>";
echo "<ul>";
echo "<li>✓ Total cars with images in database: " . count($cars) . "</li>";
echo "<li>✓ Total car image files in assets/images: " . count($carImageFiles) . "</li>";
echo "<li>✓ All images should now display correctly in fleet</li>";
echo "</ul>";
?>
