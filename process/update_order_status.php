<?php
// process/update_order_status.php
session_start();
include __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit();
}

$order_id   = (int)($_POST['order_id']   ?? 0);
$new_status = strtoupper(trim($_POST['new_status'] ?? ''));

$allowed = ['PROCESSING','READY_FOR_PICKUP','FOR_DELIVERY','COMPLETED','REJECTED','CANCELLED'];

if (!$order_id || !in_array($new_status, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit();
}

$stmt = $conn->prepare("UPDATE tbl_orders SET order_status = ? WHERE order_id = ?");
$stmt->bind_param("si", $new_status, $order_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
}