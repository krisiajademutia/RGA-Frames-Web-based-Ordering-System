<?php
require_once __DIR__ . '/../config/db_connect.php';

$repoDir = __DIR__ . '/../classes/Option/Repository/';

// 1. Load the Interface FIRST
require_once $repoDir . 'OptionRepositoryInterface.php';

// 2. Load all concrete Repositories
foreach (glob($repoDir . "*.php") as $filename) {
    require_once $filename;
}

// 3. Load the Service
require_once __DIR__ . '/../classes/Option/OptionService.php';

$upload_dir = __DIR__ . '/../uploads/';

$active_tab = $_GET['tab'] ?? 'frame_types';

$tabs = [
    'frame_types'     => ['label' => 'Frame Type',     'suffix' => 'Type'],
    'frame_designs'   => ['label' => 'Frame Design',   'suffix' => 'Design'],
    'frame_colors'    => ['label' => 'Frame Color',    'suffix' => 'Color'],
    'frame_sizes'     => ['label' => 'Frame Size',     'suffix' => 'Size'],
    'matboard_colors' => ['label' => 'Matboard Color', 'suffix' => 'Color'],
    'mount_types'     => ['label' => 'Mount Type',     'suffix' => 'Type'],
    'paper_types'     => ['label' => 'Paper Type',     'suffix' => 'Type'],
];

if (!isset($tabs[$active_tab])) { $active_tab = 'frame_types'; }

$tab_label = $tabs[$active_tab]['label'];
$suffix    = $tabs[$active_tab]['suffix'];

$service = new OptionService();

// Pass $upload_dir to every repository that handles image uploads
$service->registerRepository('frame_types',     new FrameTypeRepository($conn,     $upload_dir));
$service->registerRepository('frame_designs',   new FrameDesignRepository($conn,   $upload_dir));
$service->registerRepository('frame_colors',    new FrameColorRepository($conn,    $upload_dir));
$service->registerRepository('frame_sizes',     new FrameSizeRepository($conn));
$service->registerRepository('matboard_colors', new MatboardColorRepository($conn, $upload_dir));
$service->registerRepository('mount_types',     new MountTypeRepository($conn));
$service->registerRepository('paper_types',     new PaperTypeRepository($conn));

$res   = $service->fetchOptions($active_tab);
$count = $res ? $res->num_rows : 0;