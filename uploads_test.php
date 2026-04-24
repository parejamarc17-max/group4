<!DOCTYPE html>
<html>
<head>
    <title>Uploads Directory Test</title>
</head>
<body>
    <h2>Uploads Directory Test</h2>
    
    <?php
    echo "<h3>Directory Permissions Check:</h3>";
    $uploadsDir = 'uploads/cars/';
    
    echo "Directory: $uploadsDir<br>";
    echo "Exists: " . (is_dir($uploadsDir) ? 'YES' : 'NO') . "<br>";
    echo "Readable: " . (is_readable($uploadsDir) ? 'YES' : 'NO') . "<br>";
    echo "Writable: " . (is_writable($uploadsDir) ? 'YES' : 'NO') . "<br>";
    
    echo "<h3>Files in uploads/cars/:</h3>";
    $files = scandir($uploadsDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $fullPath = $uploadsDir . $file;
            $exists = file_exists($fullPath) ? 'YES' : 'NO';
            $readable = is_readable($fullPath) ? 'YES' : 'NO';
            $size = filesize($fullPath);
            $type = mime_content_type($fullPath);
            
            echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
            echo "<strong>$file</strong><br>";
            echo "Full Path: $fullPath<br>";
            echo "Exists: $exists | Readable: $readable<br>";
            echo "Size: $size bytes | Type: $type<br>";
            
            // Test web access
            echo "<img src='$fullPath' width='100' height='75' style='border: 1px solid #ccc;' onerror=\"this.style.border='2px solid red'; this.alt='WEB ACCESS FAILED';\">";
            echo "</div>";
        }
    }
    ?>
    
    <h3>Web Server Test:</h3>
    <p>If images above show red borders, there's a web server configuration issue.</p>
    
    <h3>Solution Checklist:</h3>
    <ul>
        <li>✓ Directory exists and is readable</li>
        <li>✓ Files exist and are readable</li>
        <li>❓ Web server can serve files from uploads/</li>
        <li>❓ .htaccess blocking uploads/ directory?</li>
    </ul>
</body>
</html>
