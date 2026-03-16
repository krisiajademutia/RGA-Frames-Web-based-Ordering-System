<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db_connect.php';

$customer_id = $_SESSION['user_id'] ?? null;
$cart_items = [];
$total_amount = 0;

if (!$customer_id) {
    header("Location: login.php");
    exit;
}

// --- AJAX ACTION: FETCH MODAL DETAILS ---
if (isset($_GET['action']) && $_GET['action'] === 'get_details' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = intval($_GET['id']);
    $query = "
        SELECT f.*, ft.type_name, fd.design_name, fdi.image_name AS design_image, fc.color_name,
                p.image_path, p.width_inch AS p_w, p.height_inch AS p_h,
                pt.paper_name, mc.matboard_color_name, mt.mount_name,
                c.custom_width, c.custom_height, r.product_name AS ready_made_name,
                fs.dimension AS frame_size_label
        FROM tbl_frame_order_items f
        LEFT JOIN tbl_custom_frame_product c ON f.c_product_id = c.c_product_id
        LEFT JOIN tbl_ready_made_product r ON f.r_product_id = r.r_product_id
        LEFT JOIN tbl_frame_designs fd ON (c.frame_design_id = fd.frame_design_id OR r.frame_design_id = fd.frame_design_id)
        LEFT JOIN tbl_frame_design_images fdi ON (fd.frame_design_id = fdi.frame_design_id AND fdi.is_primary = 1)
        LEFT JOIN tbl_frame_colors fc ON (c.frame_color_id = fc.frame_color_id OR r.frame_color_id = fc.frame_color_id)
        LEFT JOIN tbl_frame_sizes fs ON (r.width = fs.width_inch AND r.height = fs.height_inch)
        LEFT JOIN tbl_printing_order_items p ON f.printing_order_item_id = p.printing_order_item_id
        LEFT JOIN tbl_paper_type pt ON p.paper_type_id = pt.paper_type_id
        LEFT JOIN tbl_matboard_colors mc ON f.primary_matboard_id = mc.matboard_color_id
        LEFT JOIN tbl_mount_type mt ON f.mount_type_id = mt.mount_type_id
        LEFT JOIN tbl_frame_types ft ON (c.frame_type_id = ft.frame_type_id OR r.frame_type_id = ft.frame_type_id)
        WHERE f.item_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode($stmt->get_result()->fetch_assoc());
    exit;
}

// --- ACTION: REMOVE ITEM ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $item_id = intval($_GET['id']);
    $conn->begin_transaction();
    try {
        $find_stmt = $conn->prepare("SELECT printing_order_item_id FROM tbl_frame_order_items WHERE item_id = ?");
        $find_stmt->bind_param("i", $item_id);
        $find_stmt->execute();
        $res = $find_stmt->get_result()->fetch_assoc();
        
        $del_f = $conn->prepare("DELETE FROM tbl_frame_order_items WHERE item_id = ?");
        $del_f->bind_param("i", $item_id);
        $del_f->execute();

        if (!empty($res['printing_order_item_id'])) {
            $del_p = $conn->prepare("DELETE FROM tbl_printing_order_items WHERE printing_order_item_id = ?");
            $del_p->bind_param("i", $res['printing_order_item_id']);
            $del_p->execute();
        }
        $conn->commit();
        header("Location: ../customer/customer_cart.php?status=deleted");
    } catch (Exception $e) { 
        $conn->rollback(); 
    }
    exit;
}

// --- ACTION: FETCH MAIN CART ---
$cart_query = "
    SELECT f.*, fd.design_name, fdi.image_name AS design_image, fc.color_name, 
           fs.dimension AS frame_size,
           COALESCE(p.width_inch, c.custom_width, r.width) as width_inch, 
           COALESCE(p.height_inch, c.custom_height, r.height) as height_inch,
           p.image_path as print_image, r.product_name
    FROM tbl_frame_order_items f
    JOIN tbl_cart cart ON f.cart_id = cart.cart_id
    LEFT JOIN tbl_custom_frame_product c ON f.c_product_id = c.c_product_id
    LEFT JOIN tbl_ready_made_product r ON f.r_product_id = r.r_product_id
    LEFT JOIN tbl_frame_designs fd ON (c.frame_design_id = fd.frame_design_id OR r.frame_design_id = fd.frame_design_id)
    LEFT JOIN tbl_frame_design_images fdi ON (fd.frame_design_id = fdi.frame_design_id AND fdi.is_primary = 1)
    LEFT JOIN tbl_frame_colors fc ON (c.frame_color_id = fc.frame_color_id OR r.frame_color_id = fc.frame_color_id)
    LEFT JOIN tbl_frame_sizes fs ON (r.width = fs.width_inch AND r.height = fs.height_inch)
    LEFT JOIN tbl_printing_order_items p ON f.printing_order_item_id = p.printing_order_item_id
    WHERE cart.customer_id = ? AND f.source_type = 'CART'
";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$res = $stmt->get_result();

while($row = $res->fetch_assoc()) {
    // Determine image path based on root 'uploads' folder
    if (!empty($row['print_image'])) {
        // Assuming print_image path in DB is relative to root or within /uploads/
        $row['display_image'] = "../" . $row['print_image'];
    } elseif (!empty($row['design_image'])) {
        // Points from /customer/ folder to the root /uploads/ folder
        $row['display_image'] = "../uploads/" . $row['design_image'];
    } else {
        $row['display_image'] = null;
    }
    
    // Build Display Name: "Design Name 7x11 Red"
    $design = $row['design_name'] ?? $row['product_name'] ?? 'Frame';
    $size = $row['frame_size'] ?? ((float)$row['width_inch'] . '"X' . (float)$row['height_inch'] . '"');
    $color = $row['color_name'] ?? '';
    $row['display_name'] = trim("$design $size $color");
    
    // JS compatibility mapping
    $row['id'] = $row['item_id']; 
    
    $cart_items[] = $row;
    $total_amount += $row['sub_total'];
}
?>