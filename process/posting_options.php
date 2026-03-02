<?php
session_start();
include __DIR__ . '/../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_option'])) {
    $active_tab = $_GET['tab'] ?? 'frame_types';
    $success = false;
    $status = (isset($_POST['is_active']) && $_POST['is_active'] == '1') ? 1 : 0;

    if ($active_tab == 'frame_types') {
        $name = $_POST['type_name'];
        $price = (double)$_POST['type_price'];
        $img = $_FILES['type_image']['name'];
        move_uploaded_file($_FILES['type_image']['tmp_name'], "../../uploads/" . $img);
        $stmt = $conn->prepare("INSERT INTO tbl_frame_types (type_name, type_price, image_name, is_active) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdsi", $name, $price, $img, $status);
        $success = $stmt->execute();
    }
    // ... Add other elseif blocks (frame_colors, sizes, etc.) from original code here ...

    if ($success) {
        header("Location: ../admin_custom_frame_options.php?tab=$active_tab&success=1");
    } else {
        header("Location: ../admin_custom_frame_options.php?tab=$active_tab&error=1");
    }
    exit();
}