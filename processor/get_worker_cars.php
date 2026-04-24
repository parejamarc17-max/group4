<?php
require_once 'config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$category = $_GET['category'] ?? 'all';

try {
    // Build query based on category
    $query = "SELECT * FROM car WHERE status = 'available'";
    $params = [];
    
    if ($category !== 'all') {
        $query .= " AND category = ?";
        $params[] = $category;
    }
    
    $query .= " ORDER BY created_at DESC LIMIT 12";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format image paths and ensure all required fields
    foreach ($cars as &$car) {
        // Format image path
        if (!empty($car['image'])) {
            if (strpos($car['image'], 'assets/images/') === false) {
                $car['image'] = 'assets/images/' . $car['image'];
            }
        } else {
            $car['image'] = 'assets/images/default-car.svg';
        }
        
        // Ensure numeric values are properly formatted
        $car['price_per_day'] = floatval($car['price_per_day']);
        $car['seating_capacity'] = intval($car['seating_capacity']);
        $car['year'] = intval($car['year']);
        
        // Ensure text fields are not null
        $car['transmission'] = $car['transmission'] ?? 'Automatic';
        $car['fuel_type'] = $car['fuel_type'] ?? 'Gasoline';
        $car['category'] = $car['category'] ?? 'economy';
        $car['description'] = $car['description'] ?? 'Quality rental car';
    }
    
    echo json_encode($cars);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
