<?php
// process/mark_admin_notif_read.php
session_start();
include __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'ADMIN') {
    echo json_encode(['success' => false]);
    exit();
}

// Update all unread admin notifications to "read"
$stmt = $conn->prepare("UPDATE tbl_notifications SET is_read = 1 WHERE customer_id IS NULL AND is_read = 0");
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
$stmt->close();
?>