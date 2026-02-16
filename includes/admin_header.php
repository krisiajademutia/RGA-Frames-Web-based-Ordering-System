<<<<<<< Updated upstream
=======
<?php
// includes/admin_header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../config/db_connect.php';
$display_name = htmlspecialchars($_SESSION['first_name'] ?? 'Admin');
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/rga_frames/assets/css/style.css">

<header class="admn-hdr-container">
    <div class="admn-hdr-left">
        <div class="admn-hdr-logo"><i class="fas fa-layer-group"></i></div>
        <div class="admn-hdr-brand">
            <h1>RGA Frames</h1>
            <p>Management System</p>
        </div>
    </div>

    <nav class="admn-hdr-nav">
        <a href="/rga_frames/admin/admin_dashboard.php" class="admn-hdr-nav-link">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="/rga_frames/admin/admin_orders.php" class="admn-hdr-nav-link">
            <i class="fas fa-shopping-bag"></i> Orders
        </a>
        <a href="/rga_frames/admin/admin_post_frames.php" class="admn-hdr-nav-link">
            <i class="fas fa-image"></i> Post Frames
        </a>
        <a href="/rga_frames/admin/admin_custom_frame_options.php" class="admn-hdr-nav-link">
            <i class="fas fa-tools"></i> Frame Options
        </a>
    </nav>

    <div class="admn-hdr-user-area">
        <div class="dropdown">
            <a class="admn-hdr-dropdown-toggle dropdown-toggle" href="#" 
               id="hdrAdminDrop" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle"></i>
                <?php echo $display_name; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end admn-hdr-dropdown-menu" aria-labelledby="hdrAdminDrop">
                <li>
                    <a class="dropdown-item admn-hdr-logout-item" href="/rga_frames/logout.php">
                        <i class="fas fa-power-off me-2"></i> Log out
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>
>>>>>>> Stashed changes
