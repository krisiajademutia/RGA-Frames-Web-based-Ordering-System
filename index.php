<?php
    session_start();

    if (isset($_SESSION['user_id'])) {
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN') {
            header("Location: admin/admin_dashboard.php");
        } else {
            header("Location: customer/customer_dashboard.php");
        }
        exit();
    }

    // Configuration / SEO variables (Optional)
    $pageTitle = "RGA Frames - Custom Framing & Printing";
    
    include 'includes/idx_header.php';
    include 'includes/idx_hero.php';
    include 'includes/idx_gallery.php';
    include 'includes/idx_services.php';
    include 'includes/idx_features.php';
    include 'includes/idx_footer.php';
?>