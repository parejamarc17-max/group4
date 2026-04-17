<?php
session_start();

// Redirect to login if NOT logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Try multiple possible image paths (check which one exists)
$possibleImages = [
    'assets/images/hero-bg.jpg',
    'assets/images/2021-Rolls-Royce-Ghost.jpg',
    'assets/images/2021 Rolls Royce Ghost.jpg',
    'assets/images/car-hero.jpg',
    'assets/images/default-hero.jpg'
];

$hero_bg = 'assets/images/hero-bg.jpg'; // Default fallback

foreach ($possibleImages as $image) {
    if (file_exists($image)) {
        $hero_bg = $image;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarRent - Premium Car Rental</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navigation.css">
    <style>
        /* Dynamic hero background */
        .hero {
            background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo $hero_bg; ?>');
        }
        
        /* Hamburger menu styles */
        .hamburger-btn {
            width: 30px;
            height: 25px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            cursor: pointer;
        }
        
        .hamburger-btn span {
            display: block;
            height: 4px;
            background-color: #333;
            border-radius: 2px;
            transition: 0.3s;
        }
        
        /* Side Menu Styles */
        .side-menu {
            position: fixed;
            top: 0;
            left: -300px;
            width: 280px;
            height: 100%;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: 0.3s;
            padding: 2rem 1rem;
            overflow-y: auto;
        }
        
        .side-menu.active {
            left: 0;
        }
        
        .side-menu h3 {
            padding-bottom: 1rem;
            border-bottom: 2px solid #ff6b00;
            margin-bottom: 1rem;
        }
        
        .side-menu a {
            display: block;
            padding: 0.8rem 1rem;
            color: #333;
            text-decoration: none;
            transition: 0.3s;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        
        .side-menu a:hover {
            background: #ff6b00;
            color: white;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
        
        .overlay.active {
            display: block;
        }
        
        .no-cars {
            text-align: center;
            padding: 40px;
            background: #f9f9f9;
            border-radius: 10px;
            grid-column: span 4;
        }
        
        .no-cars p {
            font-size: 1.2rem;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .hero h1 { font-size: 2rem; }
            .cars { padding: 1rem; gap: 1rem; }
            .header-right nav {
                gap: 0.5rem;
            }
            nav a {
                padding: 0.3rem 0.5rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="custom-header">
        <div class="header-left">
            <!-- Hamburger Button -->
            <div class="hamburger-btn" onclick="toggleMenu()" title="Menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <h2>CarRent</h2>
        </div>
        <div class="header-right">
            <nav>
                <a href="index.php"><strong>Home</strong></a>
                <a href="car.php"><strong>Cars</strong></a>
                <a href="logout.php"><strong>Logout</strong></a>
            </nav>
        </div>
    </div>
</header>

<!-- Sidebar -->
<div class="side-menu" id="sideMenu">
    <h3>Dashboard Menu</h3>
    <a href="admin/dashboard.php" onclick="toggleMenu()">Dashboard</a>
    <a href="admin/cars.php" onclick="toggleMenu()">Cars</a>
    <a href="admin/rentals.php" onclick="toggleMenu()">Rentals</a>
    <a href="admin/users.php" onclick="toggleMenu()">Users</a>
    <a href="logout.php" onclick="toggleMenu()"><strong>Logout</strong></a>
</div>

<div class="overlay" id="overlay" onclick="toggleMenu()"></div>

<!-- Hero Section -->
<section class="hero">
    <h1>Drive Your Dream Car Today</h1>
    <p style="font-size: 1.2rem; text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.6);">
        Premium cars available for rent at unbeatable prices 
    </p>
    <button class="btn-vc" onclick="window.location.href='car.php'">View Cars</button>
</section>

<!-- Cars Section -->
<section class="cars">
    <?php
    try {
        $stmt = $pdo->query("SELECT * FROM cars WHERE status = 'available' ORDER BY id DESC LIMIT 4");
        $cars = $stmt->fetchAll();

        if (count($cars) > 0):
            foreach($cars as $car):
                // Use the actual image from database
                $image = !empty($car['image']) ? htmlspecialchars($car['image']) : 'assets/images/default-car.jpg';
                // Fix image path if needed
                if (!file_exists($image) && file_exists('../' . $image)) {
                    $image = '../' . $image;
                }
    ?>
    <div class="car-card">
        <img src="<?= $image ?>" 
             alt="<?= htmlspecialchars($car['car_name']) ?>"
             onerror="this.src='assets/images/default-car.jpg'">
        <h3><?= htmlspecialchars($car['car_name']) ?></h3>
        <p>₱<?= number_format($car['price_per_day'], 2) ?>/day</p>
        <button onclick="window.location.href='car.php'">Rent Now</button>
    </div>
    <?php 
            endforeach;
        else:
    ?>
    <div class="no-cars">
        <p>No cars available at the moment. Please check back later.</p>
    </div>
    <?php 
        endif;
    } catch (PDOException $e) {
        echo '<div class="no-cars"><p>Database error. Please try again later.</p></div>';
    }
    ?>
</section>

<footer>
    <p>&copy; <?= date('Y') ?> CarRent System. All rights reserved.</p>
    <p>Premium car rental services | Drive your dream car today</p>
</footer>

<script>
function toggleMenu() {
    const sideMenu = document.getElementById("sideMenu");
    const overlay = document.getElementById("overlay");
    sideMenu.classList.toggle("active");
    overlay.classList.toggle("active");
    
    // Prevent body scroll when menu is open
    if (sideMenu.classList.contains("active")) {
        document.body.style.overflow = "hidden";
    } else {
        document.body.style.overflow = "";
    }
}

// Close menu when clicking escape key
document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
        const sideMenu = document.getElementById("sideMenu");
        const overlay = document.getElementById("overlay");
        if (sideMenu.classList.contains("active")) {
            sideMenu.classList.remove("active");
            overlay.classList.remove("active");
            document.body.style.overflow = "";
        }
    }
});
</script>

</body>
</html>