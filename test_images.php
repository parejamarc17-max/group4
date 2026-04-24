<?php
require_once 'config/database.php';

echo "<h3>Checking Car Images in Database</h3>";

$stmt = $pdo->query('SELECT id, car_name, image FROM car LIMIT 5');
$cars = $stmt->fetchAll();

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Image Path</th><th>File Exists?</th></tr>";

foreach ($cars as $car) {
    $imagePath = $car['image'];
    $fullPath = 'uploads/cars/' . $imagePath;
    $exists = file_exists($fullPath) ? 'Yes' : 'No';
    
    echo "<tr>";
    echo "<td>{$car['id']}</td>";
    echo "<td>{$car['car_name']}</td>";
    echo "<td>{$imagePath}</td>";
    echo "<td>{$exists}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Files in uploads/cars/ directory:</h3>";
$files = scandir('uploads/cars/');
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo "- " . $file . "<br>";
    }
}
?>
