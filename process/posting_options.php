<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); 
ini_set('log_errors', 1);
session_start();

require_once __DIR__ . '/../config/db_connect.php';
$repoDir = __DIR__ . '/../classes/Option/Repository/';

require_once $repoDir . 'OptionRepositoryInterface.php';

foreach (glob($repoDir . "*.php") as $filename) {
    require_once $filename;
}
require_once __DIR__ . '/../classes/Option/OptionService.php';

$upload_dir = "../uploads/";

$service = new OptionService();
$service->registerRepository('frame_types',     new FrameTypeRepository($conn, $upload_dir));
$service->registerRepository('frame_designs',   new FrameDesignRepository($conn, $upload_dir));
$service->registerRepository('frame_colors',    new FrameColorRepository($conn, $upload_dir));
$service->registerRepository('frame_sizes',     new FrameSizeRepository($conn));
$service->registerRepository('matboard_colors', new MatboardColorRepository($conn, $upload_dir));
$service->registerRepository('mount_types',     new MountTypeRepository($conn));
$service->registerRepository('paper_types',     new PaperTypeRepository($conn));

$fixedPriceRepo = new FixedPriceRepository($conn);

$action = $_POST['action'] ?? '';
$active_tab = $_POST['tab'] ?? $_GET['tab'] ?? 'frame_types';

// ── FIXED PRINT PRICE ACTIONS ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add_fixed_price' || $action === 'update_fixed_price' || $action === 'delete_fixed_price') {
        $success = false;
        $msg_action = "";
        $displayName = "Pricing record";

        $f_id = (int)($_POST['fixed_price_id'] ?? 0);
        if ($f_id > 0) {
            $stmt = $conn->prepare("SELECT dimension FROM tbl_fixed_print_prices WHERE fixed_price_id = ?");
            $stmt->bind_param("i", $f_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $displayName = $row['dimension'];
            }
        } elseif ($action === 'add_fixed_price') {
            $displayName = $_POST['dimension'] ?? 'New Pricing';
        }

        if ($action === 'add_fixed_price') {
            $success = $fixedPriceRepo->create($_POST);
            $msg_action = "added";
        } elseif ($action === 'update_fixed_price') {
            $success = ($f_id > 0) ? $fixedPriceRepo->update($f_id, $_POST) : false;
            $msg_action = "updated";
        } elseif ($action === 'delete_fixed_price') {
            $success = ($f_id > 0) ? $fixedPriceRepo->delete($f_id) : false;
            $msg_action = "deleted";
        }

        if ($success) {
            $_SESSION['opt_success_modal'] = [
                'name' => $displayName, 
                'action' => $msg_action, 
                'type' => 'fixed' 
            ];
        } else {
            $_SESSION['opt_error_modal'] = ['title' => 'Operation Failed', 'message' => 'Could not process the pricing record.'];
        }
        header("Location: ../admin/admin_custom_frame_options.php?tab=paper_types");
        exit();
    }
}

// ── ADD (STANDARD OPTIONS) ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_option'])) {
    $data = $_POST;
    $data['is_active'] = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

    if ($active_tab === 'frame_sizes') {
        $displayName = ($_POST['width'] ?? '0') . 'x' . ($_POST['height'] ?? '0');
    } elseif ($active_tab === 'mount_types') {
        $displayName = $_POST['generic_name'] ?? 'Mount Type';
    } elseif ($active_tab === 'matboard_colors') {
        $displayName = $_POST['matboard_color_name'] ?? 'Matboard Color';
    } else {
        $displayName = $_POST['name'] ?? $_POST['type_name'] ?? $_POST['design_name'] ?? $_POST['color_name'] ?? $_POST['paper_name'] ?? 'Option';
    }

    $success = $service->addOption($active_tab, $data, $_FILES);

    if ($success) {
        $_SESSION['opt_success_modal'] = [
            'name' => $displayName, 
            'action' => 'added', 
            'type' => $active_tab 
        ];
    } else {
        $_SESSION['opt_error_modal'] = ['title' => 'Add Failed', 'message' => 'The item could not be added.'];
    }
    header("Location: ../admin/admin_custom_frame_options.php?tab=$active_tab");
    exit();
}

// ── EDIT (UPDATE STANDARD OPTIONS) ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['update_option']) || $action === 'edit')) {
    $id = (int)($_POST['option_id'] ?? 0);
    $data = $_POST;
    $data['is_active'] = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

    $currentRecord = $service->getOptionById($active_tab, $id);
    
    $displayName = $currentRecord['dimension'] ?? $currentRecord['type_name'] ?? $currentRecord['design_name'] ?? $currentRecord['color_name'] ?? $currentRecord['matboard_color_name'] ?? $currentRecord['mount_name'] ?? $currentRecord['paper_name'] ?? 'Option';

    if ($id > 0 && $active_tab !== '') {
        $success = $service->updateOption($active_tab, $id, $data, $_FILES);
        if ($success) {
            $_SESSION['opt_success_modal'] = [
                'name' => $displayName, 
                'action' => 'updated', 
                'type' => $active_tab
            ];
        } else {
            $_SESSION['opt_error_modal'] = ['title' => 'Update Failed', 'message' => 'Changes could not be saved.'];
        }
        header("Location: ../admin/admin_custom_frame_options.php?tab=$active_tab");
        exit();
    }
}

// ── DELETE (STANDARD OPTIONS) ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    $tab = $_POST['tab'] ?? $active_tab;
    $id  = (int)($_POST['option_id'] ?? 0);
    
    $currentRecord = $service->getOptionById($tab, $id);
    $displayName = $currentRecord['dimension'] ?? $currentRecord['type_name'] ?? $currentRecord['design_name'] ?? $currentRecord['color_name'] ?? $currentRecord['matboard_color_name'] ?? $currentRecord['mount_name'] ?? $currentRecord['paper_name'] ?? 'Item';

    try {
        if ($id > 0 && $service->deleteOption($tab, $id)) {
            $_SESSION['opt_success_modal'] = [
                'name' => $displayName, 
                'action' => 'deleted', 
                'type' => $tab
            ];
        } else {
            $_SESSION['opt_error_modal'] = ['title' => 'Delete Failed', 'message' => 'The item could not be removed.'];
        }
    } catch (Exception $e) {
        $_SESSION['opt_error_modal'] = ['title' => 'Cannot Delete', 'message' => 'This option is currently linked to existing products or orders.'];
    }
    header("Location: ../admin/admin_custom_frame_options.php?tab=$tab");
    exit();
}