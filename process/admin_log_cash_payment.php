<?php
// process/admin_log_cash_payment.php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

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

    // 1. Insert the cash payment as 'Verified' immediately (since admin received it in person)
    $stmt = $conn->prepare("INSERT INTO tbl_payment_proof_uploads (payment_id, payment_proof, uploaded_amount, verification_status) VALUES (?, 'Admin: Walk-in Cash Payment', ?, 'Verified')");
    $stmt->bind_param("id", $payment_id, $amount);
    $stmt->execute();
    $stmt->close();

    // 2. Recalculate the total verified payments for this order
    $stmt = $conn->prepare("SELECT SUM(uploaded_amount) as total_paid FROM tbl_payment_proof_uploads WHERE payment_id = ? AND verification_status = 'Verified'");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $paid_result = $stmt->get_result()->fetch_assoc();
    $total_paid = (float)($paid_result['total_paid'] ?? 0);
    $stmt->close();

    // 3. Get the required total amount AND the order_id (Modified to get order_id)
    $stmt = $conn->prepare("SELECT total_amount, order_id FROM tbl_payment WHERE payment_id = ?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $payment_row = $stmt->get_result()->fetch_assoc();
    $total_required = (float)($payment_row['total_amount'] ?? 0);
    $order_id = (int)($payment_row['order_id'] ?? 0);
    $stmt->close();

    // 4. Update the payment status (PARTIAL or FULL)
    $new_status = ($total_paid >= $total_required) ? 'FULL' : 'PARTIAL';
    $stmt = $conn->prepare("UPDATE tbl_payment SET payment_status = ? WHERE payment_id = ?");
    $stmt->bind_param("si", $new_status, $payment_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    // ========================================================================
    // --- NOTIFICATION TRIGGER: CASH PAYMENT LOGGED ---
    // ========================================================================
    require_once __DIR__ . '/../classes/Notification/NotificationService.php';
    $notifService = new NotificationService($conn);

    if ($order_id > 0) {
        $stmtC = $conn->prepare("SELECT customer_id FROM tbl_orders WHERE order_id = ?");
        $stmtC->bind_param("i", $order_id);
        $stmtC->execute();
        $resC = $stmtC->get_result()->fetch_assoc();
        
        if ($resC && isset($resC['customer_id'])) {
            $formatted_amount = number_format($amount, 2);
            $notifService->notifyCustomer(
                $resC['customer_id'], 
                $order_id, 
                "Payment Received ", 
                "We have successfully recorded your cash payment of ₱{$formatted_amount} for Order #{$order_id}."
            );
        }
        $stmtC->close();
    }
    // ========================================================================

    echo json_encode(['success' => true, 'message' => 'Cash payment logged successfully.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>