<?php
// process/mark_customer_notif_read.php
session_start();
include __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    echo json_encode(['success' => false]);
    exit();
}

$customer_id = (int)$_SESSION['user_id'];

// Update all unread notifications for THIS customer to "read"
$stmt = $conn->prepare("UPDATE tbl_notifications SET is_read = 1 WHERE customer_id = ? AND is_read = 0");
$stmt->bind_param("i", $customer_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
$stmt->close();
?>