<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in.']);
    exit;
}

// Gather posted data
$image = $_FILES['image'] ?? null;
$type = $_POST['type'] ?? null;
$paper_name = $_POST['paper_name'] ?? 'Standard'; // Catches the actual text name
$size = $_POST['size'] ?? null;
$width = $_POST['width'] ?? 0;
$height = $_POST['height'] ?? 0;
$qty = $_POST['qty'] ?? 1;
$total_price = $_POST['total_price'] ?? 0;

// Move uploaded file to her correct printing folder
if ($image && $image['tmp_name']) {
    $target_dir = "../uploads/customer_print/"; 
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $filename = time() . '_' . basename($image['name']);
    move_uploaded_file($image['tmp_name'], $target_dir . $filename);
    
    // Add the folder path so the database knows exactly where to find the image!
    $full_image_path = "uploads/customer_print/" . $filename; 
} else {
    $full_image_path = null;
}

// Save item in session using the exact keys expected by the Omni-Channel checkout & DB
$_SESSION['buy_now_item'] = [
    'item_type'     => 'PRINTING',      
    'paper_type_id' => $type,           
    'paper_name'    => $paper_name,     
    'size'          => $size,
    'width'         => $width,
    'height'        => $height,
    'quantity'      => $qty,
    'total_price'   => $total_price,
    'image_path'    => $full_image_path 
];

echo json_encode(['success' => true]);