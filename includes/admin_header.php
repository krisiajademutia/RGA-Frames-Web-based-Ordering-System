<?php
// includes/admin_header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../config/db_connect.php';
$display_name = htmlspecialchars($_SESSION['first_name'] ?? 'Admin');
?>
<body class="admn-body">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/RGA-Frames-Web-based-Ordering-System-main/assets/css/style.css">

<header class="admn-hdr-container" id="mainHeader">
    <div class="admn-hdr-left">
        <a href="index.php" style="text-decoration: none; display: flex; align-items: center; gap: 0.3rem;">
            <div class="admn-hdr-logo">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#ffff" viewBox="2 2 20 20">
                    <path d="M3 16c0 .34.18.67.47.85l8 5a1.01 1.01 0 0 0 1.06 0l8-5c.29-.18.47-.5.47-.85V8c0-.34-.18-.67-.47-.85l-8-5c-.32-.2-.74-.2-1.06 0l-8 5c-.29.18-.47.5-.47.85zm2-6.53 6 3.6v6.13l-6-3.75zm8 9.73v-6.13l6-3.6v5.98zM12 4.18l5.84 3.65-5.84 3.5-5.84-3.5z"></path>
                </svg>
            </div>
            <div class="admn-hdr-brand">
                <h1>RGA Frames</h1>
            </div>
        </a>
    </div>

    <button class="admn-mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <nav class="admn-hdr-nav" id="mainNav">
        <a href="/RGA-Frames-Web-based-Ordering-System-main/admin/admin_dashboard.php" class="admn-hdr-nav-link">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="/RGA-Frames-Web-based-Ordering-System-main/admin/admin_orders.php" class="admn-hdr-nav-link">
            <i class="fas fa-shopping-bag"></i> Orders
        </a>
        <a href="/RGA-Frames-Web-based-Ordering-System-main/admin/admin_post_frames.php" class="admn-hdr-nav-link">
            <i class="fas fa-image"></i> Post Frames
        </a>
        <a href="/RGA-Frames-Web-based-Ordering-System-main/admin/admin_custom_frame_options.php" class="admn-hdr-nav-link">
            <i class="fas fa-tools"></i> Frame Options
        </a>
        <a href="/RGA-Frames-Web-based-Ordering-System-main/logout.php" class="admn-hdr-nav-link d-lg-none logout-mobile">
            <i class="fas fa-power-off"></i> Log out
        </a>
    </nav>

    <div class="admn-hdr-user-area">
        <div class="dropdown">
            <a class="admn-hdr-dropdown-toggle dropdown-toggle" href="#"
               id="hdrAdminDrop" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <svg width="36px" height="36px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.5" d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" fill="#489883 "></path> 
                    <path d="M16.807 19.0112C15.4398 19.9504 13.7841 20.5 12 20.5C10.2159 20.5 8.56023 19.9503 7.193 19.0111C6.58915 18.5963 6.33109 17.8062 6.68219 17.1632C7.41001 15.8302 8.90973 15 12 15C15.0903 15 16.59 15.8303 17.3178 17.1632C17.6689 17.8062 17.4108 18.5964 16.807 19.0112Z" fill="#004030 "></path> 
                    <path d="M12 12C13.6569 12 15 10.6569 15 9C15 7.34315 13.6569 6 12 6C10.3432 6 9.00004 7.34315 9.00004 9C9.00004 10.6569 10.3432 12 12 12Z" fill="#004030 "></path> 
                </svg>
                <span class="d-none d-lg-inline"><?php echo $display_name; ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end admn-hdr-dropdown-menu" aria-labelledby="hdrAdminDrop">
                <li>
                    <a class="dropdown-item admn-hdr-logout-item" href="/RGA-Frames-Web-based-Ordering-System-main/logout.php">
                        <i class="fas fa-power-off me-2"></i> Log out
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="admn-mobile-overlay" id="mobileOverlay"></div>
</header>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/RGA-Frames-Web-based-Ordering-System-main/assets/js/index_animations.js"></script>
</body>