<?php
require_once '../config/auth.php';
require_once '../config/database.php';
require_once '../includes/NotificationHelper.php';
checkAuth();

if ($_SESSION['role'] !== 'customer') {
    header('Location: ../p_login/login.php?error=Access denied');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Get rental ID from URL
$rental_id = isset($_GET['rental_id']) ? (int)$_GET['rental_id'] : 0;

// Get rental details - only show if approved
$stmt = $pdo->prepare("SELECT r.*, c.car_name, c.brand, c.model, c.image 
                       FROM rentals r 
                       JOIN car c ON r.car_id = c.id 
                       WHERE r.id = ? AND r.user_id = ?");
$stmt->execute([$rental_id, $user_id]);
$rental = $stmt->fetch();

if (!$rental) {
    header('Location: my_bookings.php?error=Invalid booking');
    exit();
}

// Check if booking is approved for payment
if ($rental['approval_status'] !== 'approved') {
    header('Location: my_bookings.php?error=Payment is not yet available. Please wait for approval.');
    exit();
}

// Check if payment is already completed
if ($rental['payment_status'] === 'paid') {
    header('Location: my_bookings.php?error=This booking has already been paid.');
    exit();
}

// Get payment request
$stmt = $pdo->prepare("SELECT * FROM payment_requests WHERE rental_id = ? AND customer_id = ? AND status = 'pending' ORDER BY id DESC LIMIT 1");
$stmt->execute([$rental_id, $user_id]);
$payment_request = $stmt->fetch();

// If no payment request exists, create one
if (!$payment_request) {
    require_once '../includes/PaymentHelper.php';
    $paymentHelper = new PaymentHelper($pdo);
    $paymentHelper->createPaymentRequest($rental_id, $rental['total_cost'], $user_id, 'gcash');
    
    // Reload payment request
    $stmt = $pdo->prepare("SELECT * FROM payment_requests WHERE rental_id = ? AND customer_id = ? AND status = 'pending' ORDER BY id DESC LIMIT 1");
    $stmt->execute([$rental_id, $user_id]);
    $payment_request = $stmt->fetch();
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment'])) {
    $payment_method = $_POST['payment_method'];
    $reference_number = trim($_POST['reference_number']);
    $notes = trim($_POST['notes']);
    $amount = $rental['total_cost'];
    
    $errors = [];
    
    // Validate reference number for non-cash payments
    if ($payment_method !== 'cash' && empty($reference_number)) {
        $errors[] = "Reference number is required for this payment method";
    }
    
    // Handle receipt upload
    $receipt_path = null;
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/receipts/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'application/pdf'];
        $file_type = $_FILES['receipt']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $file_extension = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
            $filename = 'receipt_' . time() . '_' . $rental_id . '.' . $file_extension;
            $receipt_path = $upload_dir . $filename;
            
            if (!move_uploaded_file($_FILES['receipt']['tmp_name'], $receipt_path)) {
                $errors[] = "Failed to upload receipt";
            }
        } else {
            $errors[] = "Invalid file type. Please upload JPG, PNG, or PDF.";
        }
    } else {
        $errors[] = "Please upload your payment receipt/screenshot";
    }
    
    if (empty($errors)) {
        try {
            // Update payment request
            $stmt = $pdo->prepare("UPDATE payment_requests SET 
                                    payment_method = ?, 
                                    transaction_reference = ?, 
                                    receipt_image = ?, 
                                    notes = ?,
                                    status = 'paid',
                                    paid_at = NOW()
                                   WHERE id = ?");
            $stmt->execute([$payment_method, $reference_number, $receipt_path, $notes, $payment_request['id']]);
            
            // Update rental payment status
            $stmt = $pdo->prepare("UPDATE rentals SET payment_status = 'paid', payment_method = ? WHERE id = ?");
            $stmt->execute([$payment_method, $rental_id]);
            
            // Send notification to all workers and admins
            $notificationHelper = new NotificationHelper($pdo);
            $notificationHelper->notifyAllStaff(
                'Payment Received - Need Verification',
                "Customer {$rental['customer_name']} has submitted payment for {$rental['car_name']}. Reference: " . ($reference_number ?: 'Cash payment'),
                'payment',
                'worker/verify_payments.php'
            );
            
            $success_message = "Payment submitted successfully! Our team will verify your payment within 24 hours. You will receive a notification once verified.";
            
            // Auto redirect after 5 seconds
            echo "<script>setTimeout(function(){ window.location.href = 'my_bookings.php'; }, 5000);</script>";
        } catch (PDOException $e) {
            $error_message = "Error submitting payment: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Check if payment already submitted
$stmt = $pdo->prepare("SELECT * FROM payment_requests WHERE rental_id = ? AND customer_id = ? AND status = 'paid'");
$stmt->execute([$rental_id, $user_id]);
$existing_payment = $stmt->fetch();

// Decode payment details
$payment_details = json_decode($payment_request['payment_details'] ?? '{}', true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment - DriveGo Car Rental</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }
        .payment-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .payment-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .payment-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }
        .card-header h2 {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        .card-header p {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        .card-body {
            padding: 25px;
        }
        .rental-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .rental-details h3 {
            font-size: 1rem;
            margin-bottom: 15px;
            color: #333;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .amount-due {
            font-size: 1.5rem;
            font-weight: 700;
            color: #28a745;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
        }
        .form-group label .required {
            color: #dc3545;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .method-option {
            text-align: center;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .method-option:hover {
            border-color: #667eea;
        }
        .method-option.selected {
            border-color: #667eea;
            background: #f0f2ff;
        }
        .method-option i {
            font-size: 2rem;
            margin-bottom: 8px;
            display: block;
        }
        .gcash-icon {
            font-size: 2rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 40px;
        }
        .gcash-icon img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        .gcash-icon i {
            font-size: 2rem;
            color: #0066cc;
        }
        .method-option span {
            font-size: 0.85rem;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.3);
        }
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        .alert-warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: #5a6268;
        }
        .qr-section {
            text-align: center;
            padding: 20px;
        }
        .qr-placeholder {
            width: 180px;
            height: 180px;
            background: #f0f2f5;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        .qr-placeholder i {
            font-size: 4rem;
            color: #667eea;
        }
        .qr-code-container {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto 15px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background: white;
        }
        .qr-code-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .qr-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.9);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .qr-code-container:hover .qr-overlay {
            opacity: 1;
        }
        .qr-overlay i {
            font-size: 3rem;
            color: #667eea;
        }
        .amount-highlight {
            color: #28a745;
            font-weight: 600;
        }
        .payment-instructions {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            font-size: 0.85rem;
        }
        .payment-instructions h4 {
            margin-bottom: 10px;
            color: #333;
        }
        .payment-instructions ol {
            padding-left: 20px;
        }
        .payment-instructions li {
            margin: 5px 0;
        }
        .expiry-warning {
            background: #fff3cd;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 0.8rem;
            text-align: center;
        }
        @media (max-width: 768px) {
            .payment-wrapper {
                grid-template-columns: 1fr;
            }
            .payment-methods {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
<div class="payment-container">
    <a href="my_bookings.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to My Bookings</a>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($existing_payment): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Your payment has been submitted and is pending verification. You will receive a notification once verified.
        </div>
    <?php elseif ($payment_request && $payment_request['status'] === 'pending'): ?>
        
        <div class="payment-wrapper">
            <!-- Payment Instructions Column -->
            <div class="payment-card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> Payment Instructions</h2>
                    <p>Follow these steps to complete your payment</p>
                </div>
                <div class="card-body">
                    <div class="qr-section">
                        <?php
                        // QR code payment setup
                        $payment_reference = $payment_details['reference'] ?? 'DRV' . $rental_id;
                        ?>
                        
                        <div class="qr-code-container">
                            <img src="../assets/images/QR_code_payment.jpg" alt="Payment QR Code" class="qr-code-image" />
                            <div class="qr-overlay">
                                <i class="fas fa-qrcode"></i>
                            </div>
                        </div>
                        
                        <p><strong>Scan QR Code to Pay</strong></p>
                        <small>Amount: <span class="amount-highlight">₱<?= number_format($rental['total_cost'], 2) ?></span></small><br>
                        <small>Reference: <strong><?= $payment_reference ?></strong></small>
                    </div>
                    
                    <div class="payment-instructions">
                        <h4><i class="fas fa-list-ol"></i> How to Pay:</h4>
                        <?php
                        $instructions = $payment_details['instructions'] ?? '
                            <ol>
                                <li>Open your GCash or banking app</li>
                                <li>Select "Send Money" or "Pay QR"</li>
                                <li>Scan the QR code above</li>
                                <li>Enter amount: <strong>₱' . number_format($rental['total_cost'], 2) . '</strong></li>
                                <li>Enter reference: <strong>' . ($payment_details['reference'] ?? 'DRV' . $rental_id) . '</strong></li>
                                <li>Save or screenshot the transaction receipt</li>
                                <li>Upload the receipt in the form</li>
                            </ol>
                        ';
                        echo $instructions;
                        ?>
                    </div>
                    
                    <div class="expiry-warning">
                        <i class="fas fa-clock"></i> Please complete payment before 
                        <strong><?= date('F d, Y h:i A', strtotime($payment_request['expires_at'])) ?></strong>
                        to avoid cancellation.
                    </div>
                </div>
            </div>
            
            <!-- Payment Form Column -->
            <div class="payment-card">
                <div class="card-header">
                    <h2><i class="fas fa-credit-card"></i> Complete Payment</h2>
                    <p>Submit your payment details for verification</p>
                </div>
                <div class="card-body">
                    <div class="rental-details">
                        <h3><i class="fas fa-receipt"></i> Booking Summary</h3>
                        <div class="detail-row">
                            <span>Car:</span>
                            <strong><?= htmlspecialchars($rental['car_name']) ?> (<?= $rental['brand'] . ' ' . $rental['model'] ?>)</strong>
                        </div>
                        <div class="detail-row">
                            <span>Rental Period:</span>
                            <strong><?= date('M d, Y', strtotime($rental['rental_date'])) ?> → <?= date('M d, Y', strtotime($rental['return_date'])) ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>Total Days:</span>
                            <strong><?= $rental['total_days'] ?> days</strong>
                        </div>
                        <div class="detail-row">
                            <span>Amount Due:</span>
                            <span class="amount-due">₱<?= number_format($rental['total_cost'], 2) ?></span>
                        </div>
                    </div>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="submit_payment" value="1">
                        
                        <div class="form-group">
                            <label>Select Payment Method <span class="required">*</span></label>
                            <div class="payment-methods" id="paymentMethods">
                                <div class="method-option" data-method="gcash">
                                    <div class="gcash-icon">
                                        <img src="../assets/images/gcash-logo.svg" alt="GCash" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                                        <i class="fas fa-mobile-alt" style="display:none;"></i>
                                    </div>
                                    <span>GCash</span>
                                </div>

                                <div class="method-option" data-method="cash">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>Cash (Branch)</span>
                                </div>
                            </div>
                            <input type="hidden" name="payment_method" id="payment_method" required>
                        </div>
                        
                        <div class="form-group" id="referenceGroup" style="display: none;">
                            <label>Reference Number / Transaction ID <span class="required">*</span></label>
                            <input type="text" name="reference_number" placeholder="Enter reference number from your payment">
                        </div>
                        
                        <div class="form-group">
                            <label>Upload Payment Receipt/Screenshot <span class="required">*</span></label>
                            <input type="file" name="receipt" accept="image/jpeg,image/png,image/jpg,image/webp,application/pdf" required>
                            <small><i class="fas fa-info-circle"></i> Upload clear screenshot of your payment confirmation</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Additional Notes (Optional)</label>
                            <textarea name="notes" rows="3" placeholder="Any additional information about your payment..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-check-circle"></i> Submit Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> No pending payment found for this booking. Please contact support.
        </div>
    <?php endif; ?>
</div>

<script>
    const methodOptions = document.querySelectorAll('.method-option');
    const paymentMethodInput = document.getElementById('payment_method');
    const referenceGroup = document.getElementById('referenceGroup');
    const referenceInput = referenceGroup.querySelector('input');
    
    methodOptions.forEach(option => {
        option.addEventListener('click', function() {
            methodOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            const method = this.dataset.method;
            paymentMethodInput.value = method;
            
            if (method === 'cash') {
                referenceGroup.style.display = 'none';
                referenceInput.removeAttribute('required');
            } else {
                referenceGroup.style.display = 'block';
                referenceInput.setAttribute('required', 'required');
            }
        });
    });
    
    // Auto-select first method if none selected
    if (methodOptions.length > 0 && !paymentMethodInput.value) {
        methodOptions[0].click();
    }
</script>

<!-- Add notification styles if not present -->
<style>
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
</style>
</body>
</html>