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


// ── ADD ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_option'])) {
    $active_tab = $_GET['tab'] ?? 'frame_types';

    $data = $_POST;

    // Use submitted is_active if present, default to 1 (active)
    $data['is_active'] = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

    $success = $service->addOption($active_tab, $data, $_FILES);

    if ($success) {
        header("Location: ../admin/admin_custom_frame_options.php?tab=$active_tab&success=1");
    } else {
        header("Location: ../admin/admin_custom_frame_options.php?tab=$active_tab&error=1");
    }
    exit();
}


// ── EDIT ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $tab = $_POST['tab'] ?? '';
    $id  = (int)($_POST['option_id'] ?? 0);

    // Map the incoming form fields to the array expected by the Repository
    $data = [
        'name'        => $_POST['edit_name'] ?? '',
        'price'       => $_POST['edit_price'] ?? 0,
        'width_inch'  => $_POST['edit_width'] ?? 0,
        'height_inch' => $_POST['edit_height'] ?? 0,

        // FIX: read status from form instead of forcing active
        'is_active'   => isset($_POST['edit_is_active']) ? (int)$_POST['edit_is_active'] : 1
    ];

    if ($id > 0 && $tab !== '') {
        $success = $service->updateOption($tab, $id, $data, $_FILES);
        header("Location: ../admin/admin_custom_frame_options.php?tab=$tab&success=" . ($success ? "1" : "0"));
        exit();
    }
}


// ── DELETE ───────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $tab = $_POST['tab'] ?? '';
    $id  = (int)($_POST['option_id'] ?? 0);

    $success = ($id > 0) ? $service->deleteOption($tab, $id) : false;

    header("Location: ../admin/admin_custom_frame_options.php?tab=$tab&success=" . ($success ? '1' : '0'));
    exit();
}