<!DOCTYPE html>
<html>
<head>
    <title>Test Relative Paths</title>
</head>
<body>
    <h2>Testing Image Path Resolution</h2>
    
    <h3>Direct Path Tests:</h3>
    
    <!-- Test different path variations -->
    <div style="margin: 20px; padding: 10px; border: 1px solid #ccc;">
        <h4>Path: assets/images/default-car.svg</h4>
        <img src="assets/images/default-car.svg" width="100" height="75" style="border: 1px solid green;" onerror="this.style.border='2px solid red'; this.alt='FAILED';">
    </div>
    
    <div style="margin: 20px; padding: 10px; border: 1px solid #ccc;">
        <h4>Path: ./assets/images/default-car.svg</h4>
        <img src="./assets/images/default-car.svg" width="100" height="75" style="border: 1px solid green;" onerror="this.style.border='2px solid red'; this.alt='FAILED';">
    </div>
    
    <div style="margin: 20px; padding: 10px; border: 1px solid #ccc;">
        <h4>Path: /assets/images/default-car.svg</h4>
        <img src="/assets/images/default-car.svg" width="100" height="75" style="border: 1px solid green;" onerror="this.style.border='2px solid red'; this.alt='FAILED';">
    </div>
    
    <h3>Sample Car Images:</h3>
    <?php
    $files = glob('assets/images/*.{jpg,jpeg,png,gif,svg}', GLOB_BRACE);
    foreach (array_slice($files, 0, 3) as $file) {
        $filename = basename($file);
        echo "<div style='margin: 20px; padding: 10px; border: 1px solid #ccc;'>";
        echo "<h4>File: $filename</h4>";
        
        // Test different path formats
        echo "<div style='margin: 5px;'>";
        echo "assets/images/$filename: ";
        echo "<img src='assets/images/$filename' width='80' height='60' style='border: 1px solid green;' onerror=\"this.style.border='2px solid red'; this.alt='FAILED';\">";
        echo "</div>";
        
        echo "</div>";
    }
    ?>
    
    <h3>Current Working Directory Info:</h3>
    <p>Current Script: <?php echo __FILE__; ?></p>
    <p>Current Dir: <?php echo __DIR__; ?></p>
    <p>Web Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
    <p>Request URI: <?php echo $_SERVER['REQUEST_URI']; ?></p>
</body>
</html>
