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

// --- ACTION: REMOVE SINGLE ITEM ---
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

// --- ACTION: REMOVE SELECTED ITEMS ONLY ---
if (isset($_GET['action']) && $_GET['action'] === 'delete_selected' && isset($_GET['ids'])) {
    $ids_raw = json_decode($_GET['ids'], true);

    // Validate: must be a non-empty array of integers belonging to this customer
    if (!is_array($ids_raw) || empty($ids_raw)) {
        header("Location: ../customer/customer_cart.php");
        exit;
    }

    // Sanitize to integers
    $item_ids = array_map('intval', $ids_raw);
    $item_ids = array_filter($item_ids, fn($id) => $id > 0);

    if (empty($item_ids)) {
        header("Location: ../customer/customer_cart.php");
        exit;
    }

    $conn->begin_transaction();
    try {
        $placeholders = implode(',', array_fill(0, count($item_ids), '?'));
        $types = str_repeat('i', count($item_ids));

        // Get associated printing IDs for these items (only if they belong to this customer)
        $get_p = $conn->prepare("
            SELECT f.item_id, f.printing_order_item_id 
            FROM tbl_frame_order_items f
            JOIN tbl_cart c ON f.cart_id = c.cart_id
            WHERE f.item_id IN ($placeholders)
              AND c.customer_id = ?
              AND f.source_type = 'CART'
        ");
        $get_p->bind_param($types . 'i', ...[...$item_ids, $customer_id]);
        $get_p->execute();
        $res = $get_p->get_result();

        $valid_item_ids = [];
        $printing_ids = [];
        while ($row = $res->fetch_assoc()) {
            $valid_item_ids[] = $row['item_id'];
            if (!empty($row['printing_order_item_id'])) {
                $printing_ids[] = $row['printing_order_item_id'];
            }
        }

        if (!empty($valid_item_ids)) {
            $vPlaceholders = implode(',', array_fill(0, count($valid_item_ids), '?'));
            $vTypes = str_repeat('i', count($valid_item_ids));

            // Delete frame items
            $del_f = $conn->prepare("DELETE FROM tbl_frame_order_items WHERE item_id IN ($vPlaceholders)");
            $del_f->bind_param($vTypes, ...$valid_item_ids);
            $del_f->execute();

            // Delete associated printing items
            if (!empty($printing_ids)) {
                $pPlaceholders = implode(',', array_fill(0, count($printing_ids), '?'));
                $pTypes = str_repeat('i', count($printing_ids));
                $del_p = $conn->prepare("DELETE FROM tbl_printing_order_items WHERE printing_order_item_id IN ($pPlaceholders)");
                $del_p->bind_param($pTypes, ...$printing_ids);
                $del_p->execute();
            }
        }

        $conn->commit();
        header("Location: ../customer/customer_cart.php?status=deleted_selected");
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../customer/customer_cart.php?status=error");
    }
    exit;
}

// --- ACTION: CLEAR ALL ITEMS ---
if (isset($_GET['action']) && $_GET['action'] === 'delete_all') {
    $conn->begin_transaction();
    try {
        // Get printing IDs first
        $get_p = $conn->prepare("SELECT printing_order_item_id FROM tbl_frame_order_items f JOIN tbl_cart c ON f.cart_id = c.cart_id WHERE c.customer_id = ? AND f.source_type = 'CART'");
        $get_p->bind_param("i", $customer_id);
        $get_p->execute();
        $res = $get_p->get_result();
        $p_ids = [];
        while ($row = $res->fetch_assoc()) { if ($row['printing_order_item_id']) $p_ids[] = $row['printing_order_item_id']; }

        // Delete Frames
        $del_f = $conn->prepare("DELETE f FROM tbl_frame_order_items f JOIN tbl_cart c ON f.cart_id = c.cart_id WHERE c.customer_id = ? AND f.source_type = 'CART'");
        $del_f->bind_param("i", $customer_id);
        $del_f->execute();

        // Delete Printing
        if (!empty($p_ids)) {
            $placeholders = implode(',', array_fill(0, count($p_ids), '?'));
            $del_p = $conn->prepare("DELETE FROM tbl_printing_order_items WHERE printing_order_item_id IN ($placeholders)");
            $del_p->bind_param(str_repeat('i', count($p_ids)), ...$p_ids);
            $del_p->execute();
        }
        $conn->commit();
        header("Location: ../customer/customer_cart.php?status=cleared");
    } catch (Exception $e) { $conn->rollback(); }
    exit;
}

// --- FETCH MAIN CART ---
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

while ($row = $res->fetch_assoc()) {
    if (!empty($row['print_image'])) { $row['display_image'] = "../" . $row['print_image']; }
    elseif (!empty($row['design_image'])) { $row['display_image'] = "../uploads/" . $row['design_image']; }
    else { $row['display_image'] = null; }

    $design = $row['design_name'] ?? $row['product_name'] ?? 'Frame';
    $size = $row['frame_size'] ?? ((float)$row['width_inch'] . '"X' . (float)$row['height_inch'] . '"');
    $color = $row['color_name'] ?? '';
    $row['display_name'] = trim("$design $size $color");
    $row['id'] = $row['item_id'];
    $cart_items[] = $row;
    $total_amount += $row['sub_total'];
}
?>