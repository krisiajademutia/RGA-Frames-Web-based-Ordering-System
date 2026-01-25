<?php
// admin_header.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// include 'db_connect.php'; 
$unread_count = 0; 
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    :root {
        --palette-green: #A7C957;
        --palette-gold: #B89655;
        --palette-brown: #795338;
        --palette-cream: #fdfcf9;
    }

    /* --- UPDATED HEADER LAYOUT --- */
    .admin-header {
        position: fixed; top: 0; left: 0; right: 0;
        background: white; height: 80px;
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 30px;
        box-shadow: 0 4px 12px rgba(121, 83, 56, 0.08); /* Brown-tinted shadow */
        z-index: 1000;
        font-family: 'Inter', sans-serif;
        border-bottom: 2px solid var(--palette-gold);
    }

    .header-left { display: flex; align-items: center; gap: 15px; }
    .logo-box {
        width: 45px; height: 45px;
        background: var(--palette-brown); /* Replaced blue with brown */
        color: white; border-radius: 8px; 
        display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .brand-text h1 { margin: 0; font-size: 18px; font-weight: 800; color: var(--palette-brown); letter-spacing: -0.5px; }
    .brand-text p { margin: 0; font-size: 11px; color: var(--palette-gold); text-transform: uppercase; letter-spacing: 1px; }

    .nav-pills { display: flex; gap: 10px; }
    .nav-btn {
        text-decoration: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600;
        transition: all 0.2s ease; display: flex; align-items: center; gap: 6px;
    }
    .nav-btn:hover { transform: translateY(-2px); }

    /* Button Colors Synced to Palette */
    .btn-home { background: #f4f1ea; color: var(--palette-brown); } 
    .btn-green { background: rgba(167, 201, 87, 0.1); color: #6a8235; } /* Green Tint */
    .btn-gold { background: rgba(184, 150, 85, 0.1); color: var(--palette-brown); } /* Gold Tint */
    .btn-brown { background: var(--palette-brown); color: white; } /* Solid Brown */

    /* --- RIGHT SECTION --- */
    .header-right { display: flex; align-items: center; gap: 20px; }
    .notif-wrapper { position: relative; }
    .notif-btn {
        position: relative; color: var(--palette-brown); font-size: 20px;
        cursor: pointer; background: none; border: none; padding: 5px;
    }
    
    /* THE 420px DROPDOWN */
    .notif-dropdown {
        display: none;
        position: absolute;
        right: 0; top: 60px; width: 420px; 
        background: white; border-radius: 12px;
        box-shadow: 0 15px 35px rgba(121, 83, 56, 0.15);
        z-index: 2000; border: 1px solid #e9e3d8;
        overflow: hidden;
    }

    .dropdown-header {
        padding: 20px 25px; border-bottom: 1px solid #f1ede4; 
        display: flex; justify-content: space-between; align-items: center;
        background: #faf9f6;
    }
    .dropdown-header span { color: var(--palette-brown); }

    .dropdown-body { max-height: 400px; overflow-y: auto; background: white; }

    .mark-read { color: var(--palette-gold) !important; font-weight: 600; }

    .view-all-link {
        display: block; text-align: center; padding: 15px;
        background: var(--palette-brown); color: white; 
        font-weight: 700; font-size: 12px; text-decoration: none;
        text-transform: uppercase; letter-spacing: 1px;
    }
    .view-all-link:hover { background: #5a3d29; }

    .user-section { display: flex; align-items: center; gap: 15px; padding-left: 20px; border-left: 1px solid #e9e3d8; }
    .user-name { font-weight: 700; color: var(--palette-brown); font-size: 14px; }
    
    .btn-logout { 
        text-decoration: none; color: #d97706; font-size: 12px; font-weight: 700; 
        border: 1.5px solid #fde68a; padding: 6px 12px; border-radius: 6px; 
        transition: 0.2s;
    }
    .btn-logout:hover { background: #fffbeb; }

    .notif-badge {
        position: absolute; top: 0; right: 0;
        background: var(--palette-green); color: white;
        font-size: 10px; width: 16px; height: 16px;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        border: 2px solid white;
    }
</style>

<header class="admin-header">
    <div class="header-left">
        <div class="logo-box"><i class="fas fa-layer-group"></i></div>
        <div class="brand-text">
            <h1>RGA Frames</h1>
            <p>Administrative Access</p>
        </div>
    </div>

    <div class="nav-pills">
        <a href="admin_dashboard.php" class="nav-btn btn-home"><i class="fas fa-home"></i> Dashboard</a>
        <a href="admin_orders.php" class="nav-btn btn-gold"><i class="fas fa-clipboard-list"></i> Orders</a>
        <a href="admin_post_frames.php" class="nav-btn btn-green"><i class="fas fa-plus"></i> Post Frames</a>
        <a href="admin_custom_frame_options.php" class="nav-btn btn-brown"><i class="fas fa-sliders-h"></i> Options</a>
    </div>

    <div class="header-right">
        <div class="notif-wrapper">
            <button class="notif-btn" onclick="toggleNotifications()">
                <i class="fas fa-bell"></i>
                <?php if($unread_count > 0): ?>
                    <span class="notif-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </button>

            <div id="notificationPopup" class="notif-dropdown">
                <div class="dropdown-header">
                    <span style="font-weight:700;">Notifications</span>
                    <span class="mark-read" style="font-size:12px; cursor:pointer;">Mark all read</span>
                </div>
                
                <div class="dropdown-body">
                    <?php if ($unread_count == 0): ?>
                        <div class="empty-notif" style="padding: 60px 40px; text-align: center; color: var(--palette-gold);">
                            <i class="far fa-bell-slash" style="font-size: 50px; display: block; margin-bottom: 20px; opacity: 0.3;"></i>
                            <span style="font-size: 14px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; color: var(--palette-brown);">KANG MAY NI</span>
                        </div>
                    <?php else: ?>
                        <?php endif; ?>
                </div>

                <a href="admin_notification_list.php" class="view-all-link">
                    VIEW ALL NOTIFICATIONS
                </a>
            </div>
        </div>

        <div class="user-section">
            <span class="user-name">Admin: <?php echo htmlspecialchars($_SESSION['first_name'] ?? 'RGA'); ?></span>
            <a href="index.php" class="btn-logout">Logout</a>
        </div>
    </div>
</header>

<script>
    function toggleNotifications() {
        const popup = document.getElementById('notificationPopup');
        popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
    }

    // Close when clicking outside
    window.onclick = function(event) {
        if (!event.target.closest('.notif-wrapper')) {
            const popup = document.getElementById('notificationPopup');
            if (popup) popup.style.display = 'none';
        }
    }
</script>