<?php
// process/ready_made_process.php
ob_start();
session_start();
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/ReadyMade/Repository/ReadyMadeRepository.php';
require_once __DIR__ . '/../classes/ReadyMade/ReadyMadeService.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Please log in first.']); exit();
}

$customerId = (int)$_SESSION['user_id'];
$action     = $_POST['action'] ?? '';

$r_product_id          = (int)($_POST['r_product_id'] ?? 0);
$quantity              = (int)($_POST['quantity'] ?? 1);

// Bulletproof check: If the word "PRINT" is anywhere in the form data, force it to FRAME&PRINT
$raw_service           = strtoupper($_POST['service_type'] ?? '');
$service_type          = str_contains($raw_service, 'PRINT') ? 'FRAME&PRINT' : 'FRAME_ONLY';

$primary_matboard_id   = !empty($_POST['primary_matboard_id']) && $_POST['primary_matboard_id'] !== 'None' ? (int)$_POST['primary_matboard_id'] : null;
$secondary_matboard_id = !empty($_POST['secondary_matboard_id']) && $_POST['secondary_matboard_id'] !== 'None' ? (int)$_POST['secondary_matboard_id'] : null;
$mount_type_id         = !empty($_POST['mount_type_id']) ? (int)$_POST['mount_type_id'] : null;
$paper_type_id         = !empty($_POST['paper_type_id']) ? (int)$_POST['paper_type_id'] : null;
$printing_order_item_id = null; 

if ($r_product_id < 1 || $quantity < 1) {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Invalid product or quantity.']); exit();
}

$repo    = new \Classes\ReadyMade\Repository\ReadyMadeRepository($conn);
$service = new \Classes\ReadyMade\ReadyMadeService($repo);

$product = $service->getFrameById($r_product_id);
if (!$product) { ob_clean(); echo json_encode(['success' => false, 'message' => 'Product not found.']); exit(); }

$base_price  = (float)$product['product_price'];
$extra_price = 0;

if (!empty($primary_matboard_id) && !empty($secondary_matboard_id)) {
    $extra_price += $service->getMatboardColorPrice($secondary_matboard_id);
}

if (!empty($mount_type_id)) {
    $extra_price += $service->getMountTypeFee($mount_type_id);
}

$print_subtotal = 0; 

// --- HANDLE PRINTING ORDER LINKING ---
if ($service_type === 'FRAME&PRINT') {
    if (!$paper_type_id) { ob_clean(); echo json_encode(['success' => false, 'message' => 'Please select a paper type.']); exit(); }
    
    if (!isset($_FILES['print_image']) || $_FILES['print_image']['error'] !== UPLOAD_ERR_OK) {
        ob_clean(); echo json_encode(['success' => false, 'message' => 'Please upload an image for printing.']); exit();
    }
    
    $file      = $_FILES['print_image'];
    $allowed   = ['image/jpeg', 'image/png', 'image/gif'];
    
    $targetDir = __DIR__ . '/../uploads/customer_print/';
    if (!is_dir($targetDir)) { mkdir($targetDir, 0777, true); }
    
    $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFile   = "READY_MADE_PRINT_" . uniqid() . "." . $ext;
    $targetFile = $targetDir . $newFile;
    
    if (!in_array($file['type'], $allowed) || $file['size'] > 5000000) { 
        ob_clean(); echo json_encode(['success' => false, 'message' => 'Invalid image type or size (limit 5MB).']); exit();
    }
    
    if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
        ob_clean(); echo json_encode(['success' => false, 'message' => 'Failed to save the image.']); exit();
    }
    
    $multiplier = $service->getPaperTypeMultiplier($paper_type_id);
    $print_price = ( (float)$product['width'] * (float)$product['height'] ) * $multiplier;
    $print_subtotal = $print_price * $quantity;

    $conn->begin_transaction();
    try {
        $getCart = $conn->prepare("SELECT cart_id FROM tbl_cart WHERE customer_id = ?");
        $getCart->bind_param('i', $customerId); $getCart->execute();
        $cartRes = $getCart->get_result()->fetch_assoc();
        $cartId = $cartRes ? $cartRes['cart_id'] : null;

        if (!$cartId) {
            $create = $conn->prepare("INSERT INTO tbl_cart (customer_id) VALUES (?)");
            $create->bind_param('i', $customerId); $create->execute();
            $cartId = $conn->insert_id;
        }

        $insPrint = $conn->prepare("
            INSERT INTO tbl_printing_order_items 
            (cart_id, paper_type_id, image_path, width_inch, height_inch, quantity, sub_total)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        // FIXED: Added "uploads/" so it maps perfectly to your database expectations
        $imagePathForDB = "uploads/customer_print/" . $newFile;
        
        $insPrint->bind_param('iisssid', $cartId, $paper_type_id, $imagePathForDB, $product['width'], $product['height'], $quantity, $print_subtotal);
        if (!$insPrint->execute()) { throw new Exception("DB error: " . $conn->error); }
        $printing_order_item_id = $conn->insert_id; 
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback(); if (file_exists($targetFile)) unlink($targetFile);
        ob_clean(); echo json_encode(['success' => false, 'message' => 'Internal error: ' . $e->getMessage()]); exit();
    }
}

$sub_total = ($base_price + $extra_price) * $quantity; 
$total_price = $sub_total + $print_subtotal; 

$itemData = [
    'r_product_id'          => $r_product_id,
    'quantity'              => $quantity,
    'service_type'          => $service_type,
    'primary_matboard_id'   => $primary_matboard_id,
    'secondary_matboard_id' => $secondary_matboard_id,
    'mount_type_id'         => $mount_type_id,
    'printing_order_item_id' => $printing_order_item_id, 
    'base_price'            => $base_price,
    'extra_price'           => $extra_price,
    'sub_total'             => $sub_total,
    'total_price'           => $total_price 
];

if ($action === 'add_to_cart') {
    $conn->begin_transaction();
    try {
        if (!isset($cartId)) { 
            $getCart = $conn->prepare("SELECT cart_id FROM tbl_cart WHERE customer_id = ? LIMIT 1");
            $getCart->bind_param('i', $customerId); $getCart->execute();
            $row = $getCart->get_result()->fetch_assoc();
            if ($row) { $cartId = (int)$row['cart_id']; }
            else {
                $ins = $conn->prepare("INSERT INTO tbl_cart (customer_id) VALUES (?)");
                $ins->bind_param('i', $customerId); $ins->execute(); $cartId = (int)$conn->insert_id;
            }
        }
        
        $result = $service->addToCart($customerId, $itemData, $cartId);
        if (!$result['success']) { throw new Exception($result['message']); }
        $conn->commit();
        ob_clean(); echo json_encode($result); exit();
    } catch (Exception $e) {
        $conn->rollback(); ob_clean(); echo json_encode(['success' => false, 'message' => 'Internal error: ' . $e->getMessage()]); exit();
    }

} elseif ($action === 'buy_now') {
    $result = $service->buildBuyNowPayload($itemData);
    if ($result['success']) {
        $_SESSION['buy_now_item'] = $result['payload'];
        $result['redirect'] = 'customer_checkout.php'; 
    }
    ob_clean(); echo json_encode($result); exit();
}

ob_clean(); echo json_encode(['success' => false, 'message' => 'Invalid action.']); exit();
?>