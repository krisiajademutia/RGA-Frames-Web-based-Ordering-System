<?php
include_once __DIR__ . '/../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in.']);
    exit;
}

$target_dir = "../uploads/customer_print/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$file_name = time() . '_' . basename($_FILES["image"]["name"]);
$target_file = $target_dir . $file_name;

if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    $customer_id = $_SESSION['user_id'];
    
    // Inputs from JS
    $paper_type_id = (int)$_POST['type'];
    $w = (float)$_POST['width'];
    $h = (float)$_POST['height'];
    $qty = (int)$_POST['qty'];
    $sub_total = (float)$_POST['total_price'];

    // --- SECURITY CHECK START ---
    $stmt_check = $conn->prepare("SELECT max_width_inch, max_height_inch FROM tbl_paper_type WHERE paper_type_id = ?");
    $stmt_check->bind_param("i", $paper_type_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $paper = $result_check->fetch_assoc();

    if ($paper) {
        $max_w = (float)$paper['max_width_inch'];
        $max_h = (float)$paper['max_height_inch'];

        // Only enforce if max values are defined (greater than 0)
        if ($max_w > 0 && ($w > $max_w || $h > $max_h)) {
            // Delete the uploaded file since the order is invalid
            unlink($target_file);
            
            // REMOVED: The dimension violation message.
            // We return success false with no message so the JS doesn't trigger a pop-up.
            echo json_encode(['success' => false, 'message' => '']); 
            exit;
        }
    }
    // --- SECURITY CHECK END ---

    // 1. Get or Create Cart
    $stmt_cart = $conn->prepare("SELECT cart_id FROM tbl_cart WHERE customer_id = ? LIMIT 1");
    $stmt_cart->bind_param("i", $customer_id);
    $stmt_cart->execute();
    $result_cart = $stmt_cart->get_result();

    if ($result_cart->num_rows > 0) {
        $cart_id = $result_cart->fetch_assoc()['cart_id'];
    } else {
        $stmt_ins_cart = $conn->prepare("INSERT INTO tbl_cart (customer_id) VALUES (?)");
        $stmt_ins_cart->bind_param("i", $customer_id);
        $stmt_ins_cart->execute();
        $cart_id = $stmt_ins_cart->insert_id;
    }

    // 2. Insert into tbl_printing_order_items
    $insert_sql = "INSERT INTO tbl_printing_order_items 
                   (cart_id, paper_type_id, image_path, width_inch, height_inch, quantity, sub_total) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("iisddid", $cart_id, $paper_type_id, $file_name, $w, $h, $qty, $sub_total);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => "Database Error: " . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'File upload failed.']);
}
?>