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
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get product details for the modal and file cleanup before deleting
    $product = $frameService->getFrameById($id);
    $product_name = $product['product_name'] ?? 'Product';

    try {
        if ($product && !empty($product['image_name'])) {
            @unlink("../uploads/" . $product['image_name']);
        }

        if ($frameService->deleteFrame($id)) {
            // Updated to trigger Success Modal
            $_SESSION['post_success_modal'] = [
                'name' => $product_name,
                'action' => 'deleted'
            ];
        }
    } catch (mysqli_sql_exception $e) {
        // Updated to trigger Error Modal
        $_SESSION['post_error_modal'] = [
            'title' => 'Cannot Delete Product',
            'message' => 'This product is linked to existing records and cannot be removed.'
        ];
    }
    header("Location: ../admin/admin_post_frames.php?view=posted");
    exit();
}

// ADD PRODUCT ACTION
if (isset($_POST['add_product'])) {
    $imageName = time() . "_" . $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $imageName);

    $data = [
        'product_name'    => $_POST['product_name'],
        'frame_type_id'   => $_POST['frame_type_id'],
        'frame_design_id' => $_POST['frame_design_id'],
        'frame_color_id'  => $_POST['frame_color_id'],
        'width'           => $_POST['width'],
        'height'          => $_POST['height'],
        'product_price'   => $_POST['product_price'],
        'stock_quantity'  => $_POST['stock_quantity'],
        'image_name'      => $imageName
    ];

    if ($frameService->createFrame($data)) {
        // Updated to trigger Success Modal
        $_SESSION['post_success_modal'] = [
            'name' => $_POST['product_name'],
            'action' => 'posted'
        ];
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

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageName = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $imageName);
        $data['image_name'] = $imageName;
    }

    if ($frameService->updateFrame($id, $data)) {
        // Updated to trigger Success Modal
        $_SESSION['post_success_modal'] = [
            'name' => $_POST['product_name'],
            'action' => 'updated'
        ];
    }
    header("Location: ../admin/admin_post_frames.php?view=posted");
    exit();
}