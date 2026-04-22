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
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/car.css">
</head>
<body>
<?php if (isset($_SESSION['success'])): ?>
    <div style="background: #4CAF50; color: white; padding: 15px 20px; text-align: center; font-weight: bold; border-bottom: 3px solid #45a049; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        ✓ <?= htmlspecialchars($_SESSION['success']) ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div style="background: #f44336; color: white; padding: 15px 20px; text-align: center; font-weight: bold; border-bottom: 3px solid #da190b; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        ✗ <?= htmlspecialchars($_SESSION['error']) ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<header>
    <div class="custom-header">
        <div class="header-left">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="hamburger-btn" onclick="toggleMenu()" title="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            <?php endif; ?>
            <h2> CarRent</h2>
        </div>
        <div class="header-right">
            <nav>
                <a href="index.php">Home</a>
                <a href="car.php">Cars</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <?php else: ?>
                    <a href="p_login/login.php">Login</a>
                <?php endif; ?>
            </nav>
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="user-section">
                <span class="username"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if (isset($_SESSION['user_id'])): ?>
<?php if ($_SESSION['role'] === 'admin'): ?>
<div class="side-menu" id="sideMenu">
    <div class="menu-header">
        <h3>Dashboard Menu</h3>
    </div>
    <a href="admin/dashboard.php">📊 Dashboard</a>
    <a href="admin/cars.php">🚘 Manage Cars</a>
    <a href="admin/rentals.php">📅 Rentals</a>
    <a href="admin/products.php">📦 Products</a>
    <a href="admin/sales.php">💰 Sales</a>
    <a href="admin/users.php">👥 Users</a>
    <a href="./p_login/logout.php">🚪 Logout</a>
</div>
<?php endif; ?>

<div class="overlay" id="overlay" onclick="closeMenus()"></div>
<?php endif; ?>

<section class="rentals-hero">
    <h1>Our Premium Rental Fleet</h1>
    <p>Choose from a wide range of luxury and economy vehicles</p>
</section>

<section class="rentals-section">
    <div class="section-title">
        <h2>Available Cars for Rent</h2>
        <p>Quality vehicles at competitive prices</p>
    </div>

    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Search cars by name, brand, or model...">
    </div>
    
    <div class="rental-grid">
        <?php
        $stmt = $pdo->query("SELECT * FROM car WHERE status = 'available' ORDER BY id DESC");
        $cars = $stmt->fetchAll();
        
        foreach($cars as $car):
        ?>
        <div class="rental-item">
            <img class="rental-img" src="assets/images/download (4).jpg" alt="<?= htmlspecialchars($car['car_name']) ?>">
            <div class="rental-info">
                <h3><?= htmlspecialchars($car['car_name']) ?></h3>
                <div class="car-meta"><?= htmlspecialchars($car['brand'] ?? '') ?> <?= htmlspecialchars($car['model'] ?? '') ?></div>
                <div class="car-year"><?= $car['year'] ?></div>
                <p class="car-price">$<?= number_format($car['price_per_day'], 2) ?> <span>/ day</span></p>
                <p class="car-description"><?= htmlspecialchars($car['description'] ?? 'Premium car rental') ?></p>
                <button class="btn-rent-now" onclick="rentCar(<?= $car['id'] ?>, '<?= htmlspecialchars($car['car_name']) ?>', <?= $car['price_per_day'] ?>)">
                    Rent Now →
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<div id="bookingModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Book Your Rental</h2>
        <div id="modalCarInfo"></div>
        <form id="bookingForm" method="POST" action="processor/booking_process.php">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="car_id" id="car_id">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="customer_name" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="customer_email" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="customer_phone" required>
            </div>
            <div class="form-group">
                <label>Total Days</label>
                <input type="number" name="total_days" id="totalDays" min="1" required>
            </div>
            <div class="form-group">
                <label>Pickup Date</label>
                <input type="date" name="pickup_date" id="pickupDate" required>
            </div>
            <div class="form-group">
                <label>Return Date</label>
                <input type="date" name="return_date" id="returnDate" required>
            </div>
            <div class="total-price-display" id="totalPriceDisplay" style="display: none;">
                <strong>Total Price: $<span id="totalPrice">0.00</span></strong>
            </div>
            <button type="submit" class="submit-booking">Confirm Booking</button>
        </form>
    </div>
</div>

<footer>
    <p>&copy; <?= date('Y') ?> CarRent System. All rights reserved.</p>
