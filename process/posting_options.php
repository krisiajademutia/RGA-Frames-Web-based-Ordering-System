<?php
session_start();
// Path to reach config from the /process/ folder
include __DIR__ . '/../config/db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_option'])) {
    $active_tab = $_GET['tab'] ?? 'frame_types';
    $status = (isset($_POST['is_active']) && $_POST['is_active'] == '1') ? 1 : 0;
    $success = false;

    // Define upload directory relative to this script
    $upload_dir = "../../uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // 1. Frame Types (tbl_frame_types)
    if ($active_tab == 'frame_types') {
        $name = $_POST['type_name'];
        $price = (double)$_POST['type_price'];
        $stmt = $conn->prepare("INSERT INTO tbl_frame_types (type_name, type_price, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $name, $price, $status);
        $success = $stmt->execute();

    // 2. Frame Designs (tbl_frame_designs)
    } elseif ($active_tab == 'frame_designs') {
        $name = $_POST['design_name'];
        $price = (double)$_POST['price'];
        $stmt = $conn->prepare("INSERT INTO tbl_frame_designs (design_name, price, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $name, $price, $status);
        $success = $stmt->execute();

    // 3. Frame Colors (tbl_frame_colors)
    } elseif ($active_tab == 'frame_colors') {
        $name = $_POST['color_name'];
        $img = $_FILES['color_image']['name'];
        $target = $upload_dir . basename($img);
        
        if (move_uploaded_file($_FILES['color_image']['tmp_name'], $target)) {
            $stmt = $conn->prepare("INSERT INTO tbl_frame_colors (color_name, color_image, is_active) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $name, $img, $status);
            $success = $stmt->execute();
        }

    // 4. Frame Sizes (tbl_frame_sizes)
    } elseif ($active_tab == 'frame_sizes') {
        $width = (double)$_POST['width'];
        $height = (double)$_POST['height'];
        $total = (double)$_POST['total_inches'];
        $price = (double)$_POST['base_price'];
        $dim = $width . "x" . $height;

        $stmt = $conn->prepare("INSERT INTO tbl_frame_sizes (dimension, width_inch, height_inch, total_inch, price, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sddddi", $dim, $width, $height, $total, $price, $status);
        $success = $stmt->execute();

    // 5. Matboard Colors (tbl_matboard_colors)
    } elseif ($active_tab == 'matboard_colors') {
        $name = $_POST['matboard_color_name'];
        $img = $_FILES['matboard_image']['name'];
        $target = $upload_dir . basename($img);

        move_uploaded_file($_FILES['matboard_image']['tmp_name'], $target);
        // SQL uses base_price for matboards
        $stmt = $conn->prepare("INSERT INTO tbl_matboard_colors (matboard_color_name, image_name, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $img, $status);
        $success = $stmt->execute();

    // 6. Mount Types (tbl_mount_type)
    } elseif ($active_tab == 'mount_types') {
        $name = $_POST['generic_name'];
        $fee = (double)$_POST['generic_price'];
        
        $stmt = $conn->prepare("INSERT INTO tbl_mount_type (mount_name, additional_fee, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $name, $fee, $status);
        $success = $stmt->execute();

    // 7. Paper Types (tbl_paper_type)
    } elseif ($active_tab == 'paper_types') {
        $name = $_POST['generic_name'];
        $logic = strtoupper($_POST['pricing_logic']); // FIXED or CALCULATED
        $width = (double)$_POST['width'];
        $height = (double)$_POST['height'];
        $total = (double)$_POST['total_inches'];
        $price = (double)$_POST['generic_price'];
        $dim = $width . "x" . $height;

        $stmt = $conn->prepare("INSERT INTO tbl_paper_type (paper_name, pricing_logic, dimension, width_inch, height_inch, total_inch, price, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssddddi", $name, $logic, $dim, $width, $height, $total, $price, $status);
        $success = $stmt->execute();
    }

    // Redirect
    $status_param = $success ? "success=1" : "error=1";
    header("Location: ../admin/admin_custom_frame_options.php?tab=$active_tab&$status_param");
    exit();
}