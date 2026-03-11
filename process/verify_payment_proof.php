<?php
// process/verify_payment_proof.php
session_start();
include __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Order/OrderService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit();
}

$upload_id  = (int)($_POST['upload_id']  ?? 0);
$payment_id = (int)($_POST['payment_id'] ?? 0);
$action     = $_POST['action'] ?? 'verify';

if (!$upload_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid upload ID.']);
    exit();
}

$service = new OrderService($conn);

if ($action === 'reject') {
    $result = $service->rejectProof($upload_id);
} else {
    if (!$payment_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid payment ID.']);
        exit();
    }
    $result = $service->verifyProof($upload_id, $payment_id);
}

echo json_encode(['success' => (bool)$result]);