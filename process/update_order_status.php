<?php
// process/update_order_status.php
ob_start();                    // ← IMPORTANT for clean JSON
session_start();
include __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Notification/NotificationRepository.php';
require_once __DIR__ . '/../classes/Notification/NotificationService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'ADMIN') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit();
}

$order_id   = (int)($_POST['order_id']   ?? 0);
$new_status = strtoupper(trim($_POST['new_status'] ?? ''));

$allowed = ['PROCESSING','READY_FOR_PICKUP','FOR_DELIVERY','COMPLETED','REJECTED','CANCELLED'];

if (!$order_id || !in_array($new_status, $allowed)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit();
}

try {
    // Update order status
    $stmt = $conn->prepare("UPDATE tbl_orders SET order_status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $new_status, $order_id);

    if (!$stmt->execute()) {
        throw new Exception('DB error updating status: ' . $conn->error);
    }

// Check balance before allowing COMPLETED status
if ($new_status === 'COMPLETED') {
    $check_stmt = $conn->prepare("
        SELECT 
            o.total_price,
            COALESCE(SUM(pu.uploaded_amount), 0) AS paid_amount
        FROM tbl_orders o
        LEFT JOIN tbl_payment p ON o.order_id = p.order_id
        LEFT JOIN tbl_payment_proof_uploads pu 
               ON p.payment_id = pu.payment_id 
              AND pu.verification_status = 'Verified'
        WHERE o.order_id = ?
        GROUP BY o.order_id, o.total_price
    ");
    $check_stmt->bind_param("i", $order_id);
    $check_stmt->execute();
    $res = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();
    
    if ($res) {
        $total = (float)$res['total_price'];
        $paid  = (float)$res['paid_amount'];
        $balance = $total - $paid;

        if (round($balance, 2) > 0) {
            ob_clean();
            echo json_encode([
                'success' => false, 
                'message' => 'Cannot complete order. There is an unpaid balance of ₱' . number_format($balance, 2)
            ]);
            exit();
        }
    }
}

    // Stock decrement for PROCESSING (READY_MADE only)
    if ($new_status === 'PROCESSING') {
        $stmtStock = $conn->prepare("
            UPDATE tbl_ready_made_product_stocks s
            JOIN tbl_frame_order_items i ON s.r_product_id = i.r_product_id
            SET s.quantity = GREATEST(0, s.quantity - i.quantity)
            WHERE i.order_id = ? AND i.frame_category = 'READY_MADE'
        ");
        if ($stmtStock) {
            $stmtStock->bind_param("i", $order_id);
            $stmtStock->execute();
        }
    }

    // --- NOTIFICATION ---
    $notifRepo = new NotificationRepository($conn);
    $notifService = new NotificationService($notifRepo);

    $stmtC = $conn->prepare("SELECT customer_id, order_reference_no FROM tbl_orders WHERE order_id = ?");
    $stmtC->bind_param("i", $order_id);
    $stmtC->execute();
    $resC = $stmtC->get_result()->fetch_assoc();
    $stmtC->close();

    if ($resC) {
        $customer_id = $resC['customer_id'];
        $ref_no = $resC['order_reference_no'] ?? "#" . $order_id;

        $status_messages = [
            'PROCESSING'       => ['Order Accepted!', "Your order ($ref_no) has been accepted and is now processing."],
            'READY_FOR_PICKUP' => ['Ready for Pick-up!', "Your order ($ref_no) is ready! Please visit the store to claim it."],
            'FOR_DELIVERY'     => ['Out for Delivery!', "Your order ($ref_no) is on its way to you!"],
            'COMPLETED'        => ['Order Completed', "Your order ($ref_no) is complete. Thank you for choosing RGA Frames!"],
            'REJECTED'         => ['Order Update', "Unfortunately, your order ($ref_no) was rejected. Please contact us for details."]
        ];

        if (isset($status_messages[$new_status])) {
            $notifService->notifyCustomer($customer_id, $order_id, $status_messages[$new_status][0], $status_messages[$new_status][1]);
        }
    }

    ob_clean();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    ob_clean();
    error_log("Update Order Status Error (Order #$order_id): " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>  