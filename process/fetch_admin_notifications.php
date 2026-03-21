<?php
// process/fetch_admin_notifications.php
session_start();
include __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

// Security check: Only Admins can fetch these
if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// 1. Get the count of UNREAD notifications for the admin
// (Admin notifications have customer_id as NULL)
$countStmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM tbl_notifications WHERE customer_id IS NULL AND is_read = 0");
$countStmt->execute();
$countResult = $countStmt->get_result()->fetch_assoc();
$unreadCount = $countResult['unread_count'] ?? 0;
$countStmt->close();

// 2. Get the 10 most recent notifications (both read and unread)
$notifStmt = $conn->prepare("SELECT * FROM tbl_notifications WHERE customer_id IS NULL ORDER BY notification_id DESC LIMIT 10");
$notifStmt->execute();
$result = $notifStmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$notifStmt->close();

// Send it all back to the frontend!
echo json_encode([
    'success' => true,
    'unread_count' => $unreadCount,
    'notifications' => $notifications
]);
?>