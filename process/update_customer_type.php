<?php
// process/update_customer_type.php
session_start();
include __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$customer_id   = (int)($_POST['customer_id']   ?? 0);
$customer_type = strtoupper(trim($_POST['customer_type'] ?? ''));

if (!$customer_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid customer.']);
    exit();
}
if (!in_array($customer_type, ['REGULAR', 'PHOTOGRAPHER'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid customer type.']);
    exit();
}

$stmt = $conn->prepare("UPDATE tbl_customer SET customer_type = ? WHERE customer_id = ?");
$stmt->bind_param("si", $customer_type, $customer_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $label = $customer_type === 'PHOTOGRAPHER' ? 'Photographer' : 'Regular';
    echo json_encode(['success' => true, 'message' => "Customer type updated to $label successfully."]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update. Customer not found.']);
}
exit();