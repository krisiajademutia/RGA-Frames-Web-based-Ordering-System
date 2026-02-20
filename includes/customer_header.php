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
    // 1. Fix: Count Notifications (Changed 'user_id' to 'customer_id')
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM tbl_notifications WHERE customer_id = ? AND is_read = 0");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $notif_count = $row['total'];
    }
    $stmt->close();

    // 2. Fix: Calculate Cart Count (Added this query because you use $cart_count in HTML)
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM tbl_cart WHERE customer_id = ?");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $cart_count = $row['total'];
    }
    $stmt->close();
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">

<header class="cust-hdr-container">
    <div class="cust-hdr-left">
        <div class="cust-hdr-logo"><i class="fas fa-box-open"></i></div>
        <div class="cust-hdr-brand">
            <h1>RGA Frames</h1>
            <p>Customer Portal</p>
        </div>
    </div>

    <nav class="cust-hdr-nav">
        <a href="customer_dashboard.php" class="cust-hdr-nav-link">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="customer_orders.php" class="cust-hdr-nav-link">
            <i class="fas fa-store"></i> My Orders
        </a>
        <a href="customer_cart.php" class="cust-hdr-nav-link">
            <i class="fas fa-shopping-cart"></i> Cart
            <?php if (isset($cart_count) && $cart_count > 0): ?>
                <span class="cust-hdr-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="customer_notifications.php" class="cust-hdr-nav-link">
            <i class="fas fa-bell"></i> Notifications
            <?php if ($notif_count > 0): ?>
                <span class="cust-hdr-badge"><?php echo $notif_count; ?></span>
            <?php endif; ?>
        </a>
    </nav>

    <div class="cust-hdr-user-area">
        <div class="dropdown">
            <a class="cust-hdr-dropdown-toggle dropdown-toggle" href="#" 
               id="custHdrDrop" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle"></i>
                <?php echo $display_name; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end cust-hdr-dropdown-menu" aria-labelledby="custHdrDrop">
                <li>
                    <a class="dropdown-item cust-hdr-logout-item" href="../logout.php">
                        <i class="fas fa-power-off me-2"></i> Log out
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>