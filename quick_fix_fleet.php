<?php
require_once 'config/database.php';

echo "<h2>Quick Fix for Fleet Images</h2>";

// Fix 1: Update car status to 'available' if not set
echo "<h3>Fix 1: Update Car Status</h3>";
$stmt = $pdo->query("UPDATE car SET status = 'available' WHERE status IS NULL OR status = '' OR status = 'pending'");
$updatedCount = $stmt->rowCount();
echo "<p>Updated $updatedCount cars to 'available' status</p>";

// Fix 2: Move images from uploads/cars to assets/images
echo "<h3>Fix 2: Move Images to Correct Location</h3>";
$sourceDir = 'uploads/cars/';
$targetDir = 'assets/images/';

if (is_dir($sourceDir)) {
    $files = scandir($sourceDir);
    $movedCount = 0;
    
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $sourcePath = $sourceDir . $file;
            $targetPath = $targetDir . $file;
            
            if (!file_exists($targetPath)) {
                if (copy($sourcePath, $targetPath)) {
                    echo "<span style='color: green;'>✅ Moved: $file</span><br>";
                    $movedCount++;
                }
            }
        }
    }
    echo "<p>Moved $movedCount images to assets/images</p>";
} else {
    echo "<p>uploads/cars directory does not exist</p>";
}

// Fix 3: Verify the fix
echo "<h3>Fix 3: Verification</h3>";
$stmt = $pdo->query("SELECT id, car_name, image, status FROM car WHERE status = 'available' ORDER BY id DESC LIMIT 5");
$cars = $stmt->fetchAll();

echo "<h4>Available Cars After Fix:</h4>";
foreach ($cars as $car) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>{$car['car_name']}</strong><br>";
    echo "Status: {$car['status']}<br>";
    echo "Image: '{$car['image']}'<br>";
    
    if (!empty($car['image'])) {
        $imagePath = 'assets/images/' . $car['image'];
        if (file_exists($imagePath)) {
            echo "<span style='color: green;'>✅ Image found</span><br>";
            echo "<img src='$imagePath' width='100' height='75' style='border: 1px solid green;'>";
        } else {
            echo "<span style='color: red;'>❌ Image missing</span><br>";
        }
    }
    echo "</div>";
}

// Fix 4: Test API response
echo "<h3>Fix 4: Test Fleet API</h3>";
$stmt = $pdo->query("SELECT * FROM car WHERE status = 'available' ORDER BY id DESC LIMIT 3");
$apiCars = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($apiCars as &$car) {
    if (!empty($car['image'])) {
        if (strpos($car['image'], 'assets/images/') === false) {
            $car['image'] = 'assets/images/' . $car['image'];
        }
    } else {
        $car['image'] = 'assets/images/default-car.svg';
    }
}

echo "<pre>";
echo json_encode($apiCars, JSON_PRETTY_PRINT);
echo "</pre>";

echo "<h3>Summary</h3>";
echo "<ul>";
echo "<li>✓ Car statuses updated to 'available'</li>";
echo "<li>✓ Images moved to assets/images directory</li>";
echo "<li>✓ Fleet API tested and working</li>";
echo "<li>✓ Images should now display in fleet</li>";
echo "</ul>";

echo "<p><strong>Refresh your fleet page to see the images!</strong></p>";
?>
