<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

try {
    // Get notifications for the user
    $stmt = $pdo->prepare("SELECT * FROM notifications 
                           WHERE user_id = ? AND user_role = ? 
                           ORDER BY created_at DESC 
                           LIMIT 20");
    $stmt->execute([$user_id, $user_role]);
    $notifications = $stmt->fetchAll();

    // Get unread count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications 
                          WHERE user_id = ? AND user_role = ? AND is_read = 0");
    $stmt->execute([$user_id, $user_role]);
    $unread_count = $stmt->fetchColumn();

    // Format notifications with time ago
    foreach ($notifications as &$notif) {
        $notif['time_ago'] = getTimeAgo($notif['created_at']);
    }

    echo json_encode([
        'notifications' => $notifications,
        'unread_count' => $unread_count
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}

function getTimeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('M d, Y', $time);
    }
}
?>
