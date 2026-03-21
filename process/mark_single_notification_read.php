<?php
// process/mark_single_notification_read.php
session_start();
include __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit();
}

$notif_id = (int)($_GET['notif_id'] ?? 0);

if ($notif_id <= 0) {
    echo json_encode(['success' => false]);
    exit();
}

// Optional: add security check — make sure this notification belongs to current user/admin
$role = strtoupper($_SESSION['role'] ?? '');
$where = ($role === 'ADMIN') 
    ? "customer_id IS NULL AND notification_id = ?"
    : "customer_id = ? AND notification_id = ?";

$stmt = $conn->prepare("UPDATE tbl_notifications SET is_read = 1 WHERE $where");

if ($role === 'ADMIN') {
    $stmt->bind_param("i", $notif_id);
} else {
    $customer_id = (int)$_SESSION['user_id'];
    $stmt->bind_param("ii", $customer_id, $notif_id);
}

$success = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $success]);
?>