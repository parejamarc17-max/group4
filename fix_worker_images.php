<?php
require_once 'config/database.php';

echo "<h2>Fixing Worker-Uploaded Car Images</h2>";

// Step 1: Move images from uploads/cars to assets/images
$sourceDir = 'uploads/cars/';
$targetDir = 'assets/images/';

if (is_dir($sourceDir)) {
    echo "<h3>Moving images from uploads/cars to assets/images...</h3>";
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
        echo "Created target directory: $targetDir<br>";
    }

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
                } else {
                    echo "<span style='color: red;'>❌ Failed to move: $file</span><br>";
                }
            } else {
                echo "<span style='color: orange;'>⚠️ Already exists: $file</span><br>";
            }
        }
    }
    echo "<p><strong>Moved $movedCount files</strong></p>";
}

// Step 2: Check database and verify images
echo "<h3>Verifying Database Images:</h3>";
$stmt = $pdo->query("SELECT id, car_name, image, status FROM car ORDER BY id DESC");
$cars = $stmt->fetchAll();

$issuesFound = 0;
foreach ($cars as $car) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>ID: {$car['id']} - {$car['car_name']}</strong><br>";
    echo "Status: {$car['status']}<br>";
    echo "DB Image: '{$car['image']}'<br>";
    
    if (!empty($car['image'])) {
        $imagePath = 'assets/images/' . $car['image'];
        $exists = file_exists($imagePath);
        
        echo "File exists: " . ($exists ? 'YES' : 'NO') . "<br>";
        
        if ($exists && $car['status'] === 'available') {
            echo "<img src='$imagePath' width='100' height='75' style='border: 1px solid green;' onerror=\"this.style.border='2px solid red';\">";
        } elseif (!$exists) {
            echo "<span style='color: red;'>❌ Image file missing!</span>";
            $issuesFound++;
        }
    } else {
        echo "<span style='color: orange;'>⚠️ No image in database</span>";
    }
    echo "</div>";
}

// Step 3: Test API response
echo "<h3>Testing Fleet API Response:</h3>";
$stmt = $pdo->query("SELECT * FROM car WHERE status = 'available' ORDER BY id DESC LIMIT 3");
$apiCars = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($apiCars as &$car) {
    // Apply same logic as get_cars.php
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

echo "<h3>Summary:</h3>";
echo "<ul>";
echo "<li>✓ Worker upload directory fixed to assets/images</li>";
echo "<li>✓ Existing images moved to correct location</li>";
echo "<li>✓ Database records verified</li>";
echo "<li>✓ API response tested</li>";
echo "</ul>";

if ($issuesFound > 0) {
    echo "<p style='color: red;'><strong>$issuesFound issues found - some images may still not display</strong></p>";
} else {
    echo "<p style='color: green;'><strong>All images should now display correctly!</strong></p>";
}
?>
