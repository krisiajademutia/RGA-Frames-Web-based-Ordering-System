<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in.']);
    exit;
}

// Gather posted data
$image = $_FILES['image'] ?? null;
$type = $_POST['type'] ?? null;
$size = $_POST['size'] ?? null;
$width = $_POST['width'] ?? 0;
$height = $_POST['height'] ?? 0;
$qty = $_POST['qty'] ?? 1;
$total_price = $_POST['total_price'] ?? 0;

// Optional: move uploaded file to temp folder
if ($image && $image['tmp_name']) {
    $target_dir = "../uploads/temp/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $filename = time() . '_' . basename($image['name']);
    move_uploaded_file($image['tmp_name'], $target_dir . $filename);
} else {
    $filename = null;
}

// Save item in session as expected by checkout.php
$_SESSION['buy_now_item'] = [
    'image' => $filename,
    'paper_type' => $type,
    'size' => $size,
    'width' => $width,
    'height' => $height,
    'quantity' => $qty,
    'total_price' => $total_price
];

echo json_encode(['success' => true]);