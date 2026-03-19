<?php
ob_start();
session_start();
require_once '../config/db_connect.php';
require_once '../classes/Frames/Repository/FrameRepositoryInterface.php';
require_once '../classes/Frames/Repository/ReadyMadeFrameRepository.php';
require_once '../classes/Frames/FrameService.php';

$repository = new \Classes\Frames\Repository\ReadyMadeFrameRepository($conn);
$frameService = new \Classes\Frames\FrameService($repository);

// DELETE ACTION
// DELETE ACTION
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $product = $frameService->getFrameById($id);
    $product_name = $product['product_name'] ?? 'Product';

    try {
      
        if ($frameService->deleteFrame($id)) {
            $_SESSION['post_success_modal'] = [
                'name' => $product_name, 
                'action' => 'deleted'
            ];
        } else {
            throw new Exception("Linked record detected.");
        }

    } catch (Exception $e) {
        $_SESSION['post_error_modal'] = [
            'title' => 'Cannot Delete Product', 
            'message' => 'This frame is currently linked to existing customer orders or records. To protect your sales history, it cannot be removed.'
        ];
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

    $new_id = $frameService->createFrame($data);
    if ($new_id) {
        // Handle Multiple Images
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $fileName = time() . "_" . $_FILES['images']['name'][$key];
                if (move_uploaded_file($tmp_name, "../uploads/" . $fileName)) {
                    $isPrimary = ($key === 0) ? 1 : 0;
                    $frameService->addFrameImage($new_id, $fileName, $isPrimary);
                }
            }
        }
        $_SESSION['post_success_modal'] = ['name' => $_POST['product_name'], 'action' => 'posted'];
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
        // 1. Handle removals from the 'x' button in JS
        if (!empty($_POST['removed_images'])) {
            $removedImages = json_decode($_POST['removed_images'], true);
            foreach ($removedImages as $fileName) {
                $repository->deleteImageByName($fileName);
                @unlink("../uploads/" . $fileName);
            }
        }

        // 2. Handle new additional uploads
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $fileName = time() . "_" . $_FILES['images']['name'][$key];
                if (move_uploaded_file($tmp_name, "../uploads/" . $fileName)) {
                    $frameService->addFrameImage($id, $fileName, 0);
                }
            }
        }
        $conn->query("UPDATE tbl_ready_made_product_images SET is_primary = 0 WHERE r_product_id = $id");
        $conn->query("UPDATE tbl_ready_made_product_images SET is_primary = 1 WHERE r_product_id = $id ORDER BY image_id ASC LIMIT 1");

        $_SESSION['post_success_modal'] = ['name' => $_POST['product_name'], 'action' => 'updated'];
    }
    header("Location: ../admin/admin_post_frames.php?view=posted");
    exit();
}