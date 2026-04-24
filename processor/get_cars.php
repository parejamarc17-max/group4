<?php
require_once '../config/database.php';

// Set proper headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

try {
    $category = $_GET["category"] ?? "all";
    
    // Ensure we have some available cars
    $checkStmt = $pdo->query("SELECT COUNT(*) as count FROM car WHERE status = 'available'");
    $count = $checkStmt->fetch()['count'];
    
    if ($count == 0) {
        // Update some cars to available status if none are available
        $updateStmt = $pdo->prepare("UPDATE car SET status = 'available' WHERE status != 'available' LIMIT 5");
        $updateStmt->execute();
    }
    
    // Get cars based on category
    if ($category === "all") {
        $stmt = $pdo->query("SELECT * FROM car WHERE status = 'available' ORDER BY id DESC LIMIT 8");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM car WHERE status = 'available' AND category = ? ORDER BY id DESC LIMIT 8");
        $stmt->execute([$category]);
    }
    
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process each car
    foreach ($cars as &$car) {
        // Handle image path
        if (!empty($car['image'])) {
            if (strpos($car['image'], 'assets/images/') === false) {
                $car['image'] = 'assets/images/' . $car['image'];
            }
        } else {
            $car['image'] = 'assets/images/default-car.svg';
        }
        
        // Ensure image file exists, use default if not
        if (!file_exists($car['image'])) {
            $car['image'] = 'assets/images/default-car.svg';
        }
        
        // Clean up data for JSON
        foreach ($car as $key => $value) {
            if (is_string($value)) {
                $car[$key] = trim($value);
                // Remove any problematic characters
                $car[$key] = preg_replace('/[\x00-\x1F\x7F]/', '', $car[$key]);
            }
        }
        
        // Ensure required fields exist
        $car['transmission'] = $car['transmission'] ?? 'Manual';
        $car['fuel_type'] = $car['fuel_type'] ?? 'Gasoline';
        $car['seating_capacity'] = $car['seating_capacity'] ?? '5';
        $car['year'] = $car['year'] ?? '2023';
        $car['description'] = $car['description'] ?? 'Great car for your journey';
        $car['category'] = $car['category'] ?? 'economy';
    }
    
    // Return success response
    echo json_encode($cars);
    
} catch (PDOException $e) {
    // Return database error response
    http_response_code(500);
    echo json_encode([
        "error" => "Database error: " . $e->getMessage(),
        "cars" => []
    ]);
} catch (Exception $e) {
    // Return general error response
    http_response_code(500);
    echo json_encode([
        "error" => "Server error: " . $e->getMessage(),
        "cars" => []
    ]);
}
?>