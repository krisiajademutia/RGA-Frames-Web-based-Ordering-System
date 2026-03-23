<?php
// process/printing_process.php
session_start();
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Printing/PrintingService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in.']);
    exit;
}

$customerId = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$imageFile = $_FILES['image'] ?? null;

$target_dir = "../uploads/customer_print/";

$service = new PrintingService($conn);

try {
    if ($action === 'add_to_cart') {
        echo json_encode($service->addToCart($customerId, $_POST, $imageFile, $target_dir));
    } 
    elseif ($action === 'buy_now') {
        echo json_encode($service->processBuyNow($_POST, $imageFile, $target_dir));
    } 
    else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}