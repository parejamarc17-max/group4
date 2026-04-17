<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div style="display: flex; align-items: center; margin-bottom: 20px; padding: 10px;">
        <img src="car_rental_system/assets/images/best_car.jpg" alt="cuete">
        <h2 style="margin: 0; color: white;"><strong>DRIVE ADMIN</strong></h2>
    </div>
    
    <!-- Back button -->
    <button onclick="history.back()" class="btn-nav" style="background-color: #555; margin-bottom: 10px;">
        ⬅️ Back
    </button>
    
    <a href="dashboard.php" class="btn-nav <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="cars.php" class="btn-nav <?= $current_page == 'cars.php' ? 'active' : '' ?>"> Cars</a>
    <a href="rentals.php" class="btn-nav <?= $current_page == 'rentals.php' ? 'active' : '' ?>"> Rentals</a>
    <a href="users.php" class="btn-nav <?= $current_page == 'users.php' ? 'active' : '' ?>"> Users</a>
    <a href="../logout.php" class="btn-nav"> Logout</a>
</div>