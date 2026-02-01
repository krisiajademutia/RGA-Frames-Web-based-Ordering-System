<?php
// customer_header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

$user_id = $_SESSION['user_id'] ?? 0;
$display_name = htmlspecialchars($_SESSION['first_name'] ?? 'Customer');

// Get unread notification count
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

<style>
    :root {
        --brand-brown:    #4a2c18;
        --brand-gold:     #c19a5f;
        --brand-cream:    #fffdf7;
        --text-dark:      #1a0f09;
    }

    .navbar {
        padding: 0.6rem 0 !important;
        min-height: 60px;
        border-bottom: 3px solid var(--brand-gold);
        background-color: white !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .navbar-brand {
        font-size: 1.9rem;
        font-weight: 700;
        color: var(--brand-brown);
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .nav-link {
        font-size: 1.1rem;
        font-weight: 500;
        color: var(--brand-brown) !important;
        padding: 0.45rem 1rem !important;
    }

    .nav-link:hover {
        color: #3a1f10 !important;
    }

    .btn-outline-brown {
        border: 2px solid var(--brand-brown);
        color: var(--brand-brown);
        font-weight: 600;
        padding: 0.45rem 1.2rem;
    }

    .btn-outline-brown:hover {
        background-color: var(--brand-brown);
        color: white;
    }

    .badge-notif {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        position: absolute;
        top: -6px;
        right: -10px;
    }

    .navbar-toggler {
        border: none;
        padding: 0.35rem 0.65rem;
    }
</style>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="customer_dashboard.php">
            <i class="fas fa-box-open"></i>
            RGA Frames
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCustomer" aria-controls="navbarCustomer" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarCustomer">
            <ul class="navbar-nav align-items-center gap-3 gap-lg-4">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link" href="customer_dashboard.php">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>

                <!-- Shop -->
                <li class="nav-item">
                    <a class="nav-link" href="customer_orders.php">
                        <i class="fas fa-store me-1"></i> My Order
                    </a>
                </li>

                <!-- Cart -->
                <li class="nav-item position-relative">
                    <a class="nav-link" href="customer_cart.php">
                        <i class="fas fa-shopping-cart me-1"></i> Cart
                        <?php if (isset($cart_count) && $cart_count > 0): ?>
                            <span class="badge-notif"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Notifications -->
                <li class="nav-item position-relative">
                    <a class="nav-link" href="customer_notifications.php">
                        <i class="fas fa-bell me-1"></i> Notifications
                        <?php if ($notif_count > 0): ?>
                            <span class="badge-notif"><?php echo $notif_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- User Dropdown (only Log out) -->
                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user me-1"></i> <?php echo $display_name; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item text-danger" href="index.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Log out
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>