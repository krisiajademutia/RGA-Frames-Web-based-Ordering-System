<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Correct path: From /process/ to /config/
$conn_file = __DIR__ . '/../config/db_connect.php';

if (!file_exists($conn_file)) {
    die("Fatal Error: db_connect.php not found at " . $conn_file);
}

include $conn_file;

// Tab logic
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'frame_types';
$tab_label = str_replace('_', ' ', $active_tab);

// Table Mapping
$table_map = [
    'frame_types'     => 'tbl_frame_types',
    'frame_designs'   => 'tbl_frame_designs',
    'frame_colors'    => 'tbl_frame_colors',
    'frame_sizes'     => 'tbl_frame_sizes',
    'matboard_colors' => 'tbl_matboard_colors',
    'mount_types'     => 'tbl_mount_type',
    'paper_types'     => 'tbl_paper_type'
];

$table = $table_map[$active_tab] ?? 'tbl_frame_types';

// Fetch Results
$res = $conn->query("SELECT * FROM $table ORDER BY 1 DESC");
$count = ($res) ? $res->num_rows : 0;
$suffix = str_replace('frame ', '', str_replace('_', ' ', $active_tab));