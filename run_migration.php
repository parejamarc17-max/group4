<?php
echo "<h2>Running Image Migration</h2>";

$sourceDir = 'uploads/cars/';
$targetDir = 'assets/images/';

// Check if source exists
if (!is_dir($sourceDir)) {
    echo "<p style='color: orange;'>Source directory $sourceDir does not exist. No migration needed.</p>";
} else {
    // Ensure target directory exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
        echo "Created target directory: $targetDir<br>";
    }

    // Get all files in source
    $files = scandir($sourceDir);
    $movedCount = 0;
    $errorCount = 0;

    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $sourcePath = $sourceDir . $file;
            $targetPath = $targetDir . $file;
            
            if (file_exists($targetPath)) {
                echo "<span style='color: orange;'>⚠️ File already exists: $file (skipped)</span><br>";
                continue;
            }
            
            if (copy($sourcePath, $targetPath)) {
                echo "<span style='color: green;'>✅ Moved: $file</span><br>";
                $movedCount++;
            } else {
                echo "<span style='color: red;'>❌ Failed to move: $file</span><br>";
                $errorCount++;
            }
        }
    }

    echo "<h3>Migration Summary:</h3>";
    echo "Moved: $movedCount files<br>";
    echo "Errors: $errorCount files<br>";
}

echo "<h3>Current files in assets/images:</h3>";
$files = scandir('assets/images/');
$carImages = [];
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..' && !in_array($file, ['logo.png', 'login_background.png', 'default-car.txt'])) {
        $carImages[] = $file;
        echo "- $file<br>";
    }
}

echo "<h3>Database Check:</h3>";
require_once 'config/database.php';
$stmt = $pdo->query("SELECT id, car_name, image FROM car WHERE status = 'available' LIMIT 5");
$cars = $stmt->fetchAll();

foreach ($cars as $car) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>{$car['car_name']}</strong><br>";
    echo "DB Image: '{$car['image']}'<br>";
    
    // Check if this image exists in assets/images
    $imageFile = $car['image'];
    if (in_array($imageFile, $carImages)) {
        echo "<span style='color: green;'>✅ Image found in assets/images</span><br>";
        echo "<img src='assets/images/$imageFile' width='100' height='75' style='border: 1px solid green;' onerror=\"this.style.border='2px solid red';\">";
    } else {
        echo "<span style='color: red;'>❌ Image NOT found in assets/images</span><br>";
    }
    echo "</div>";
}
?>
