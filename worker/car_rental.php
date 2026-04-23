<?php
require_once '../config/auth.php';
require_once '../config/database.php';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    
    $rating = $_POST['rating'];
    $feedback = $_POST['feedback'];
    
    // Store feedback in database (you'd need to create a feedback table)
    $stmt = $pdo->prepare("INSERT INTO feedback (rating, comment, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$rating, $feedback]);
    
    $feedback_success = "Thank you for your feedback!";
}

// Get products from database
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental Platform</title>
    <style>
        :root {
            --brand-color: #2563eb;
            --text-dark: #1f2937;
            --bg-light: #f3f4f6;
            --white: #ffffff;
        }

        body { 
            font-family: 'Segoe UI', Tahoma, sans-serif; 
            background: var(--bg-light); 
            margin: 0; 
            padding: 20px; 
            color: var(--text-dark); 
        }
        
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .header h1 {
            color: var(--brand-color);
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #6b7280;
            font-size: 1.1rem;
        }
        
        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }

        .car-card {
            background: var(--white);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .image-placeholder {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
            margin-bottom: 20px;
            border: 2px dashed rgba(255,255,255,0.3);
            font-weight: 500;
        }

        .car-title { 
            font-size: 1.4rem; 
            font-weight: 700; 
            margin: 15px 0; 
            color: var(--text-dark);
        }
        
        .car-category {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .price-label {
            font-size: 1.3rem;
            color: var(--brand-color);
            font-weight: 700;
            margin: 15px 0;
        }

        .car-description {
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 20px;
            min-height: 60px;
        }

        .stock-info {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .in-stock {
            background: #d1fae5;
            color: #065f46;
        }

        .low-stock {
            background: #fed7aa;
            color: #92400e;
        }

        .out-of-stock {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .actions { 
            display: flex; 
            gap: 12px; 
            flex-wrap: wrap; 
        }
        
        .btn {
            flex: 1;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
            font-size: 0.95rem;
            min-width: 120px;
        }

        .btn-details { 
            background: var(--brand-color); 
            color: white; 
        }
        
        .btn-details:hover { 
            background: #1d4ed8; 
            transform: translateY(-2px);
        }

        .btn-rent {
            background: #10b981;
            color: white;
        }

        .btn-rent:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        /* Feedback Section */
        .feedback-section {
            background: var(--white);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-top: 50px;
        }

        .feedback-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .feedback-header h2 {
            color: var(--text-dark);
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .feedback-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .rating-section {
            margin-bottom: 25px;
        }

        .rating-stars {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 15px;
        }

        .star {
            font-size: 2rem;
            color: #d1d5db;
            cursor: pointer;
            transition: color 0.2s;
        }

        .star:hover,
        .star.active {
            color: #fbbf24;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 1rem;
            resize: vertical;
            transition: border-color 0.3s;
        }

        textarea:focus {
            outline: none;
            border-color: var(--brand-color);
        }

        .submit-btn { 
            background: var(--brand-color); 
            color: white; 
            width: 100%; 
            font-size: 1.1rem;
            padding: 15px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .submit-btn:hover { 
            background: #1d4ed8; 
        }

        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            margin-top: 20px;
        }

        /* Modal for Details */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: var(--white);
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            position: relative;
        }

        .close-modal {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 2rem;
            cursor: pointer;
            color: #6b7280;
        }

        .close-modal:hover {
            color: var(--text-dark);
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-header h3 {
            color: var(--brand-color);
            font-size: 1.5rem;
        }

        .modal-body {
            color: var(--text-dark);
            line-height: 1.6;
        }

        .modal-price {
            font-size: 1.5rem;
            color: var(--brand-color);
            font-weight: 700;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🚗 Premium Car Rental</h1>
        <p>Find your perfect ride for any occasion</p>
    </div>

    <div class="product-grid">
        <?php if (empty($products)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                <h3 style="color: #6b7280; margin-bottom: 20px;">No cars available at the moment</h3>
                <p style="color: #9ca3af;">Please check back later or contact us for special requests.</p>
            </div>
        <?php else: ?>
            <?php foreach($products as $product): ?>
                <div class="car-card">
                    <div class="image-placeholder">
                        <span>🚗 <?php echo htmlspecialchars($product['name']); ?></span>
                    </div>
                    
                    <div class="car-category"><?php echo htmlspecialchars($product['category'] ?: 'Standard'); ?></div>
                    <div class="car-title"><?php echo htmlspecialchars($product['name']); ?></div>
                    
                    <div class="price-label">$<?php echo number_format($product['price'], 2); ?> / Day</div>
                    
                    <div class="car-description">
                        <?php echo htmlspecialchars($product['description'] ?: 'Comfortable and reliable vehicle perfect for your needs.'); ?>
                    </div>
                    
                    <?php
                    $stock_class = 'in-stock';
                    $stock_text = 'Available';
                    if ($product['stock'] <= 0) {
                        $stock_class = 'out-of-stock';
                        $stock_text = 'Out of Stock';
                    } elseif ($product['stock'] <= 3) {
                        $stock_class = 'low-stock';
                        $stock_text = 'Only ' . $product['stock'] . ' left';
                    }
                    ?>
                    <div class="stock-info <?php echo $stock_class; ?>">
                        <?php echo $stock_text; ?>
                    </div>
                    
                    <div class="actions">
                        <button class="btn btn-details" onclick="showDetails(<?php echo $product['id']; ?>)">
                            View Details
                        </button>
                        <?php if ($product['stock'] > 0): ?>
                            <button class="btn btn-rent" onclick="rentCar(<?php echo $product['id']; ?>)">
                                Rent Now
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="feedback-section">
        <div class="feedback-header">
            <h2>Customer Feedback</h2>
            <p>Your opinion helps us improve our service</p>
        </div>
        
        <?php if(isset($feedback_success)): ?>
            <div class="success-message">
                <?php echo $feedback_success; ?>
            </div>
        <?php endif; ?>
        
        <form class="feedback-form" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="submit_feedback" value="1">
            
            <div class="rating-section">
                <label>Rate your experience:</label>
                <div class="rating-stars">
                    <span class="star" data-rating="1">⭐</span>
                    <span class="star" data-rating="2">⭐</span>
                    <span class="star" data-rating="3">⭐</span>
                    <span class="star" data-rating="4">⭐</span>
                    <span class="star" data-rating="5">⭐</span>
                </div>
                <input type="hidden" id="rating" name="rating" value="5">
            </div>

            <div class="form-group">
                <label for="feedback">Your Feedback:</label>
                <textarea id="feedback" name="feedback" rows="4" placeholder="Tell us about your experience with our car rental service..." required></textarea>
            </div>
            
            <button type="submit" class="submit-btn">Submit Feedback</button>
        </form>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <div class="modal-header">
            <h3 id="modalTitle">Car Details</h3>
        </div>
        <div class="modal-body" id="modalBody">
            Loading details...
        </div>
    </div>
</div>

<script>
// Product data for modal
const products = <?php echo json_encode($products); ?>;

// Rating system
const stars = document.querySelectorAll('.star');
let selectedRating = 5;

stars.forEach(star => {
    star.addEventListener('click', function() {
        selectedRating = this.dataset.rating;
        document.getElementById('rating').value = selectedRating;
        updateStars();
    });
    
    star.addEventListener('mouseenter', function() {
        const hoverRating = this.dataset.rating;
        stars.forEach((s, index) => {
            if (index < hoverRating) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });
    });
});

document.querySelector('.rating-stars').addEventListener('mouseleave', updateStars);

function updateStars() {
    stars.forEach((star, index) => {
        if (index < selectedRating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

// Initialize stars
updateStars();

// Modal functions
function showDetails(productId) {
    const product = products.find(p => p.id == productId);
    if (!product) return;
    
    document.getElementById('modalTitle').textContent = product.name;
    document.getElementById('modalBody').innerHTML = `
        <div class="image-placeholder" style="margin-bottom: 20px;">
            <span>🚗 ${product.name}</span>
        </div>
        
        <p><strong>Category:</strong> ${product.category || 'Standard'}</p>
        <p><strong>Description:</strong> ${product.description || 'Comfortable and reliable vehicle perfect for your needs.'}</p>
        <p><strong>Daily Rate:</strong> <span style="color: var(--brand-color); font-weight: 700;">$${parseFloat(product.price).toFixed(2)} / Day</span></p>
        <p><strong>Availability:</strong> ${product.stock > 0 ? `${product.stock} units available` : 'Currently out of stock'}</p>
        
        <div style="margin-top: 25px;">
            <h4>Features:</h4>
            <ul style="color: #4b5563; line-height: 1.8;">
                <li>Air Conditioning</li>
                <li>Automatic Transmission</li>
                <li>GPS Navigation</li>
                <li>Bluetooth Connectivity</li>
                <li>Insurance Included</li>
            </ul>
        </div>
        
        ${product.stock > 0 ? `
            <div style="margin-top: 30px; text-align: center;">
                <button class="btn btn-rent" style="padding: 15px 30px; font-size: 1.1rem;" onclick="rentCar(${product.id})">
                    Rent This Car - $${parseFloat(product.price).toFixed(2)}/Day
                </button>
            </div>
        ` : '<p style="color: #dc2626; font-weight: 600; margin-top: 20px;">Currently unavailable for rental</p>'}
    `;
    
    document.getElementById('detailsModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

function rentCar(productId) {
    const product = products.find(p => p.id == productId);
    if (!product) return;
    
    if (product.stock <= 0) {
        alert('Sorry, this car is currently out of stock.');
        return;
    }
    
    // Here you would typically redirect to a booking page
    alert(`Booking process for ${product.name} would start here. This would take you to a booking/checkout page.`);
    closeModal();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('detailsModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

</body>
</html>
