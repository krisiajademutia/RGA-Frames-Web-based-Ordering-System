<?php
ob_start();
session_start();

$root = __DIR__ . '/..';
require_once $root . '/config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {

    // =========================
    // 1. Get & Validate Fields
    // =========================
    $product_name = trim($_POST['product_name'] ?? '');
    $type_id      = (int)($_POST['frame_type_id'] ?? 0);
    $design_id    = (int)($_POST['frame_design_id'] ?? 0);
    $color_id     = (int)($_POST['frame_color_id'] ?? 0);
    $width        = (float)($_POST['width'] ?? 0);
    $height       = (float)($_POST['height'] ?? 0);
    $price        = (float)($_POST['product_price'] ?? 0);
    $stock        = (int)($_POST['stock_quantity'] ?? 0);

    // Required validation
    if (
        empty($product_name) ||
        $type_id <= 0 ||
        $design_id <= 0 ||
        $color_id <= 0 ||
        $width <= 0 ||
        $height <= 0 ||
        $price <= 0
    ) {
        $_SESSION['post_error'] = "Please fill out all required fields properly.";
        header("Location: ../admin/admin_post_frames.php");
        exit();
    }

    // =========================
    // 2. Handle Image Upload
    // =========================
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['post_error'] = "Please upload a product image.";
        header("Location: ../admin/admin_post_frames.php");
        exit();
    }

    $upload_dir = $root . "/uploads/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $image_name = time() . "_" . basename($_FILES['image']['name']);
    $target     = $upload_dir . $image_name;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $_SESSION['post_error'] = "Failed to upload image.";
        header("Location: ../admin/admin_post_frames.php");
        exit();
    }

    // =========================
    // 3. Insert Product
    // =========================
    $sql = "INSERT INTO tbl_ready_made_product
            (product_name, frame_type_id, frame_design_id, frame_color_id, width, height, image_name, product_price)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $_SESSION['post_error'] = "Prepare failed: " . $conn->error;
        header("Location: ../admin/admin_post_frames.php");
        exit();
    }

    $stmt->bind_param(
        "siiiddsd",
        $product_name,
        $type_id,
        $design_id,
        $color_id,
        $width,
        $height,
        $image_name,
        $price
    );

    if (!$stmt->execute()) {
        $_SESSION['post_error'] = "Database Error: " . $stmt->error;
        header("Location: ../admin/admin_post_frames.php");
        exit();
    }

    $new_product_id = $conn->insert_id;
    $stmt->close();

    // =========================
    // 4. Insert Stock
    // =========================
    if ($stock > 0) {
        $stock_sql = "INSERT INTO tbl_ready_made_product_stocks
                      (r_product_id, quantity)
                      VALUES (?, ?)";

        $stock_stmt = $conn->prepare($stock_sql);
        $stock_stmt->bind_param("ii", $new_product_id, $stock);
        $stock_stmt->execute();
        $stock_stmt->close();
    }

    $_SESSION['post_success'] = "Product published successfully!";
    header("Location: ../admin/admin_post_frames.php");
    exit();
}
?>