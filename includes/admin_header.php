<?php
// includes/admin_header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
include_once __DIR__ . '/../config/db_connect.php';
$display_name = htmlspecialchars($_SESSION['first_name'] ?? 'Admin');
$notif_count = 0;
if (isset($_SESSION['user_id'])) {
    $admin_id = (int)$_SESSION['user_id'];
    $notifStmt = $conn->prepare("
        SELECT COUNT(*) as unread_count 
        FROM tbl_notifications 
        WHERE customer_id IS NULL AND is_read = 0
    ");
    
    if ($notifStmt) {
        $notifStmt->execute();
        $notif_count = $notifStmt->get_result()->fetch_assoc()['unread_count'] ?? 0;
        $notifStmt->close();
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<header class="admn-hdr-container" id="mainHeader">

    <div class="admn-hdr-left">
        <a href="index.php" class="admn-hdr-brand-link">
            <div class="cust-hdr-logo">
                <img src="../assets/img/rga_logo.png" alt="RGA Frames Logo">
            </div>
            <div class="cust-hdr-brand">
                <h1>RGA Frames</h1>
            </div>
        </a>
    </div>

    <nav class="admn-hdr-nav d-none d-lg-flex">
        <a href="admin_dashboard.php"
           class="admn-hdr-nav-link <?php echo ($current_page == 'admin_dashboard.php') ? 'admn-hdr-active' : ''; ?>">
            <i class="fas fa-home"></i> My Dashboard
        </a>

        <a href="admin_orders.php"
           class="admn-hdr-nav-link <?php echo ($current_page == 'admin_orders.php') ? 'admn-hdr-active' : ''; ?>">
            <i class="fas fa-list-alt"></i> Orders
        </a>

        <a href="admin_post_frames.php"
           class="admn-hdr-nav-link <?php echo ($current_page == 'admin_post_frames.php') ? 'admn-hdr-active' : ''; ?>">
            <i class="fas fa-plus"></i> Post Frames
        </a>

        <a href="admin_custom_frame_options.php"
           class="admn-hdr-nav-link <?php echo ($current_page == 'admin_custom_frame_options.php') ? 'admn-hdr-active' : ''; ?>">
            <i class="fas fa-toolbox"></i> Frame Options
        </a>
    </nav>

    <div class="admn-hdr-right-actions">
        <a href="admin_notification.php" class="admn-hdr-icon-btn">
            <i class="fas fa-bell"></i>
            <?php if ($notif_count > 0): ?>
                <span class="cust-hdr-badge"><?php echo $notif_count; ?></span>
            <?php endif; ?>
        </a>

        <div class="dropdown admn-hdr-user-area d-none d-lg-block">
            <a class="admn-hdr-dropdown-toggle dropdown-toggle" href="#" id="admnHdrUserDrop" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle"></i> <?php echo $display_name; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end admn-hdr-dropdown-menu">
                <li>
                    <a class="dropdown-item admn-hdr-logout-item" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Log out
                    </a>
                </li>
            </ul>
        </div>

        <button class="admn-mobile-menu-toggle d-lg-none" id="admnMenuToggle">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<div class="admn-mobile-overlay" id="admnMobileOverlay"></div>

<div class="admn-mobile-menu" id="admnMobileMenu">
    <nav class="admn-hdr-nav-mobile">
        <a href="admin_dashboard.php" class="admn-hdr-nav-link">
            <i class="fas fa-home"></i> My Dashboard
        </a>

        <a href="admin_orders.php" class="admn-hdr-nav-link">
            <i class="fas fa-list-alt"></i> Orders
        </a>

        <a href="admin_post_frames.php" class="admn-hdr-nav-link">
            <i class="fas fa-plus"></i> Post Frames
        </a>

        <a href="admin_custom_frame_options.php" class="admn-hdr-nav-link">
            <i class="fas fa-toolbox"></i> Frame Options
        </a>
        
        <a href="admin_notification.php" class="admn-hdr-nav-link">
            <i class="fas fa-bell"></i> Notifications
        </a>

        <a href="../logout.php" class="admn-hdr-nav-link text-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const toggle = document.getElementById("admnMenuToggle");
    const menu = document.getElementById("admnMobileMenu");
    const overlay = document.getElementById("admnMobileOverlay");

    toggle.addEventListener("click", () => {
        menu.classList.toggle("active");
        overlay.classList.toggle("active");
    });

    overlay.addEventListener("click", () => {
        menu.classList.remove("active");
        overlay.classList.remove("active");
    });
</script>