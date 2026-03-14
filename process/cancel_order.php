<?php
// process/cancel_order.php
session_start();
include __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$customer_id = (int)$_SESSION['user_id'];
$order_id    = (int)($_POST['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
    exit();
}

// Verify the order belongs to this customer AND is still PENDING
$stmt = $conn->prepare("
    SELECT order_id, order_status
    FROM tbl_orders
    WHERE order_id = ? AND customer_id = ?
");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found.']);
    exit();
}

if ($order['order_status'] !== 'PENDING') {
    echo json_encode(['success' => false, 'message' => 'This order can no longer be cancelled.']);
    exit();
}

// Update status to CANCELLED
$update = $conn->prepare("
    UPDATE tbl_orders SET order_status = 'CANCELLED' WHERE order_id = ?
");
$update->bind_param("i", $order_id);

if ($update->execute()) {
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cancel order. Please try again.']);
}
exit();