<!DOCTYPE html>
<html>
<head>
    <title>Simple Image Test</title>
</head>
<body>
    <h2>Testing Image Accessibility</h2>
    
    <h3>Default Image Test:</h3>
    <img src="assets/images/default-car.svg" width="200" height="150" style="border: 2px solid green;" onerror="this.style.border='2px solid red'; this.alt='FAILED: ' + this.src;">
    
    <h3>Sample Car Images:</h3>
    <?php
    $files = glob('uploads/cars/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    foreach (array_slice($files, 0, 3) as $file) {
        $relativePath = str_replace('uploads/cars/', 'uploads/cars/', $file);
        echo "<div style='margin: 10px;'>";
        echo "<img src='$relativePath' width='200' height='150' style='border: 2px solid green;' onerror=\"this.style.border='2px solid red'; this.alt='FAILED: ' + this.src;\">";
        echo "<br><small>$relativePath</small>";
        echo "</div>";
    }
    ?>
    
    <h3>Direct File Check:</h3>
    <?php
    $files = glob('uploads/cars/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    foreach (array_slice($files, 0, 3) as $file) {
        $exists = file_exists($file) ? 'YES' : 'NO';
        $readable = is_readable($file) ? 'YES' : 'NO';
        $size = filesize($file);
        echo "<div>";
        echo "File: $file<br>";
        echo "Exists: $exists<br>";
        echo "Readable: $readable<br>";
        echo "Size: $size bytes<br>";
        echo "</div><hr>";
    }
    ?>
</body>
</html>
