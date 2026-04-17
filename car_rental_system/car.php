<?php
require_once 'config/database.php';
session_start();

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Cars - CarRent</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/car.css">
    <style>
        .rental-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
        }
    </style>
</head>
<body>

<?php if (isset($_SESSION['success'])): ?>
    <div style="background: green; color: white; padding: 10px; text-align: center;">
        <?= htmlspecialchars($_SESSION['success']) ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div style="background: red; color: white; padding: 10px; text-align: center;">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<header>
    <div class="logo">
        <h2>CarRent</h2>
    </div>
    <nav>
        <a href="index.php"><strong>Home</strong></a>
        <a href="car.php" class="active"><strong>Cars</strong></a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php"><strong>Logout</strong></a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>

<section class="rentals-hero">
    <h1>Our Premium Rental Fleet</h1>
    <p>Choose from a wide range of luxury and economy vehicles</p>
</section>

<section class="rentals-section">
    <div class="section-title">
        <h2>Available Cars for Rent</h2>
        <p>Quality vehicles at competitive prices</p>
    </div>
    
    <div class="rental-grid">
        <?php
        // Only show cars with status = 'available'
        $stmt = $pdo->prepare("SELECT * FROM cars WHERE status = 'available' ORDER BY id DESC");
        $stmt->execute();
        $cars = $stmt->fetchAll();
        
        if (count($cars) > 0):
            foreach($cars as $car):
        ?>
        <div class="rental-item">
            <img class="rental-img" src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['car_name']) ?>">
            <div class="rental-info">
                <h3><?= htmlspecialchars($car['car_name']) ?></h3>
                <div class="car-year"><?= htmlspecialchars($car['year'] ?? '2024') ?></div>
                <p class="car-price">₱<?= number_format($car['price_per_day'], 2) ?> <span>/ day</span></p>
                <p class="car-description"><?= htmlspecialchars($car['description'] ?? 'Premium car rental') ?></p>
                <?php if(isset($_SESSION['user_id'])): ?>
                <button class="btn-rent-now" onclick="rentCar(<?= $car['id'] ?>, '<?= htmlspecialchars($car['car_name']) ?>', <?= $car['price_per_day'] ?>)">
                    Rent Now →
                </button>
                <?php else: ?>
                <a href="login.php" class="btn-rent-now" style="display: inline-block; text-decoration: none;">Login to Rent</a>
                <?php endif; ?>
            </div>
        </div>
        <?php 
            endforeach;
        else:
        ?>
        <div class="no-cars">
            <p>No cars available at the moment. Please check back later.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal and footer continue... -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2><strong>Book Your Rental</strong></h2>
        <div id="modalCarInfo" class="car-info-card"></div>
        <form id="bookingForm" method="POST" action="process_booking.php" class="booking-form">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="car_id" id="car_id">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="customer_name" placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="customer_email" placeholder="your@email.com" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="customer_phone" placeholder="+1 (555) 123-4567" required>
                </div>
                <div class="form-group">
                    <label>Number of Days</label>
                    <input type="number" name="rental_days" id="rentalDays" min="1" max="30" placeholder="1" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Pickup Date</label>
                    <input type="date" name="pickup_date" id="pickupDate" required>
                </div>
                <div class="form-group">
                    <label>Return Date</label>
                    <input type="date" name="return_date" id="returnDate" required>
                </div>
            </div>
            
            <div class="total-price-display">
                <div class="price-summary">
                    <span class="price-label">Total Price:</span>
                    <span class="price-amount">₱<span id="totalPrice">0.00</span></span>
                </div>
            </div>
            
            <button type="submit" class="submit-booking">✅ Confirm Booking</button>
        </form>
    </div>
</div>

<footer>
    <p>&copy; <?= date('Y') ?> CarRent System. All rights reserved.</p>
</footer>

<script>
    let selectedCar = { id: null, name: '', pricePerDay: 0 };
    
    function rentCar(carId, carName, pricePerDay) {
        selectedCar = { id: carId, name: carName, pricePerDay: pricePerDay };
        document.getElementById('car_id').value = carId;
        document.getElementById('modalCarInfo').innerHTML = `
            <div class="car-details">
                <h3>${carName}</h3>
                <p class="car-price-info">₱${pricePerDay.toFixed(2)} per day</p>
            </div>
        `;
        document.getElementById('bookingForm').reset();
        document.getElementById('totalPrice').textContent = '0.00';
        document.getElementById('bookingModal').style.display = 'block';
    }
    
    document.getElementById('rentalDays').addEventListener('input', function() {
        const days = parseInt(this.value) || 0;
        const total = days * selectedCar.pricePerDay;
        document.getElementById('totalPrice').textContent = total.toFixed(2);
        
        const pickup = document.getElementById('pickupDate').value;
        if (pickup && days > 0) {
            const pickupDate = new Date(pickup);
            pickupDate.setDate(pickupDate.getDate() + days);
            document.getElementById('returnDate').value = pickupDate.toISOString().split('T')[0];
        }
    });
    
    var modal = document.getElementById('bookingModal');
    if(modal) {
        document.querySelector('.close-modal').onclick = function() { modal.style.display = 'none'; }
        window.onclick = function(event) { if (event.target == modal) modal.style.display = 'none'; }
    }
    
    var today = new Date().toISOString().split('T')[0];
    var pickupInput = document.getElementById('pickupDate');
    if(pickupInput) {
        pickupInput.min = today;
        pickupInput.addEventListener('change', function() {
            document.getElementById('returnDate').min = this.value;
            
            const days = parseInt(document.getElementById('rentalDays').value) || 0;
            if (days > 0) {
                const pickupDate = new Date(this.value);
                pickupDate.setDate(pickupDate.getDate() + days);
                document.getElementById('returnDate').value = pickupDate.toISOString().split('T')[0];
            }
        });
        document.getElementById('returnDate').min = today;
    }
</script>
<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>