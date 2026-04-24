<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is worker
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    echo json_encode(['error' => 'Access denied']);
    exit;
}

try {
    // Get pending approvals count
    $stmt = $pdo->query("SELECT COUNT(*) FROM rentals WHERE approval_status = 'pending'");
    $pending_approvals = $stmt->fetchColumn();

    // Get pending verifications count (payments that need verification)
    $stmt = $pdo->query("SELECT COUNT(*) FROM payment_requests WHERE status = 'paid'");
    $pending_verifications = $stmt->fetchColumn();

    // Get other stats
    $stmt = $pdo->query("SELECT COUNT(*) FROM car WHERE status = 'available'");
    $available_cars = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM rentals WHERE status = 'active'");
    $active_rentals = $stmt->fetchColumn();

    echo json_encode([
        'pending_approvals' => $pending_approvals,
        'pending_verifications' => $pending_verifications,
        'available_cars' => $available_cars,
        'active_rentals' => $active_rentals
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
