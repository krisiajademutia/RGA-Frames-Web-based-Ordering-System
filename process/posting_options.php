<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // don't display, log instead
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

// Initialize FixedPriceRepository standalone to avoid Interface Type errors
$fixedPriceRepo = new FixedPriceRepository($conn);

$action = $_POST['action'] ?? '';
$active_tab = $_GET['tab'] ?? $_POST['tab'] ?? 'frame_types';

// ── FIXED PRINT PRICE ACTIONS ──────────────────────────────────────────────
// These bypass the $service because they use a different data structure
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add_fixed_price') {
        $success = $fixedPriceRepo->create($_POST);
        header("Location: ../admin/admin_custom_frame_options.php?tab=paper_types&success=" . ($success ? "1" : "0"));
        exit();
    }

    if ($action === 'update_fixed_price') {
        $id = (int)($_POST['fixed_price_id'] ?? 0);
        $success = ($id > 0) ? $fixedPriceRepo->update($id, $_POST) : false;
        header("Location: ../admin/admin_custom_frame_options.php?tab=paper_types&success=" . ($success ? "1" : "0"));
        exit();
    }

    if ($action === 'delete_fixed_price') {
        $id = (int)($_POST['fixed_price_id'] ?? 0);
        $success = ($id > 0) ? $fixedPriceRepo->delete($id) : false;
        header("Location: ../admin/admin_custom_frame_options.php?tab=paper_types&success=" . ($success ? "1" : "0"));
        exit();
    }
}

// ── ADD (STANDARD OPTIONS) ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_option'])) {
    $data = $_POST;
    $data['is_active'] = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

    $success = $service->addOption($active_tab, $data, $_FILES);

    if ($success) {
        header("Location: ../admin/admin_custom_frame_options.php?tab=$active_tab&success=1");
    } else {
        header("Location: ../admin/admin_custom_frame_options.php?tab=$active_tab&error=1");
    }
    exit();
}

// ── EDIT (UPDATE STANDARD OPTIONS) ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['update_option']) || $action === 'edit')) {
    $id = (int)($_POST['option_id'] ?? 0);
    $data = $_POST;
    $data['is_active'] = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

    if ($id > 0 && $active_tab !== '') {
        $success = $service->updateOption($active_tab, $id, $data, $_FILES);
        header("Location: ../admin/admin_custom_frame_options.php?tab=$active_tab&success=" . ($success ? "1" : "0"));
        exit();
    }
}

// ── DELETE (STANDARD OPTIONS) ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    $tab = $_POST['tab'] ?? $active_tab;
    $id  = (int)($_POST['option_id'] ?? 0);

    $success = ($id > 0) ? $service->deleteOption($tab, $id) : false;

    header("Location: ../admin/admin_custom_frame_options.php?tab=$tab&success=" . ($success ? '1' : '0'));
    exit();
}