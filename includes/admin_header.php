<?php
// includes/admin_header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Exit /includes, enter /config
include_once __DIR__ . '/../config/db_connect.php';
$display_name = htmlspecialchars($_SESSION['first_name'] ?? 'Admin');

if (empty(trim($display_name)) || $display_name === '') {
    $display_name = 'Admin';
}

$unread_count = 0; // ← replace with real count logic later if needed
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    :root {
        --palette-green: #A7C957;
        --palette-gold:  #B89655;
        --palette-brown: #795338;
        --palette-cream: #fdfcf9;
        --text-dark:     #2d241e;
    }

    .admin-header {
        position: fixed;
        top: 0; left: 0; right: 0;
        background: white;
        min-height: 70px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1.5rem;
        box-shadow: 0 2px 12px rgba(121, 83, 56, 0.08);
        z-index: 1000;
        border-bottom: 3px solid var(--palette-gold);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .logo-box {
        width: 42px;
        height: 42px;
        background: var(--palette-brown);
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .brand-text h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--palette-brown);
    }

    .brand-text p {
        margin: 0;
        font-size: 0.75rem;
        color: var(--palette-gold);
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    .nav-pills {
        display: flex;
        gap: 0.5rem;
    }

    .nav-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        text-decoration: none;
        color: var(--palette-brown);
        background: #f8f5f0;
        transition: all 0.2s;
    }

    .nav-btn:hover {
        background: var(--palette-gold);
        color: white;
        transform: translateY(-1px);
    }

    .user-section {
        display: flex;
        align-items: center;
        gap: 1.2rem;
    }

    .user-name {
        font-weight: 600;
        color: var(--palette-brown);
        font-size: 1rem;
    }

    .dropdown-toggle {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--palette-brown) !important;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
    }

    .dropdown-menu {
        border-radius: 10px;
        border: 1px solid #e9e3d8;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        z-index: 1050; /* higher than fixed header */
    }

    .dropdown-item.text-danger:hover {
        background-color: #f8d7da;
    }
</style>

<header class="admin-header">
    <div class="header-left">
        <div class="logo-box"><i class="fas fa-layer-group"></i></div>
        <div class="brand-text">
            <h1>RGA Frames</h1>
            <p>Admin Panel</p>
        </div>
    </div>

    <div class="nav-pills">
        <a href="/rga_frames/admin/admin_dashboard.php" class="nav-btn">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="/rga_frames/admin/admin_orders.php" class="nav-btn">
            <i class="fas fa-clipboard-list"></i> Orders
        </a>
        <a href="/rga_frames/admin/admin_post_frames.php" class="nav-btn">
            <i class="fas fa-plus"></i> Post Frames
        </a>
        <a href="/rga_frames/admin/admin_custom_frame_options.php" class="nav-btn">
            <i class="fas fa-sliders-h"></i> Custom Options
        </a>
    </div>

    <div class="user-section">
    <div class="dropdown">
        <a class="dropdown-toggle nav-btn" href="#" 
           id="adminDropdown" 
           role="button" 
           data-bs-toggle="dropdown" 
           aria-expanded="false">
            <i class="fas fa-user-shield me-1"></i>
            <?php echo $display_name; ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
            <li>
                <a class="dropdown-item text-danger" href="/rga_frames/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Log out
                </a>
            </li>
        </ul>
    </div>
</div>
</header>