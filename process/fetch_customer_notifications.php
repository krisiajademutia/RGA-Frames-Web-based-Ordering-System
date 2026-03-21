<?php
// process/fetch_customer_notifications.php
session_start();
include __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

// Security check: Only Customers can fetch these
if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$customer_id = (int)$_SESSION['user_id'];

// 1. Get the count of UNREAD notifications for THIS specific customer
$countStmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM tbl_notifications WHERE customer_id = ? AND is_read = 0");
$countStmt->bind_param("i", $customer_id);
$countStmt->execute();
$countResult = $countStmt->get_result()->fetch_assoc();
$unreadCount = $countResult['unread_count'] ?? 0;
$countStmt->close();

// 2. Get the 10 most recent notifications for THIS customer
$notifStmt = $conn->prepare("SELECT * FROM tbl_notifications WHERE customer_id = ? ORDER BY notification_id DESC LIMIT 10");
$notifStmt->bind_param("i", $customer_id);
$notifStmt->execute();
$result = $notifStmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$notifStmt->close();

echo json_encode([
    'success' => true,
    'unread_count' => $unreadCount,
    'notifications' => $notifications
]);
?>