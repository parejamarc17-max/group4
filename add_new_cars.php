<?php
require_once 'config/database.php';

echo "<h2>Adding New Cars from Assets Folder</h2>";

// Car images from assets folder (excluding non-car images)
$car_images = [
    '1777003333_69eaeb457d014.jpg' => [
        'car_name' => 'Toyota Camry 2023',
        'brand' => 'Toyota',
        'model' => 'Camry',
        'year' => 2023,
        'price_per_day' => 2500,
        'transmission' => 'Automatic',
        'fuel_type' => 'Gasoline',
        'seating_capacity' => 5,
        'category' => 'Sedan',
        'description' => 'Comfortable and reliable sedan perfect for city driving and long trips. Features modern safety technology and excellent fuel economy.'
    ],
    '1777003840_69eaed40b3ca5.jpg' => [
        'car_name' => 'Honda CR-V 2023',
        'brand' => 'Honda',
        'model' => 'CR-V',
        'year' => 2023,
        'price_per_day' => 3200,
        'transmission' => 'Automatic',
        'fuel_type' => 'Gasoline',
        'seating_capacity' => 7,
        'category' => 'SUV',
        'description' => 'Spacious SUV perfect for families. Advanced safety features, comfortable interior, and excellent performance for both city and highway driving.'
    ],
    '1777011014_69eb094605daa.jpg' => [
        'car_name' => 'Ford Mustang 2022',
        'brand' => 'Ford',
        'model' => 'Mustang',
        'year' => 2022,
        'price_per_day' => 4500,
        'transmission' => 'Manual',
        'fuel_type' => 'Gasoline',
        'seating_capacity' => 4,
        'category' => 'Sports',
        'description' => 'Iconic sports car with powerful performance and stunning design. Perfect for enthusiasts who want an exciting driving experience.'
    ],
    'cdb01d20b2b15e4152cfa2b82cd6fb01.jpg' => [
        'car_name' => 'Mitsubishi Montero 2023',
        'brand' => 'Mitsubishi',
        'model' => 'Montero',
        'year' => 2023,
        'price_per_day' => 3800,
        'transmission' => 'Automatic',
        'fuel_type' => 'Diesel',
        'seating_capacity' => 7,
        'category' => 'SUV',
        'description' => 'Rugged SUV designed for adventure. Excellent off-road capabilities with premium comfort and advanced safety features.'
    ],
    'd0f4687fcffd30cdd82321dd957cec53.jpg' => [
        'car_name' => 'Nissan Altima 2023',
        'brand' => 'Nissan',
        'model' => 'Altima',
        'year' => 2023,
        'price_per_day' => 2200,
        'transmission' => 'Automatic',
        'fuel_type' => 'Gasoline',
        'seating_capacity' => 5,
        'category' => 'Sedan',
        'description' => 'Efficient sedan with advanced safety features and comfortable interior. Great for daily commuting and business trips.'
    ],
    'download (3).jpg' => [
        'car_name' => 'Chevrolet Tahoe 2022',
        'brand' => 'Chevrolet',
        'model' => 'Tahoe',
        'year' => 2022,
        'price_per_day' => 4200,
        'transmission' => 'Automatic',
        'fuel_type' => 'Gasoline',
        'seating_capacity' => 8,
        'category' => 'SUV',
        'description' => 'Full-size SUV with exceptional space and capability. Perfect for large groups and families with premium comfort.'
    ],
    'download (4).jpg' => [
        'car_name' => 'BMW 3 Series 2023',
        'brand' => 'BMW',
        'model' => '3 Series',
        'year' => 2023,
        'price_per_day' => 5500,
        'transmission' => 'Automatic',
        'fuel_type' => 'Gasoline',
        'seating_capacity' => 5,
        'category' => 'Luxury',
        'description' => 'Premium luxury sedan with cutting-edge technology and exceptional performance. Experience ultimate comfort and style.'
    ],
    'f828a23bfed13d4f79c0b34d9b6cf8a1.jpg' => [
        'car_name' => 'Hyundai Tucson 2023',
        'brand' => 'Hyundai',
        'model' => 'Tucson',
        'year' => 2023,
        'price_per_day' => 2800,
        'transmission' => 'Automatic',
        'fuel_type' => 'Gasoline',
        'seating_capacity' => 5,
        'category' => 'SUV',
        'description' => 'Modern compact SUV with stylish design and advanced features. Perfect for urban adventures with excellent fuel efficiency.'
    ],
    'car-png-39073.png' => [
        'car_name' => 'Volkswagen Golf 2022',
        'brand' => 'Volkswagen',
        'model' => 'Golf',
        'year' => 2022,
        'price_per_day' => 2000,
        'transmission' => 'Manual',
        'fuel_type' => 'Gasoline',
        'seating_capacity' => 5,
        'category' => 'Hatchback',
        'description' => 'Versatile hatchback with European engineering. Perfect for city driving with excellent handling and fuel economy.'
    ],
    'pngegg (1).png' => [
        'car_name' => 'Mercedes-Benz C-Class 2023',
        'brand' => 'Mercedes-Benz',
        'model' => 'C-Class',
        'year' => 2023,
        'price_per_day' => 6000,
        'transmission' => 'Automatic',
        'fuel_type' => 'Gasoline',
        'seating_capacity' => 5,
        'category' => 'Luxury',
        'description' => 'Luxury sedan with premium features and exceptional comfort. Experience German engineering at its finest.'
    ],
    'pngegg.png' => [
        'car_name' => 'Mazda CX-5 2023',
        'brand' => 'Mazda',
        'model' => 'CX-5',
        'year' => 2023,
        'price_per_day' => 3000,
        'transmission' => 'Automatic',
        'fuel_type' => 'Gasoline',
        'seating_capacity' => 5,
        'category' => 'SUV',
        'description' => 'Stylish compact SUV with premium interior and advanced safety features. Perfect blend of comfort and performance.'
    ]
];

