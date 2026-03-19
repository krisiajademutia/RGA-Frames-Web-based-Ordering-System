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

// Update order status
$stmt = $conn->prepare("UPDATE tbl_orders SET order_status = ? WHERE order_id = ?");
$stmt->bind_param("si", $new_status, $order_id);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
    exit();
}

// If completed — mark payment as FULL
if ($new_status === 'COMPLETED') {
    $stmtFull = $conn->prepare("UPDATE tbl_payment SET payment_status = 'FULL' WHERE order_id = ?");
    $stmtFull->bind_param("i", $order_id);
    $stmtFull->execute();
}

// If accepted (PROCESSING) — decrement stock for every ready-made item in this order
if ($new_status === 'PROCESSING') {
    $stmtStock = $conn->prepare("
        UPDATE tbl_ready_made_product_stocks s
        JOIN tbl_frame_order_items i ON s.r_product_id = i.r_product_id
        SET s.quantity = GREATEST(0, s.quantity - i.quantity)
        WHERE i.order_id = ? AND i.frame_category = 'READY_MADE'
    ");
    $stmtStock->bind_param("i", $order_id);
    $stmtStock->execute();
}
echo json_encode(['success' => true]);