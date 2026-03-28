<?php
// process/verify_payment_proof.php
ob_start();                    // ← THIS WAS MISSING
session_start();
include __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Order/OrderService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'ADMIN') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit();
}

$upload_id  = (int)($_POST['upload_id']  ?? 0);
$payment_id = (int)($_POST['payment_id'] ?? 0);
$action     = $_POST['action'] ?? 'verify';

if (!$upload_id) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid upload ID.']);
    exit();
}

$service = new OrderService($conn);

try {
    if ($action === 'reject') {
        $result = $service->rejectProof($upload_id);
    } else {
        if (!$payment_id) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Invalid payment ID.']);
            exit();
        }
        $result = $service->verifyProof($upload_id, $payment_id);
    }

    ob_clean();
    echo json_encode([
        'success' => (bool)$result,
        'message' => $result ? 'Success' : 'Failed to update proof'
    ]);
} catch (Exception $e) {
    ob_clean();
    error_log("Verify Proof Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>