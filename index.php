<?php
session_start();
require_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Luxury Car Rental Philippines</title>
    <link rel="stylesheet" href="assets/css/public.css">
    <link rel="stylesheet" href="assets/css/modal-image-fix.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php
session_start();

// Check for remember me cookie
if (!isset($_SESSION['customer_logged_in']) && isset($_COOKIE['customer_email'])) {
    $_SESSION['customer_logged_in'] = true;
    $_SESSION['customer_email'] = $_COOKIE['customer_email'];
}
?>

<!-- Professional Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <div class="logo">
            
        </div>
        <ul class="nav-menu">
            <li><a href="#home" class="nav-link active">Home</a></li>
            <li><a href="#features" class="nav-link">Features</a></li>
            <li><a href="#cars" class="nav-link">Fleet</a></li>
            <li><a href="#how-it-works" class="nav-link">How It Works</a></li>
            <li><a href="#testimonials" class="nav-link">Reviews</a></li>
        </ul>
        <div class="auth-section">
            <?php if (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']): ?>
                <div id="userDisplay" class="user-logged-in">
                    <span id="userName" class="fas fa-user-circle"><?php echo htmlspecialchars($_SESSION['customer_name'] ?? $_SESSION['customer_email']); ?></span>
                </div>
                <button id="logoutBtn" class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
            <?php else: ?>
                <a href="p_login/login.php" class="login-btn">Login</a>
                <div id="userDisplay" class="user-guest" style="display:none;">
                    <span id="userName" class="fas fa-user-circle">Guest</span>
                </div>
                <button id="logoutBtn" class="logout-btn" style="display:none;">Logout</button>
            <?php endif; ?>
        </div>
    </div>
</nav>
<!-- Hero Section -->
<section id="home" class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1>Drive Your <span class="gradient-text">Dream Car</span></h1>
        <p>Premium car rental service in the Philippines • Best prices guaranteed</p>
        <div class="hero-stats">
            <div class="stat">
                <h3>500+</h3>
                <p>Happy Customers</p>
            </div>
            <div class="stat">
                <h3>50+</h3>
                <p>Luxury Cars</p>
            </div>
            <div class="stat">
                <h3>24/7</h3>
                <p>Support</p>
            </div>
        </div>
        <a href="#cars" class="cta-button">Browse Fleet <i class="fas fa-arrow-right"></i></a>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="features">
    <div class="container">
        <div class="section-header">
            <h2>Why Choose DriveGo?</h2>
            <p>Experience the best car rental service in the Philippines</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h3>Full Insurance Coverage</h3>
                <p>Comprehensive protection for your peace of mind</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-headset"></i></div>
                <h3>24/7 Customer Support</h3>
                <p>Round-the-clock assistance anywhere in PH</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-wallet"></i></div>
                <h3>Best Price Guarantee</h3>
                <p>We'll match any lower price + 10% discount</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-gas-pump"></i></div>
                <h3>Free Mileage</h3>
                <p>Unlimited kilometers on all rentals</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                <h3>Free Cancellation</h3>
                <p>Cancel up to 24 hours before pickup</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-truck"></i></div>
                <h3>Free Delivery</h3>
                <p>Free car delivery within mati city</p>
            </div>
        </div>
    </div>
</section>

<!-- Car Fleet Section with Category Filters -->
<section id="cars" class="fleet">
    <div class="container">
        <div class="section-header">
            <h2>Our Premium Fleet</h2>
            <p>Choose from our wide selection of luxury and economy cars</p>
        </div>
        
        <!-- Category Filter Navigation -->
        <div class="fleet-filters" id="categoryFilters">
            <button class="filter-btn active" data-filter="all">
                <i class="fas fa-car"></i> All Cars
            </button>
            <button class="filter-btn" data-filter="luxury">
                <i class="fas fa-crown"></i> Luxury
            </button>
            <button class="filter-btn" data-filter="sports">
                <i class="fas fa-flag-checkered"></i> Sports
            </button>
            <button class="filter-btn" data-filter="suv">
                <i class="fas fa-truck-pickup"></i> SUV
            </button>
            <button class="filter-btn" data-filter="economy">
                <i class="fas fa-coins"></i> Economy
            </button>
        </div>
        
        <!-- Cars Dashboard Grid -->
        <div class="cars-grid" id="carsGrid">
            <!-- Direct HTML Cars - Bypass JavaScript Issues -->
            <div class="cars-section">
                <!-- Car 1: Toyota Camry -->
                <div class="car-card" data-car-id="1">
                    <img src="assets/images/1777003333_69eaeb457d014.jpg" alt="Toyota Camry" class="car-image" onerror="this.src='assets/images/default-car.svg'">
                    <div class="car-info">
                        <h3 class="car-name">Toyota Camry</h3>
                        <p class="car-brand">Toyota Camry (2023)</p>
                        <div class="car-specs-badges">
                            <span class="spec-badge transmission">Automatic</span>
                            <span class="spec-badge fuel">Gasoline</span>
                            <span class="spec-badge seats">5 Seats</span>
                        </div>
                        <p class="car-price">₱2,500 <span>/ day</span></p>
                        <button class="rent-btn" data-car-id="1" data-car-name="Toyota Camry" data-car-price="2500" data-car-image="assets/images/1777003333_69eaeb457d014.jpg">Rent Now</button>
                    </div>
                </div>
                
                <!-- Car 2: Honda Civic -->
                <div class="car-card" data-car-id="2">
                    <img src="assets/images/1777003840_69eaed40b3ca5.jpg" alt="Honda Civic" class="car-image" onerror="this.src='assets/images/default-car.svg'">
                    <div class="car-info">
                        <h3 class="car-name">Honda Civic</h3>
                        <p class="car-brand">Honda Civic (2023)</p>
                        <div class="car-specs-badges">
                            <span class="spec-badge transmission">Manual</span>
                            <span class="spec-badge fuel">Gasoline</span>
                            <span class="spec-badge seats">5 Seats</span>
                        </div>
                        <p class="car-price">₱2,200 <span>/ day</span></p>
                        <button class="rent-btn" data-car-id="2" data-car-name="Honda Civic" data-car-price="2200" data-car-image="assets/images/1777003840_69eaed40b3ca5.jpg">Rent Now</button>
                    </div>
                </div>
                
                <!-- Car 3: Ford Mustang -->
                <div class="car-card" data-car-id="3">
                    <img src="assets/images/car-png-39073.png" alt="Ford Mustang" class="car-image" onerror="this.src='assets/images/default-car.svg'">
                    <div class="car-info">
                        <h3 class="car-name">Ford Mustang</h3>
                        <p class="car-brand">Ford Mustang (2023)</p>
                        <div class="car-specs-badges">
                            <span class="spec-badge transmission">Manual</span>
                            <span class="spec-badge fuel">Gasoline</span>
                            <span class="spec-badge seats">4 Seats</span>
                        </div>
                        <p class="car-price">₱4,500 <span>/ day</span></p>
                        <button class="rent-btn" data-car-id="3" data-car-name="Ford Mustang" data-car-price="4500" data-car-image="assets/images/car-png-39073.png">Rent Now</button>
                    </div>
                </div>
                
                <!-- Car 4: Toyota RAV4 -->
                <div class="car-card" data-car-id="4">
                    <img src="assets/images/cdb01d20b2b15e4152cfa2b82cd6fb01.jpg" alt="Toyota RAV4" class="car-image" onerror="this.src='assets/images/default-car.svg'">
                    <div class="car-info">
                        <h3 class="car-name">Toyota RAV4</h3>
                        <p class="car-brand">Toyota RAV4 (2023)</p>
                        <div class="car-specs-badges">
                            <span class="spec-badge transmission">Automatic</span>
                            <span class="spec-badge fuel">Gasoline</span>
                            <span class="spec-badge seats">7 Seats</span>
                        </div>
                        <p class="car-price">₱3,500 <span>/ day</span></p>
                        <button class="rent-btn" data-car-id="4" data-car-name="Toyota RAV4" data-car-price="3500" data-car-image="assets/images/cdb01d20b2b15e4152cfa2b82cd6fb01.jpg">Rent Now</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Empty State Message -->
        <div id="emptyState" class="empty-state" style="display: none;">
            <i class="fas fa-car-side"></i>
            <h3>No cars found in this category</h3>
            <p>Try selecting a different category</p>
        </div>
    </div>
</section>

<!-- Car Modal (Single Image View & Details) -->
<div id="carModal" class="car-modal">
    <div class="modal-container">
        <span class="modal-close">&times;</span>
        <div class="modal-content">
            <div class="modal-image-section">
                <div class="image-container">
                    <img id="modalCarImage" src="" alt="Car Image" style="width: 100%; height: auto; border-radius: 8px;">
                </div>
            </div>
            <div class="modal-info-section">
                <h2 id="modalCarName">Car Name</h2>
                <div class="modal-price" id="modalCarPrice"></div>
                <div class="modal-features" id="modalCarFeatures"></div>
                <div class="modal-specs">
                    <h3><i class="fas fa-chart-line"></i> Specifications</h3>
                    <ul id="modalCarSpecs"></ul>
                </div>
                <button id="modalRentBtn" class="rent-now-modal">Rent Now <i class="fas fa-arrow-right"></i></button>
                <p class="rental-note"><i class="fas fa-info-circle"></i> Login required to complete rental</p>
            </div>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<section id="how-it-works" class="how-it-works">
    <div class="container">
        <div class="section-header">
            <h2>How It Works</h2>
            <p>Rent a car in 3 simple steps</p>
        </div>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <i class="fas fa-search step-icon"></i>
                <h3>Choose Your Car</h3>
                <p>Browse our fleet and select your preferred vehicle</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <i class="fas fa-user-lock step-icon"></i>
                <h3>Login to Account</h3>
                <p>Sign in with your customer account</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <i class="fas fa-check-circle step-icon"></i>
                <h3>Confirm Rental</h3>
                <p>Complete payment and get your car delivered</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section id="testimonials" class="testimonials">
    <div class="container">
        <div class="section-header">
            <h2>What Our Customers Say</h2>
            <p>Trusted by thousands of happy drivers</p>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="stars">★★★★★</div>
                <p>"Amazing service! The car was in perfect condition and delivery was prompt."</p>
                <div class="customer">
                    <strong>James esteban</strong>
                    <span>dahican</span>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="stars">★★★★★</div>
                <p>"Best car rental experience in Manila. Will definitely rent again!"</p>
                <div class="customer">
                    <strong>Reyven sayp</strong>
                    <span>martinez</span>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="stars">★★★★★</div>
                <p>"Professional staff and well-maintained cars. Highly recommended!"</p>
                <div class="customer">
                    <strong>althea</strong>
                    <span>central</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<?php include("includes/public_footer.php"); ?>

<script src="script.js"></script>
</body>
</html>