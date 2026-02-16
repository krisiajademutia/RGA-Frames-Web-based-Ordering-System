<?php
// includes/customer_header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../config/db_connect.php';
$user_id = $_SESSION['user_id'] ?? 0;
$display_name = htmlspecialchars($_SESSION['first_name'] ?? 'Customer');

$notif_count = 0;
if ($user_id > 0) {
    $notif_res = $conn->query("SELECT COUNT(*) as total FROM tbl_notifications WHERE user_id = '$user_id' AND is_read = 0");
    if ($notif_res) {
        $row = $notif_res->fetch_assoc();
        $notif_count = $row['total'] ?? 0;
    }
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