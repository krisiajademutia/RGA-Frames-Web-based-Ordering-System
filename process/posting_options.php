<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';
$repoDir = __DIR__ . '/../classes/Option/Repository/';

require_once $repoDir . 'OptionRepositoryInterface.php';

foreach (glob($repoDir . "*.php") as $filename) { 
    require_once $filename; 
}
require_once __DIR__ . '/../classes/Option/OptionService.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_option'])) {
    $active_tab = $_GET['tab'] ?? 'frame_types';
    
    // Ensure the path is correct relative to the process folder
    $upload_dir = "../uploads/"; 
    
    // Prepare Data
    $data = $_POST;
    // Ensure status is handled correctly for the DB
    $data['is_active'] = (isset($_POST['is_active']) && $_POST['is_active'] == '1') ? 1 : 0;

    // Dependency Injection & Service Setup
    $service = new OptionService();
    
    // Registering repositories with the database connection
    // Passing $upload_dir only to repositories that handle file uploads
    $service->registerRepository('frame_types', new FrameTypeRepository($conn, $upload_dir));
    $service->registerRepository('frame_designs', new FrameDesignRepository($conn));
    $service->registerRepository('frame_colors', new FrameColorRepository($conn, $upload_dir));
    $service->registerRepository('frame_sizes', new FrameSizeRepository($conn));
    $service->registerRepository('matboard_colors', new MatboardColorRepository($conn, $upload_dir));
    $service->registerRepository('mount_types', new MountTypeRepository($conn));
    $service->registerRepository('paper_types', new PaperTypeRepository($conn));

    /**
     * DEBUG/FIX: We pass $_POST and $_FILES to the service.
     * The service will then delegate these to the specific repository's create() method.
     */
    $success = $service->addOption($active_tab, $data, $_FILES);

    // Redirect back to the admin page with status
    if ($success) {
        header("Location: ../admin/admin_custom_frame_options.php?tab=$active_tab&success=1");
    } else {
        // If it fails, we pass an error flag. 
        // Ensure your FrameColorRepository is looking for $_FILES['color_image']
        header("Location: ../admin/admin_custom_frame_options.php?tab=$active_tab&error=1");
    }
    exit();
}