<?php
class PaymentHelper {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function createPaymentRequest($rental_id, $amount, $customer_id, $payment_method = null) {
        // Generate unique reference
        $reference = 'DRV' . time() . rand(100, 999);
        
        // Set expiration (3 days from now)
        $expires_at = date('Y-m-d H:i:s', strtotime('+3 days'));
        
        // Generate QR code data (for GCash/PayMaya)
        $qr_data = $this->generateQRData($amount, $reference);
        
        $stmt = $this->pdo->prepare("INSERT INTO payment_requests (rental_id, customer_id, amount, payment_method, payment_details, qr_code, status, expires_at, sent_at) 
                                      VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())");
        
        $payment_details = json_encode([
            'reference' => $reference,
            'instructions' => $this->getPaymentInstructions($payment_method),
            'account_name' => 'DriveGo Car Rental',
            'account_number' => '1234-5678-9012'
        ]);
        
        return $stmt->execute([$rental_id, $customer_id, $amount, $payment_method, $payment_details, $qr_data, $expires_at]);
    }
    
    private function generateQRData($amount, $reference) {
        // For GCash - generate payment data
        // In production, integrate with GCash/PayMaya API
        return base64_encode(json_encode([
            'amount' => $amount,
            'reference' => $reference,
            'merchant' => 'DriveGo Rentals'
        ]));
    }
    
    private function getPaymentInstructions($method) {
        $instructions = [
            'gcash' => '1. Open GCash app<br>2. Click "Pay QR"<br>3. Scan the QR code<br>4. Enter amount and reference number<br>5. Send screenshot as proof',
            'bank_transfer' => '1. Transfer to our bank account<br>2. Use reference number as description<br>3. Upload receipt<br>4. Wait for verification',
            'cash' => '1. Visit any of our branches<br>2. Present your booking reference<br>3. Pay the exact amount'
        ];
        
        return $instructions[$method] ?? $instructions['bank_transfer'];
    }
}
?>