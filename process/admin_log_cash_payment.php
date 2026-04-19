<?php
// process/admin_log_cash_payment.php
session_start();
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Order/Repository/OrderRepository.php';
require_once __DIR__ . '/../classes/Order/Repository/OrderItemRepository.php';
require_once __DIR__ . '/../classes/Order/OrderService.php';

header('Content-Type: application/json');

// Check admin login
if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Validate input
$payment_id = isset($_POST['payment_id']) ? (int)$_POST['payment_id'] : 0;
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;

if ($payment_id <= 0 || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment ID or amount.']);
    exit();
}

try {
    $conn->begin_transaction();

    $orderService = new OrderService($conn);
    $order_id = $orderService->logCashPayment($payment_id, $amount);

    $conn->commit();

    // ========================================================================
    // --- NOTIFICATION TRIGGER: CASH PAYMENT LOGGED ---
    // ========================================================================
    require_once __DIR__ . '/../classes/Notification/NotificationRepository.php';
    require_once __DIR__ . '/../classes/Notification/NotificationService.php';
    $notifRepo = new NotificationRepository($conn);
    $notifService = new NotificationService($notifRepo);

    if ($order_id > 0) {
        $orderRepo = new OrderRepository($conn);
        $resC = $orderRepo->getCustomerReference($order_id);
        
        if ($resC && isset($resC['customer_id'])) {
            $formatted_amount = number_format($amount, 2);
            $notifService->notifyCustomer(
                $resC['customer_id'], 
                $order_id, 
                "Payment Received", 
                "We have successfully recorded your cash payment of ₱{$formatted_amount} for Order #{$order_id}."
            );
        }
    }
    // ========================================================================

    echo json_encode(['success' => true, 'message' => 'Cash payment logged successfully.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>