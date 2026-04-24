<?php
class NotificationHelper {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Send notification to specific user
    public function sendNotification($user_id, $user_role, $title, $message, $type, $link = null) {
        $stmt = $this->pdo->prepare("INSERT INTO notifications (user_id, user_role, title, message, type, link, created_at) 
                                      VALUES (?, ?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([$user_id, $user_role, $title, $message, $type, $link]);
    }
    
    // Send notification to all admins
    public function notifyAllAdmins($title, $message, $type, $link = null) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE role = 'admin'");
        $stmt->execute();
        $admins = $stmt->fetchAll();
        
        foreach ($admins as $admin) {
            $this->sendNotification($admin['id'], 'admin', $title, $message, $type, $link);
        }
    }
    
    // Send notification to all workers
    public function notifyAllWorkers($title, $message, $type, $link = null) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE role = 'worker'");
        $stmt->execute();
        $workers = $stmt->fetchAll();
        
        foreach ($workers as $worker) {
            $this->sendNotification($worker['id'], 'worker', $title, $message, $type, $link);
        }
    }
    
    // Send notification to all staff (admin + worker)
    public function notifyAllStaff($title, $message, $type, $link = null) {
        $this->notifyAllAdmins($title, $message, $type, $link);
        $this->notifyAllWorkers($title, $message, $type, $link);
    }
    
    // Send notification to customer
    public function notifyCustomer($user_id, $title, $message, $type, $link = null) {
        return $this->sendNotification($user_id, 'customer', $title, $message, $type, $link);
    }
    
    // Get unread count for user
    public function getUnreadCount($user_id, $user_role) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND user_role = ? AND is_read = 0");
        $stmt->execute([$user_id, $user_role]);
        return $stmt->fetchColumn();
    }
    
    // Get all notifications for user
    public function getNotifications($user_id, $user_role, $limit = 20) {
        $stmt = $this->pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND user_role = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$user_id, $user_role, $limit]);
        return $stmt->fetchAll();
    }
    
    // Mark notification as read
    public function markAsRead($notification_id, $user_id) {
        $stmt = $this->pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        return $stmt->execute([$notification_id, $user_id]);
    }
}
?>