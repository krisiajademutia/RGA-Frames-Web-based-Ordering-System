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

// Determine current page to highlight the active nav item
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">

<header class="cust-hdr-container">
   <div class="cust-hdr-left">
    <a href="customer_dashboard.php" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem; position: relative;">
        <div class="cust-hdr-logo" style="position: relative; width: 50px; height: 50px;">
            <img 
                src="../assets/img/rga_logo2.jpg" 
                alt="RGA Frames and Photo Studio Logo" 
                style="width: 100%; height: 100%; object-fit: contain; display: block; border-radius: 8px;"
            >
            <div style="position: absolute; inset: 0; border-radius: 50%; pointer-events: none;"></div>
        </div>
        <div class="cust-hdr-brand">
            <h1>RGA Frames</h1>
        </div>
    </a>
</div>

    <nav class="cust-hdr-nav">
        <a href="customer_dashboard.php" class="cust-hdr-nav-link <?php echo ($current_page == 'customer_dashboard.php') ? 'cust-hdr-active' : ''; ?>">
            <i class="fas fa-home"></i> Home
        </a>
        
        <div class="dropdown">
            <a class="cust-hdr-nav-link dropdown-toggle <?php echo (strpos($current_page, 'customer_shop') !== false) ? 'cust-hdr-active' : ''; ?>" href="#" id="custHdrBrowseDrop" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-shopping-bag"></i> Browse
            </a>
            <ul class="dropdown-menu cust-hdr-dropdown-menu" aria-labelledby="custHdrBrowseDrop">
                <li><a class="dropdown-item" href="customer_shop_readymade.php">Ready-Made</a></li>
                <li><a class="dropdown-item" href="customer_shop_custom.php">Custom Frames</a></li>
                <li><a class="dropdown-item" href="customer_shop_printing.php">Printing Services</a></li>
            </ul>
        </div>

        <a href="customer_orders.php" class="cust-hdr-nav-link <?php echo ($current_page == 'customer_orders.php') ? 'cust-hdr-active' : ''; ?>">
            <i class="fas fa-list-alt"></i> My Orders
        </a>
    </nav>

    <div class="cust-hdr-right">
        <a href="customer_cart.php" class="cust-hdr-icon-btn">
            <i class="fas fa-shopping-cart"></i>
            <?php if (isset($cart_count) && $cart_count > 0): ?>
                <span class="cust-hdr-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a>

        <a href="customer_notifications.php" class="cust-hdr-icon-btn">
            <i class="fas fa-bell"></i>
            <?php if ($notif_count > 0): ?>
                <span class="cust-hdr-badge"><?php echo $notif_count; ?></span>
            <?php endif; ?>
        </a>

        <div class="dropdown cust-hdr-user-area">
            <a class="cust-hdr-dropdown-toggle dropdown-toggle" href="#" id="custHdrUserDrop" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle"></i>
                <?php echo $display_name; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end cust-hdr-dropdown-menu" aria-labelledby="custHdrUserDrop">
                <li>
                    <a class="dropdown-item cust-hdr-logout-item" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Log out
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
        crossorigin="anonymous"></script>