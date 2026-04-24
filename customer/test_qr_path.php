<?php
echo "<h2>QR Code Path Test</h2>";

// Test different image paths
$paths = [
    "assets/images/QR_code_payment.jpg",
    "../assets/images/QR_code_payment.jpg",
    "/assets/images/QR_code_payment.jpg"
];

echo "<h3>Testing QR Code Image Paths:</h3>";

foreach ($paths as $path) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>Path:</strong> $path<br>";
    
    // Check if file exists relative to current directory
    $file_path = __DIR__ . "/" . str_replace("../", "", $path);
    if (file_exists($file_path)) {
        echo "<span style='color: green;'>✅ File exists at: $file_path</span><br>";
        
        // Try to display the image
        echo "<img src='$path' width='100' height='100' style='border: 1px solid green;' onerror=\"this.style.border='1px solid red'; this.alt='Failed to load';\"><br>";
        echo "<span style='color: green;'>Image should display above</span>";
    } else {
        echo "<span style='color: red;'>❌ File not found at: $file_path</span>";
    }
    
    echo "</div>";
}

echo "<h3>Current Directory Info:</h3>";
echo "<strong>Current directory:</strong> " . __DIR__ . "<br>";
echo "<strong>File should be at:</strong> " . __DIR__ . "/../assets/images/QR_code_payment.jpg<br>";

// Check if the file exists with the correct path
$correct_path = __DIR__ . "/../assets/images/QR_code_payment.jpg";
if (file_exists($correct_path)) {
    echo "<span style='color: green;'><strong>✅ QR code file exists at correct location!</strong></span><br>";
    echo "<img src='../assets/images/QR_code_payment.jpg' width='200' height='200' style='border: 2px solid green;'><br>";
    echo "<span style='color: green;'>This is how it should appear in the payment page</span>";
} else {
    echo "<span style='color: red;'><strong>❌ QR code file not found!</strong></span>";
}

echo "<h3>Recommended Fix:</h3>";
echo "<p>Use the path: <code>../assets/images/QR_code_payment.jpg</code></p>";
echo "<p>This goes up one directory from customer/ to access the assets/ folder.</p>";
?>
