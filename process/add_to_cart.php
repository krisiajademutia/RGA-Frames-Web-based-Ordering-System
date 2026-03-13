<?php
include_once __DIR__ . '/../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in.']);
    exit;
}

$target_dir = "../uploads/customer_print/";
// Ensure the directory exists
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$file_name = time() . '_' . basename($_FILES["image"]["name"]);
$target_file = $target_dir . $file_name;

if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    $customer_id = $_SESSION['user_id'];
    $paper_name = mysqli_real_escape_string($conn, $_POST['type']);
    $size = mysqli_real_escape_string($conn, $_POST['size']);
    $qty = (int)$_POST['qty'];
    $sub_total = (float)$_POST['total_price'];
    
    // 1. Capture dimensions and calculate Total Inch (Area)
    $w = (!empty($_POST['width'])) ? (float)$_POST['width'] : 0;
    $h = (!empty($_POST['height'])) ? (float)$_POST['height'] : 0;
    $total_inch = $w * $h; // Calculation: Width x Height

    $type_query = "SELECT paper_type_id FROM tbl_paper_type WHERE paper_name='$paper_name' LIMIT 1";
    $type_res = mysqli_query($conn, $type_query);
    $type_row = mysqli_fetch_assoc($type_res);
    $paper_type_id = $type_row ? (int)$type_row['paper_type_id'] : 0;

    if ($paper_type_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid paper type selected.']);
        exit;
    }

    // 2. Cart Logic (Check or Create Cart)
    $cart_query = "SELECT cart_id FROM tbl_cart WHERE customer_id = '$customer_id' LIMIT 1";
    $cart_result = mysqli_query($conn, $cart_query);
    if (mysqli_num_rows($cart_result) > 0) {
        $cart_id = mysqli_fetch_assoc($cart_result)['cart_id'];
    } else {
        mysqli_query($conn, "INSERT INTO tbl_cart (customer_id) VALUES ('$customer_id')");
        $cart_id = mysqli_insert_id($conn);
    }

    // 3. UPDATED INSERT: Added total_inch to the column list and values
    $insert_sql = "INSERT INTO tbl_printing_order_items 
               (cart_id, paper_type_id, image_path, dimension, quantity, sub_total, width_inch, height_inch, total_inch) 
               VALUES (
                   '$cart_id', 
                   '$paper_type_id', 
                   '$file_name', 
                   '$size', 
                   '$qty', 
                   '$sub_total', 
                   '$w', 
                   '$h', 
                   '$total_inch'
               )";

    if (mysqli_query($conn, $insert_sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => "Database Error: " . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'File upload failed.']);
}
?>