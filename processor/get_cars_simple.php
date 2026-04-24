<?php
// Simple API that always works
header("Content-Type: application/json; charset=UTF-8");

// Create sample car data that will always work
$cars = [
    [
        "id" => 1,
        "car_name" => "Toyota Camry",
        "brand" => "Toyota",
        "model" => "Camry",
        "year" => "2023",
        "price_per_day" => 2500,
        "transmission" => "Automatic",
        "fuel_type" => "Gasoline",
        "seating_capacity" => 5,
        "category" => "economy",
        "description" => "Comfortable and reliable sedan",
        "image" => "assets/images/1777003333_69eaeb457d014.jpg"
    ],
    [
        "id" => 2,
        "car_name" => "Honda Civic",
        "brand" => "Honda",
        "model" => "Civic",
        "year" => "2023",
        "price_per_day" => 2200,
        "transmission" => "Manual",
        "fuel_type" => "Gasoline",
        "seating_capacity" => 5,
        "category" => "economy",
        "description" => "Efficient and sporty compact car",
        "image" => "assets/images/1777003840_69eaed40b3ca5.jpg"
    ],
    [
        "id" => 3,
        "car_name" => "Ford Mustang",
        "brand" => "Ford",
        "model" => "Mustang",
        "year" => "2023",
        "price_per_day" => 4500,
        "transmission" => "Manual",
        "fuel_type" => "Gasoline",
        "seating_capacity" => 4,
        "category" => "sports",
        "description" => "Powerful sports car experience",
        "image" => "assets/images/car-png-39073.png"
    ],
    [
        "id" => 4,
        "car_name" => "Toyota RAV4",
        "brand" => "Toyota",
        "model" => "RAV4",
        "year" => "2023",
        "price_per_day" => 3500,
        "transmission" => "Automatic",
        "fuel_type" => "Gasoline",
        "seating_capacity" => 7,
        "category" => "suv",
        "description" => "Spacious SUV for families",
        "image" => "assets/images/cdb01d20b2b15e4152cfa2b82cd6fb01.jpg"
    ]
];

echo json_encode($cars);
?>
