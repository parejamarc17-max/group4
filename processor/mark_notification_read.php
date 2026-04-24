<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['error' => 'Notification ID required']);
    exit;
}

$notification_id = $data['id'];
$user_id = $_SESSION['user_id'];

try {
    // Mark notification as read
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 
                           WHERE id = ? AND user_id = ?");
    $stmt->execute([$notification_id, $user_id]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
