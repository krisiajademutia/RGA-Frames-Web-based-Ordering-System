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
$active_tab = $_GET['tab'] ?? $_POST['tab'] ?? 'frame_types';

// ── FIXED PRINT PRICE ACTIONS ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add_fixed_price' || $action === 'update_fixed_price' || $action === 'delete_fixed_price') {
        $success = false;
        $msg_action = "";

        if ($action === 'add_fixed_price') {
            $success = $fixedPriceRepo->create($_POST);
            $msg_action = "added";
        } elseif ($action === 'update_fixed_price') {
            $id = (int)($_POST['fixed_price_id'] ?? 0);
            $success = ($id > 0) ? $fixedPriceRepo->update($id, $_POST) : false;
            $msg_action = "updated";
        } elseif ($action === 'delete_fixed_price') {
            $id = (int)($_POST['fixed_price_id'] ?? 0);
            $success = ($id > 0) ? $fixedPriceRepo->delete($id) : false;
            $msg_action = "deleted";
        }

        if ($success) {
            $_SESSION['opt_success_modal'] = ['name' => 'Pricing record', 'action' => $msg_action];
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
    $success = $service->addOption($active_tab, $data, $_FILES);

    if ($success) {
        $_SESSION['opt_success_modal'] = ['name' => $_POST['name'] ?? 'Option', 'action' => 'added'];
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

    if ($id > 0 && $active_tab !== '') {
        $success = $service->updateOption($active_tab, $id, $data, $_FILES);
        if ($success) {
            $_SESSION['opt_success_modal'] = ['name' => $_POST['name'] ?? 'Option', 'action' => 'updated'];
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
    $name = $_POST['option_name'] ?? 'Item';

    try {
        if ($id > 0 && $service->deleteOption($tab, $id)) {
            $_SESSION['opt_success_modal'] = ['name' => $name, 'action' => 'deleted'];
        } else {
            $_SESSION['opt_error_modal'] = ['title' => 'Delete Failed', 'message' => 'The item could not be removed.'];
        }
    } catch (Exception $e) {
        $_SESSION['opt_error_modal'] = ['title' => 'Cannot Delete', 'message' => 'This option is currently linked to existing products or orders.'];
    }
    header("Location: ../admin/admin_custom_frame_options.php?tab=$tab");
    exit();
}