</footer>

<script>
let selectedCar = { id: null, name: '', pricePerDay: 0 };

// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();
    const items = document.querySelectorAll('.rental-item');
    
    items.forEach(item => {
        const titleEl = item.querySelector('h3');
        const brandEl = item.querySelector('.car-meta');
        const yearEl = item.querySelector('.car-year');
        
        const title = titleEl ? titleEl.textContent.toLowerCase() : '';
        const brand = brandEl ? brandEl.textContent.toLowerCase() : '';
        const year = yearEl ? yearEl.textContent.toLowerCase() : '';
        
        if (query === '') {
            item.style.display = 'block';
        } else if (title.includes(query) || brand.includes(query) || year.includes(query)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

function rentCar(carId, carName, pricePerDay) {
    selectedCar = { id: carId, name: carName, pricePerDay: pricePerDay };

    document.getElementById('car_id').value = carId;

    document.getElementById('modalCarInfo').innerHTML = `
        <div style="background:#f0f0f0; padding:10px; border-radius:8px; margin-bottom:15px;">
            <strong>${carName}</strong><br>
            Price: $${pricePerDay.toFixed(2)} per day
        </div>
    `;

    document.getElementById('bookingForm').reset();
    document.getElementById('totalPrice').textContent = '0.00';

    document.getElementById('bookingModal').classList.add('show');
    document.body.classList.add('modal-open');
}

// ✅ FIXED FUNCTION (this was broken)
function calculateTotal() {
    const pickupDate = document.getElementById('pickupDate').value;
    const returnDate = document.getElementById('returnDate').value;

    if (pickupDate && returnDate) {
        const pickup = new Date(pickupDate);
        const returnDateObj = new Date(returnDate);

        const days = Math.ceil((returnDateObj - pickup) / (1000 * 60 * 60 * 24));

        if (days > 0) {
            const total = days * selectedCar.pricePerDay;

            document.getElementById('totalPrice').textContent = total.toFixed(2);
            document.getElementById('totalPriceDisplay').style.display = 'block';

            // ✅ sync total days
            document.getElementById('totalDays').value = days;

        } else {
            document.getElementById('totalPriceDisplay').style.display = 'none';
            document.getElementById('totalDays').value = '';
        }
    } else {
        document.getElementById('totalPriceDisplay').style.display = 'none';
        document.getElementById('totalDays').value = '';
    }
}

// ✅ AUTO-UPDATE RETURN DATE FROM TOTAL DAYS
document.getElementById('totalDays').addEventListener('input', function () {
    const pickupDate = document.getElementById('pickupDate').value;
    const totalDays = parseInt(this.value);

    if (pickupDate && totalDays > 0) {
        let pickup = new Date(pickupDate);

        pickup.setDate(pickup.getDate() + totalDays);

        const returnDate = pickup.toISOString().split('T')[0];
        document.getElementById('returnDate').value = returnDate;

        calculateTotal();
    }
});

document.getElementById('pickupDate').addEventListener('change', calculateTotal);
document.getElementById('returnDate').addEventListener('change', calculateTotal);

// Modal controls
var modal = document.getElementById('bookingModal');

document.querySelector('.close-modal').onclick = function() { 
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
};

window.onclick = function(event) { 
    if (event.target == modal) {
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
    }
};

// Date limits
var today = new Date().toISOString().split('T')[0];
document.getElementById('pickupDate').min = today;

document.getElementById('pickupDate').addEventListener('change', function() {
    document.getElementById('returnDate').min = this.value;
});

document.getElementById('returnDate').min = today;

// Menus
function toggleMenu() {
    const menu = document.getElementById("sideMenu");
    const overlay = document.getElementById("overlay");
    const hamburger = document.querySelector('.hamburger-btn');

    if (!menu) return; // No menu to toggle

    if (menu.classList.contains("active")) {
        menu.classList.remove("active");
        overlay.classList.remove("active");
        hamburger.classList.remove('active');
    } else {
        menu.classList.add("active");
        overlay.classList.add("active");
        hamburger.classList.add('active');
    }
}

function closeMenus() {
    const menu = document.getElementById("sideMenu");
    const overlay = document.getElementById("overlay");
    const hamburger = document.querySelector('.hamburger-btn');

    if (menu) menu.classList.remove("active");
    if (overlay) overlay.classList.remove("active");
    if (hamburger) hamburger.classList.remove('active');
}
</script>
</body>
</html>