$added_count = 0;
$skipped_count = 0;

echo "<h3>Processing Car Images:</h3>";

foreach ($car_images as $image_file => $car_data) {
    try {
        // Check if car already exists
        $stmt = $pdo->prepare("SELECT id FROM car WHERE car_name = ? AND brand = ? AND model = ?");
        $stmt->execute([$car_data['car_name'], $car_data['brand'], $car_data['model']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            echo "<p style='color: orange;'>⚠️ Car already exists: {$car_data['car_name']} - Skipping</p>";
            $skipped_count++;
            continue;
        }
        
        // Insert new car
        $stmt = $pdo->prepare("INSERT INTO car (car_name, brand, model, year, price_per_day, transmission, fuel_type, seating_capacity, category, description, image, status, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', NOW())");
        
        $image_path = 'assets/images/' . $image_file;
        
        $stmt->execute([
            $car_data['car_name'],
            $car_data['brand'],
            $car_data['model'],
            $car_data['year'],
            $car_data['price_per_day'],
            $car_data['transmission'],
            $car_data['fuel_type'],
            $car_data['seating_capacity'],
            $car_data['category'],
            $car_data['description'],
            $image_path
        ]);
        
        $car_id = $pdo->lastInsertId();
        echo "<p style='color: green;'>✅ Added: {$car_data['car_name']} (ID: $car_id)</p>";
        $added_count++;
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error adding {$car_data['car_name']}: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>Summary:</h3>";
echo "<p><strong>✅ Cars Added:</strong> $added_count</p>";
echo "<p><strong>⚠️ Cars Skipped:</strong> $skipped_count</p>";
echo "<p><strong>📊 Total Processed:</strong> " . ($added_count + $skipped_count) . "</p>";

// Show current car inventory
echo "<h3>Current Car Inventory:</h3>";
try {
    $stmt = $pdo->query("SELECT car_name, brand, model, year, price_per_day, category, status FROM car ORDER BY created_at DESC LIMIT 15");
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($cars) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Car Name</th><th>Brand/Model</th><th>Year</th><th>Price/Day</th><th>Category</th><th>Status</th></tr>";
        
        foreach ($cars as $car) {
            $status_color = $car['status'] === 'available' ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$car['car_name']}</td>";
            echo "<td>{$car['brand']} {$car['model']}</td>";
            echo "<td>{$car['year']}</td>";
            echo "<td>₱" . number_format($car['price_per_day'], 2) . "</td>";
            echo "<td>{$car['category']}</td>";
            echo "<td style='color: $status_color; font-weight: bold;'>" . ucfirst($car['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No cars found in database.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error loading inventory: " . $e->getMessage() . "</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>✅ New cars added to database</li>";
echo "<li>🚗 Cars are now available for rent</li>";
echo "<li>👀 Cars will appear in Browse Cars section</li>";
echo "<li>👷 Workers can manage these cars in Manage Car section</li>";
echo "<li>📱 Customers can book these cars immediately</li>";
echo "</ol>";

echo "<p><a href='index.php#cars' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Cars on Website</a></p>";
echo "<p><a href='worker/manage_car.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Manage Cars (Worker)</a></p>";
?>
