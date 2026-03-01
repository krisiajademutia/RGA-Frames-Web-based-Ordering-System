<?php
ob_start();
session_start();

$root = __DIR__ . '/..';
require_once $root . '/config/db_connect.php';

// =========================
// 1. Handle Deleting
// =========================
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $img_stmt = $conn->prepare("SELECT image_name FROM tbl_ready_made_product WHERE r_product_id = ?");
    $img_stmt->bind_param("i", $id);
    $img_stmt->execute();
    $res = $img_stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $file_path = $root . "/uploads/" . $row['image_name'];
        if (file_exists($file_path)) { unlink($file_path); }
    }
    $img_stmt->close();

    $conn->query("DELETE FROM tbl_ready_made_product_stocks WHERE r_product_id = $id");
    $del_stmt = $conn->prepare("DELETE FROM tbl_ready_made_product WHERE r_product_id = ?");
    $del_stmt->bind_param("i", $id);
    
    if ($del_stmt->execute()) {
        $_SESSION['post_success'] = "Product deleted successfully.";
    }
    header("Location: ../admin/admin_post_frames.php?view=posted");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $product_name = trim($_POST['product_name'] ?? '');
    $type_id      = (int)($_POST['frame_type_id'] ?? 0);
    $design_id    = (int)($_POST['frame_design_id'] ?? 0);
    $color_id     = (int)($_POST['frame_color_id'] ?? 0);
    $width        = (float)($_POST['width'] ?? 0);
    $height       = (float)($_POST['height'] ?? 0);
    $price        = (float)($_POST['product_price'] ?? 0);
    $stock        = (int)($_POST['stock_quantity'] ?? 0);

    // =========================
    // 2. Handle Adding
    // =========================
    if (isset($_POST['add_product'])) {
        if (empty($product_name) || $type_id <= 0 || $design_id <= 0 || $price <= 0) {
            $_SESSION['post_error'] = "Please fill out all required fields.";
            header("Location: ../admin/admin_post_frames.php");
            exit();
        }

        $image_name = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_name = time() . "_" . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $root . "/uploads/" . $image_name);
        }

        $sql = "INSERT INTO tbl_ready_made_product (product_name, frame_type_id, frame_design_id, frame_color_id, width, height, image_name, product_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // Corrected: 8 placeholders, 8 types (siiiddsd)
        $stmt->bind_param("siiiddsd", $product_name, $type_id, $design_id, $color_id, $width, $height, $image_name, $price);
        $stmt->execute();
        
        $new_id = $conn->insert_id;
        $conn->query("INSERT INTO tbl_ready_made_product_stocks (r_product_id, quantity) VALUES ($new_id, $stock)");

        $_SESSION['post_success'] = "Product published successfully!";
        header("Location: ../admin/admin_post_frames.php?view=posted");
        exit();
    }

    // =========================
    // 3. Handle Editing
    // =========================
    if (isset($_POST['update_product'])) {
        $product_id = (int)$_POST['r_product_id'];

        $sql = "UPDATE tbl_ready_made_product SET product_name=?, frame_type_id=?, frame_design_id=?, frame_color_id=?, width=?, height=?, product_price=? WHERE r_product_id=?";
        $stmt = $conn->prepare($sql);
        
        // Corrected: 8 placeholders, 8 types (siiidddi)
        $stmt->bind_param("siiidddi", $product_name, $type_id, $design_id, $color_id, $width, $height, $price, $product_id);
        
        if ($stmt->execute()) {
            $conn->query("UPDATE tbl_ready_made_product_stocks SET quantity = $stock WHERE r_product_id = $product_id");

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image_name = time() . "_" . basename($_FILES['image']['name']);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $root . "/uploads/" . $image_name)) {
                    $conn->query("UPDATE tbl_ready_made_product SET image_name = '$image_name' WHERE r_product_id = $product_id");
                }
            }
            $_SESSION['post_success'] = "Product updated successfully!";
        }
        header("Location: ../admin/admin_post_frames.php?view=posted");
        exit();
    }
}
?>