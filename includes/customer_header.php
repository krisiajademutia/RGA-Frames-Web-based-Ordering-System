<?php
// includes/customer_header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../config/db_connect.php';

$current_user_id = $_SESSION['user_id'] ?? 0;
$display_name = htmlspecialchars($_SESSION['first_name'] ?? 'Customer');

$notif_count = 0;
$cart_count = 0;

if ($current_user_id > 0) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM tbl_notifications WHERE customer_id = ? AND is_read = 0");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $notif_count = $row['total'];
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM tbl_cart WHERE customer_id = ?");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $cart_count = $row['total'];
    }
    $stmt->close();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">

<header class="cust-hdr-container">

    <!-- LEFT: BRAND -->
    <div class="cust-hdr-left">
        <a href="customer_dashboard.php" class="cust-hdr-brand-link">
            <div class="cust-hdr-logo">
                <img src="../assets/img/rga_logo.png" alt="RGA Frames Logo">
            </div>
            <div class="cust-hdr-brand">
                <h1>RGA Frames</h1>
            </div>
        </a>
    </div>

    <!-- DESKTOP NAV -->
    <nav class="cust-hdr-nav d-none d-lg-flex">

        <a href="customer_dashboard.php"
           class="cust-hdr-nav-link <?php echo ($current_page == 'customer_dashboard.php') ? 'cust-hdr-active' : ''; ?>">
            <i class="fas fa-home"></i> Home
        </a>

        <div class="dropdown">
            <a class="cust-hdr-nav-link dropdown-toggle <?php echo (strpos($current_page, 'customer_shop') !== false) ? 'cust-hdr-active' : ''; ?>"
               href="#" id="custHdrBrowseDrop" role="button" data-bs-toggle="dropdown">
                <i class="fas fa-shopping-bag"></i> Browse
            </a>

            <ul class="dropdown-menu cust-hdr-dropdown-menu">
                <li><a class="dropdown-item" href="customer_shop_readymade.php">Ready-Made</a></li>
                <li><a class="dropdown-item" href="customer_shop_custom.php">Custom Frames</a></li>
                <li><a class="dropdown-item" href="customer_shop_printing.php">Printing Services</a></li>
            </ul>
        </div>

        <a href="customer_orders.php"
           class="cust-hdr-nav-link <?php echo ($current_page == 'customer_orders.php') ? 'cust-hdr-active' : ''; ?>">
            <i class="fas fa-list-alt"></i> My Orders
        </a>

    </nav>

    <!-- RIGHT ACTIONS -->
    <div class="cust-hdr-right-actions">

        <a href="customer_cart.php" class="cust-hdr-icon-btn">
            <i class="fas fa-shopping-cart"></i>
            <?php if ($cart_count > 0): ?>
                <span class="cust-hdr-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a>

        <a href="customer_notifications.php" class="cust-hdr-icon-btn">
            <i class="fas fa-bell"></i>
            <?php if ($notif_count > 0): ?>
                <span class="cust-hdr-badge"><?php echo $notif_count; ?></span>
            <?php endif; ?>
        </a>

        <div class="dropdown cust-hdr-user-area d-none d-lg-block">
            <a class="cust-hdr-dropdown-toggle dropdown-toggle" href="#" id="custHdrUserDrop" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle"></i> <?php echo $display_name; ?>
            </a>

            <ul class="dropdown-menu dropdown-menu-end cust-hdr-dropdown-menu">
                <li>
                    <a class="dropdown-item cust-hdr-profile-item" href="../customer/customer_profile.php">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider"> </li>
                <li>
                    <a class="dropdown-item cust-hdr-logout-item" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Log out
                    </a>
                </li>
            </ul>
        </div>

        <!-- HAMBURGER BUTTON -->
        <button class="cust-mobile-menu-toggle d-lg-none" id="custMenuToggle">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>

</header>

<!-- MOBILE OVERLAY -->
<div class="cust-mobile-overlay" id="custMobileOverlay"></div>

<!-- MOBILE SIDEBAR MENU -->
<div class="cust-mobile-menu" id="custMobileMenu">

    <nav class="cust-hdr-nav-mobile">
        <a href="customer_profile.php" class="cust-hdr-nav-link">
            <i class="fas fa-user"></i> Profile
        </a>

        <a href="customer_dashboard.php" class="cust-hdr-nav-link">
            <i class="fas fa-home"></i> Home
        </a>

        <a href="customer_shop_readymade.php" class="cust-hdr-nav-link">
            Ready-Made Frames
        </a>

        <a href="customer_shop_custom.php" class="cust-hdr-nav-link">
            Custom Frames
        </a>

        <a href="customer_shop_printing.php" class="cust-hdr-nav-link">
            Printing Services
        </a>

        <a href="customer_orders.php" class="cust-hdr-nav-link">
            <i class="fas fa-list-alt"></i> My Orders
        </a>

        <a href="customer_cart.php" class="cust-hdr-nav-link">
            <i class="fas fa-shopping-cart"></i> Cart
        </a>

        <a href="customer_notifications.php" class="cust-hdr-nav-link">
            <i class="fas fa-bell"></i> Notifications
        </a>

        <a href="../logout.php" class="cust-hdr-nav-link text-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>

    </nav>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

const toggle = document.getElementById("custMenuToggle");
const menu = document.getElementById("custMobileMenu");
const overlay = document.getElementById("custMobileOverlay");

toggle.addEventListener("click", () => {
    menu.classList.toggle("active");
    overlay.classList.toggle("active");
});

overlay.addEventListener("click", () => {
    menu.classList.remove("active");
    overlay.classList.remove("active");
});

</script>