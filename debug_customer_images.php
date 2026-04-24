<?php
require_once 'config/database.php';

echo "<h2>Customer Car Images Debug</h2>";

// Get available cars
$stmt = $pdo->query("SELECT id, car_name, image FROM car WHERE status = 'available' ORDER BY id DESC LIMIT 5");
$cars = $stmt->fetchAll();

echo "<h3>Available Cars and Image Status:</h3>";

foreach ($cars as $car) {
    echo "<div style='margin: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 8px;'>";
    echo "<h4>{$car['car_name']} (ID: {$car['id']})</h4>";
    echo "<p><strong>Database Image:</strong> '{$car['image']}'</p>";
    
    // Determine correct image path
    $image_path = '';
    if (!empty($car['image'])) {
        if (strpos($car['image'], 'assets/images/') === false) {
            $image_path = 'assets/images/' . $car['image'];
        } else {
            $image_path = $car['image'];
        }
    } else {
        $image_path = 'assets/images/default-car.svg';
    }
    
    echo "<p><strong>Expected Path:</strong> $image_path</p>";
    
    // Check if file exists
    $full_path = __DIR__ . '/' . $image_path;
    if (file_exists($full_path)) {
        echo "<p style='color: green;'>✅ File exists: $full_path</p>";
        
        // Display the image
        echo "<img src='$image_path' width='150' height='100' style='border: 2px solid green; object-fit: cover;'>";
    } else {
        echo "<p style='color: red;'>❌ File NOT found: $full_path</p>";
        
        // Try default image
        $default_path = 'assets/images/default-car.svg';
        $default_full = __DIR__ . '/' . $default_path;
        if (file_exists($default_full)) {
            echo "<p style='color: orange;'>🔄 Using default: $default_path</p>";
            echo "<img src='$default_path' width='150' height='100' style='border: 2px solid orange;'>";
        } else {
            echo "<p style='color: red;'>❌ Default image also missing!</p>";
        }
    }
    
    echo "</div>";
}

echo "<h3>Customer Interface Image Tests:</h3>";

// Test customer dashboard path logic
echo "<h4>Customer Dashboard Path Logic:</h4>";
foreach ($cars as $car) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>{$car['car_name']}:</strong><br>";
    
    // Simulate customer dashboard logic
    $image_path = '';
    if (!empty($car['image'])) {
        if (strpos($car['image'], 'assets/images/') === false) {
            $image_path = '../assets/images/' . $car['image'];
        } else {
            $image_path = '../' . $car['image'];
        }
    } else {
        $image_path = '../assets/images/default-car.svg';
    }
    
    echo "Customer path: $image_path<br>";
    
    // Check if this path works from customer directory
    $customer_path = __DIR__ . '/customer/' . str_replace('../', '', $image_path);
    if (file_exists($customer_path)) {
        echo "<span style='color: green;'>✅ Path works from customer directory</span>";
    } else {
        echo "<span style='color: red;'>❌ Path broken from customer directory</span>";
    }
    
    echo "</div>";
}

echo "<h3>Directory Structure Check:</h3>";
$dirs_to_check = [
    'assets/images/',
    'customer/',
    'assets/images/default-car.svg'
];

foreach ($dirs_to_check as $path) {
    $full_path = __DIR__ . '/' . $path;
    if (file_exists($full_path)) {
        if (is_dir($full_path)) {
            echo "<p style='color: green;'>✅ Directory exists: $path</p>";
            
            // List some files in the directory
            if ($path === 'assets/images/') {
                $files = scandir($full_path);
                $image_files = array_filter($files, function($file) use ($full_path) {
                    return !in_array($file, ['.', '..']) && 
                           in_array(pathinfo($full_path . $file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'svg', 'webp']);
                });
                
                echo "<p>Images found: " . count($image_files) . "</p>";
                foreach (array_slice($image_files, 0, 5) as $file) {
                    echo "<small>• $file</small><br>";
                }
                if (count($image_files) > 5) {
                    echo "<small>... and " . (count($image_files) - 5) . " more</small>";
                }
            }
        } else {
            echo "<p style='color: green;'>✅ File exists: $path</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Missing: $path</p>";
    }
}

echo "<h3>Recommended Fixes:</h3>";
echo "<ul>";
echo "<li>✅ Fixed customer dashboard image path logic</li>";
echo "<li>✅ Fixed customer car.php image path logic</li>";
echo "<li>✅ Added proper fallback to default-car.svg</li>";
echo "<li>✅ Added onerror fallback for broken images</li>";
echo "</ul>";

echo "<p><strong>Test the customer interface:</strong></p>";
echo "<ul>";
echo "<li><a href='customer/dashboard.php'>Customer Dashboard</a></li>";
echo "<li><a href='customer/car.php'>Browse Cars</a></li>";
echo "</ul>";
?>
