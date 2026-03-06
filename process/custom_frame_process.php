<?php
// process/custom_frame_process.php

session_start();
include __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/CustomFrame/CustomFrameService.php';

// ── Auth check ───────────────────────────────────────────
if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit();
}

// ── Only accept POST ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$customerId = (int)$_SESSION['user_id'];
$action     = $_POST['action'] ?? ''; // 'add_to_cart' or 'buy_now'

if (!in_array($action, ['add_to_cart', 'buy_now'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit();
}

// ── Validate required fields ─────────────────────────────
$errors = [];

$serviceType      = $_POST['service_type']         ?? '';
$frameTypeId      = $_POST['frame_type_id']         ?? null;
$frameDesignId    = $_POST['frame_design_id']        ?? null;
$frameColorId     = $_POST['frame_color_id']         ?? null;
$frameSizeId      = $_POST['frame_size_id']          ?? 'OTHER';
$customWidth      = (float)($_POST['custom_width']   ?? 0);
$customHeight     = (float)($_POST['custom_height']  ?? 0);
$primaryMatboard  = $_POST['primary_matboard']        ?? 0;
$secondaryMatboard= $_POST['secondary_matboard']      ?? 0;
$mountTypeId      = $_POST['mount_type_id']           ?? null;
$paperTypeId      = $_POST['paper_type_id']            ?? null;
$quantity         = max(1, (int)($_POST['quantity']    ?? 1));
$paymentMethod    = strtoupper($_POST['payment_method']    ?? 'CASH');
$deliveryOption   = strtoupper($_POST['delivery_option']   ?? 'PICKUP');
$deliveryAddress  = trim($_POST['delivery_address']         ?? '');

// Required for all
if (empty($serviceType) || !in_array($serviceType, ['FRAME_ONLY', 'FRAME&PRINT'])) {
    $errors[] = 'Please select a service type.';
}
if (empty($frameTypeId)) {
    $errors[] = 'Please select a frame type.';
}
if (empty($frameDesignId)) {
    $errors[] = 'Please select a frame design.';
}
if (empty($frameColorId)) {
    $errors[] = 'Please select a frame color.';
}
if ($customWidth <= 0 || $customHeight <= 0) {
    $errors[] = 'Please enter a valid frame size (width and height).';
}
if (empty($mountTypeId)) {
    $errors[] = 'Please select a mount type.';
}

// Required for Frame & Print
if ($serviceType === 'FRAME&PRINT') {
    if (empty($paperTypeId)) {
        $errors[] = 'Please select a paper type.';
    }
    if (empty($_FILES['customer_image']['name'])) {
        $errors[] = 'Please upload your image.';
    }
}

// Required for Buy Now
if ($action === 'buy_now') {
    if (!in_array($paymentMethod, ['CASH', 'GCASH'])) {
        $errors[] = 'Please select a valid payment method.';
    }
    if ($deliveryOption === 'DELIVERY' && empty($deliveryAddress)) {
        $errors[] = 'Please enter your delivery address.';
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit();
}

// ── Handle image upload ──────────────────────────────────
$imagePath = '';
if ($serviceType === 'FRAME&PRINT' && !empty($_FILES['customer_image']['name'])) {
    $uploadDir  = __DIR__ . '/../uploads/customer_images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileExt      = strtolower(pathinfo($_FILES['customer_image']['name'], PATHINFO_EXTENSION));
    $allowedExts  = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($fileExt, $allowedExts)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image format. Use JPG, PNG, or WEBP.']);
        exit();
    }

    if ($_FILES['customer_image']['size'] > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Image too large. Maximum size is 10MB.']);
        exit();
    }

    $fileName  = 'cust_' . $customerId . '_' . time() . '.' . $fileExt;
    $destPath  = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['customer_image']['tmp_name'], $destPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image. Please try again.']);
        exit();
    }

    $imagePath = 'uploads/customer_images/' . $fileName;
}

// ── Build data array for service ─────────────────────────
$data = [
    'service_type'          => $serviceType,
    'frame_type_id'         => $frameTypeId,
    'frame_design_id'       => $frameDesignId,
    'frame_color_id'        => $frameColorId,
    'frame_size_id'         => $frameSizeId,
    'custom_width'          => $customWidth,
    'custom_height'         => $customHeight,
    'primary_matboard_id'   => $primaryMatboard,
    'secondary_matboard_id' => $secondaryMatboard,
    'mount_type_id'         => $mountTypeId,
    'paper_type_id'         => $paperTypeId,
    'image_path'            => $imagePath,
    'quantity'              => $quantity,
    'payment_method'        => $paymentMethod,
    'delivery_option'       => $deliveryOption,
    'delivery_address'      => $deliveryOption === 'DELIVERY' ? $deliveryAddress : null,
];

// ── Call the service ─────────────────────────────────────
$service = new CustomFrameService($conn);

if ($action === 'add_to_cart') {
    $result = $service->addToCart($customerId, $data);
} else {
    $result = $service->buyNow($customerId, $data);
}

echo json_encode($result);
exit();