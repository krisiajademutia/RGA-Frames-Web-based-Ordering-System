<?php
ob_start();
session_start();
require_once '../config/db_connect.php';
require_once '../classes/Frames/Repository/FrameRepositoryInterface.php';
require_once '../classes/Frames/Repository/ReadyMadeFrameRepository.php';
require_once '../classes/Frames/FrameService.php';

$repository = new \Classes\Frames\Repository\ReadyMadeFrameRepository($conn);
$frameService = new \Classes\Frames\FrameService($repository);

// DELETE ACTION - Matches JS: ?action=delete&id=
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // 1. Get image names from DB to delete physical files from /uploads/
        $stmt = $conn->prepare("SELECT image_name FROM tbl_ready_made_product_images WHERE r_product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $images = [];
        while ($img = $result->fetch_assoc()) {
            $images[] = $img['image_name'];
        }

        // 2. Perform DB deletion
        if ($frameService->deleteFrame($id)) {
            foreach ($images as $imgName) {
                if (!empty($imgName)) {
                    @unlink("../uploads/" . $imgName);
                }
            }
            $_SESSION['post_success'] = "Product deleted successfully!";
        }
    } catch (mysqli_sql_exception $e) {
        $_SESSION['post_error'] = "Cannot delete: This product is linked to existing records.";
    }
    header("Location: ../admin/admin_post_frames.php?view=posted");
    exit();
}

// ADD PRODUCT ACTION
if (isset($_POST['add_product'])) {
    $data = [
        'product_name'    => $_POST['product_name'],
        'frame_type_id'   => $_POST['frame_type_id'],
        'frame_design_id' => $_POST['frame_design_id'],
        'frame_color_id'  => $_POST['frame_color_id'],
        'width'           => $_POST['width'],
        'height'          => $_POST['height'],
        'product_price'   => $_POST['product_price'],
        'stock_quantity'  => $_POST['stock_quantity']
    ];

    $productId = $frameService->createFrame($data);

    if ($productId) {
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $imageName = time() . "_" . $_FILES['images']['name'][$key];
                $isPrimary = ($key === 0) ? 1 : 0;
                if (move_uploaded_file($tmp_name, "../uploads/" . $imageName)) {
                    $frameService->addFrameImage($productId, $imageName, $isPrimary);
                }
            }
        }
        $_SESSION['post_success'] = "Frame posted successfully!";
    }
    header("Location: ../admin/admin_post_frames.php?view=posted");
    exit();
}

// UPDATE PRODUCT ACTION
if (isset($_POST['update_product'])) {
    $id = (int)$_POST['r_product_id'];
    $data = [
        'product_name'    => $_POST['product_name'],
        'frame_type_id'   => $_POST['frame_type_id'],
        'frame_design_id' => $_POST['frame_design_id'],
        'frame_color_id'  => $_POST['frame_color_id'],
        'width'           => $_POST['width'],
        'height'          => $_POST['height'],
        'product_price'   => $_POST['product_price'],
        'stock_quantity'  => $_POST['stock_quantity']
    ];

    if ($frameService->updateFrame($id, $data)) {
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $imageName = time() . "_" . $_FILES['images']['name'][$key];
                if (move_uploaded_file($tmp_name, "../uploads/" . $imageName)) {
                    $frameService->addFrameImage($id, $imageName, 0);
                }
            }
        }
        $_SESSION['post_success'] = "Frame updated successfully!";
    }
    header("Location: ../admin/admin_post_frames.php?view=posted");
    exit();
}