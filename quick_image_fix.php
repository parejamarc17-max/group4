<?php
require_once 'config/database.php';

echo "<h2>Quick Fleet Image Fix</h2>";

// Get all available cars
$stmt = $pdo->query("SELECT id, car_name, image FROM car WHERE status = 'available'");
$cars = $stmt->fetchAll();

// Available images (excluding system images)
$available_images = [
    '1777003333_69eaeb457d014.jpg',
    '1777003840_69eaed40b3ca5.jpg', 
    'car-png-39073.png',
    'cdb01d20b2b15e4152cfa2b82cd6fb01.jpg',
    'd0f4687fcffd30cdd82321dd957cec53.jpg',
    'download (3).jpg',
    'download (4).jpg',
    'f828a23bfed13d4f79c0b34d9b6cf8a1.jpg',
    'pngegg (1).png',
    'pngegg.png'
];

echo "<h3>Fixing Car Images:</h3>";

$image_index = 0;
$fixed_count = 0;

foreach ($cars as $car) {
    // Check if car needs image fix
    $needs_fix = false;
    
    if (empty($car['image'])) {
        $needs_fix = true;
        echo "<p>Car ID {$car['id']} has no image - assigning...</p>";
    } elseif (!file_exists('assets/images/' . $car['image'])) {
        $needs_fix = true;
        echo "<p>Car ID {$car['id']} has missing image - reassigning...</p>";
    }
    
    if ($needs_fix && $image_index < count($available_images)) {
        $new_image = $available_images[$image_index];
        
        // Update database
        $stmt = $pdo->prepare("UPDATE car SET image = ? WHERE id = ?");
        $stmt->execute([$new_image, $car['id']]);
        
        echo "<p style='color: green;'>✅ Fixed: {$car['car_name']} → $new_image</p>";
        
        $image_index++;
        $fixed_count++;
    }
}

echo "<h3>Summary:</h3>";
echo "<p>Fixed $fixed_count car images</p>";

// Verify the fix
echo "<h3>Verification:</h3>";
$stmt = $pdo->query("SELECT id, car_name, image FROM car WHERE status = 'available' ORDER BY id DESC LIMIT 5");
$test_cars = $stmt->fetchAll();

foreach ($test_cars as $car) {
    $image_path = 'assets/images/' . $car['image'];
    $exists = file_exists($image_path);
    
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>{$car['car_name']}</strong><br>";
    echo "Image: {$car['image']}<br>";
    echo "Status: " . ($exists ? '✅ FOUND' : '❌ MISSING') . "<br>";
    
    if ($exists) {
        echo "<img src='$image_path' width='120' height='90' style='border: 2px solid green;'>";
    }
    echo "</div>";
}

echo "<p><strong><a href='index.php#cars'>Test the Fleet Section</a></strong></p>";
?>
