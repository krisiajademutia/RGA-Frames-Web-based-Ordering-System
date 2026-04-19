<?php
// process/cancel_order.php
session_start();
include __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Notification/NotificationRepository.php';
require_once __DIR__ . '/../classes/Notification/NotificationService.php';
require_once __DIR__ . '/../classes/Order/Repository/OrderRepository.php';
require_once __DIR__ . '/../classes/Order/Repository/OrderItemRepository.php';
require_once __DIR__ . '/../classes/Order/OrderService.php';


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

$orderRepo = new OrderRepository($conn);
$orderService = new OrderService($conn);

$order = $orderRepo->getOrderById($order_id);

if (!$order || (int)$order['customer_id'] !== $customer_id) {
    echo json_encode(['success' => false, 'message' => 'Order not found.']);
    exit();
}

if ($order['order_status'] !== 'PENDING') {
    echo json_encode(['success' => false, 'message' => 'This order can no longer be cancelled.']);
    exit();
}

$ref_no = $order['order_reference_no'] ?? "#" . $order_id;

$success = $orderService->changeOrderStatus($order_id, 'CANCELLED');

if ($success) {
    // --- NOTIFICATION TRIGGER: CUSTOMER CANCELLED ---
    $notifRepo = new NotificationRepository($conn);
    $notifService = new NotificationService($notifRepo);
    $notifService->notifyAdmin($order_id, "Order Cancelled", "Order ($ref_no) has been cancelled by the customer.");
    
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cancel order. Please try again.']);
}
exit();