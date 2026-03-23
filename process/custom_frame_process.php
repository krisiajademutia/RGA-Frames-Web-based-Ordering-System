<?php
// process/custom_frame_process.php
session_start();
include __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/CustomFrame/CustomFrameService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$customerId = (int)$_SESSION['user_id'];
$action     = $_POST['action'] ?? '';

if (!in_array($action, ['add_to_cart', 'buy_now'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit();
}

// Validate
$errors        = [];
$serviceType   = $_POST['service_type']   ?? '';
$frameTypeId   = $_POST['frame_type_id']   ?? null;
$frameDesignId = $_POST['frame_design_id'] ?? null;
$frameColorId  = $_POST['frame_color_id']  ?? null;
$frameSizeId   = $_POST['frame_size_id']   ?? 'OTHER';
$customWidth   = (float)($_POST['width']  ?? 0);
$customHeight  = (float)($_POST['height'] ?? 0);
$primaryMat    = $_POST['primary_matboard']   ?? 0;
$secondaryMat  = $_POST['secondary_matboard'] ?? 0;
$mountTypeId   = $_POST['mount_type_id']  ?? null;
$paperTypeId   = $_POST['paper_type_id']  ?? null;
$quantity      = max(1, (int)($_POST['quantity'] ?? 1));

if (empty($serviceType) || !in_array($serviceType, ['FRAME_ONLY', 'FRAME&PRINT']))
    $errors[] = 'Please select a service type.';
if (empty($frameTypeId))   $errors[] = 'Please select a frame type.';
if (empty($frameDesignId)) $errors[] = 'Please select a frame design.';
if (empty($frameColorId))  $errors[] = 'Please select a frame color.';
if ($customWidth <= 0 || $customHeight <= 0) $errors[] = 'Please enter a valid frame size.';
if (empty($mountTypeId))   $errors[] = 'Please select a mount type.';
if ($serviceType === 'FRAME&PRINT') {
    if (empty($paperTypeId)) $errors[] = 'Please select a paper type.';
    if (empty($_FILES['customer_image']['name'])) $errors[] = 'Please upload your image.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit();
}

// Handle image upload
$imagePath = '';
if ($serviceType === 'FRAME&PRINT' && !empty($_FILES['customer_image']['name'])) {
    $uploadDir = __DIR__ . '/../uploads/customer_print/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $ext = strtolower(pathinfo($_FILES['customer_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid image format.']);
        exit();
    }
    if ($_FILES['customer_image']['size'] > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Image too large. Max 10MB.']);
        exit();
    }
    $fileName = 'CUSTOM_PRINT_' . $customerId . '_' . time() . '.' . $ext;
    if (!move_uploaded_file($_FILES['customer_image']['tmp_name'], $uploadDir . $fileName)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
        exit();
    }
    $imagePath = 'uploads/customer_print/' . $fileName;
}

$data = [
    'service_type'          => $serviceType,
    'frame_type_id'         => $frameTypeId,
    'frame_design_id'       => $frameDesignId,
    'frame_color_id'        => $frameColorId,
    'frame_size_id'         => $frameSizeId,
    'width'                 => $customWidth,
    'height'         => $customHeight,
    'primary_matboard_id'   => $primaryMat,
    'secondary_matboard_id' => $secondaryMat,
    'mount_type_id'         => $mountTypeId,
    'paper_type_id'         => $paperTypeId,
    'image_path'            => $imagePath,
    'quantity'              => $quantity,
    'base_price'            => isset($_POST['base_price']) ? (float)$_POST['base_price'] : 0,
    'extra_price'           => isset($_POST['extra_price']) ? (float)$_POST['extra_price'] : 0,
    'print_price'           => isset($_POST['print_price']) ? (float)$_POST['print_price'] : 0,
    'sub_total'             => isset($_POST['sub_total']) ? (float)$_POST['sub_total'] : 0,
];

$service = new CustomFrameService($conn);

if ($action === 'add_to_cart') {
    // Save to cart — customer goes to checkout later
    $result = $service->addToCart($customerId, $data);
    echo json_encode($result);

} else {
    // Buy Now — save item data in SESSION, redirect to checkout
    // Checkout will read from session instead of cart
    $_SESSION['buy_now_item'] = $data;
    echo json_encode(['success' => true, 'message' => 'Ready for checkout.']);
}
exit();