<?php
echo "<h2>Moving Images from uploads/cars to assets/images</h2>";

$sourceDir = 'uploads/cars/';
$targetDir = 'assets/images/';

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

echo "<h3>Summary:</h3>";
echo "Moved: $movedCount files<br>";
echo "Errors: $errorCount files<br>";

echo "<h3>Files now in assets/images:</h3>";
$newFiles = scandir($targetDir);
foreach ($newFiles as $file) {
    if ($file !== '.' && $file !== '..') {
        echo "- $file<br>";
    }
}

echo "<p><strong>Note:</strong> After moving images, you can delete the uploads/cars directory if no longer needed.</p>";
?>
