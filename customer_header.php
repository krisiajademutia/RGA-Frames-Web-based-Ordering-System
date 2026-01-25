<?php
// customer_header.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';

$user_id = $_SESSION['user_id'] ?? 0;
$display_name = htmlspecialchars($_SESSION['first_name'] ?? 'Customer');

// Counter for the badge
$notif_count = 0; 
if ($user_id > 0) {
    $notif_res = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE user_id = '$user_id' AND is_read = 0");
    if ($notif_res) { 
        $row = $notif_res->fetch_assoc();
        $notif_count = $row['total'] ?? 0; 
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    /* --- SYNCED NAVBAR DIMENSIONS --- */
    .navbar {
        background-color: #B89655;
        height: 80px; /* MATCHES ADMIN HEADER HEIGHT EXACTLY */
        width: 100%;
        position: fixed;
        top: 0; left: 0;
        z-index: 1000;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 30px; /* MATCHES ADMIN PADDING */
        box-shadow: 0 4px 12px rgba(0,0,0,0.05); /* MATCHES ADMIN SHADOW */
        box-sizing: border-box;
        font-family: 'Inter', sans-serif;
    }

    .nav-logo { color: white; text-decoration: none; font-weight: 800; font-size: 20px; display: flex; align-items: center; gap: 12px; }
    .nav-links { display: flex; gap: 20px; align-items: center; } /* Adjusted gap to match admin nav style */
    
    .nav-item { 
        color: white; 
        text-decoration: none; 
        font-size: 13px; /* Matches admin font size */
        font-weight: 600; 
        padding: 8px 16px;
        background: rgba(255,255,255,0.1); /* Subtle pill look */
        border-radius: 6px;
        display: flex; 
        align-items: center; 
        gap: 8px; 
    }

    /* --- RIGHT SECTION ALIGNMENT --- */
    .nav-actions { display: flex; align-items: center; gap: 20px; }
    
    .icon-link { 
        color: rgba(255,255,255,0.9); 
        text-decoration: none; 
        font-size: 20px; 
        position: relative; 
        cursor: pointer; 
        display: flex; 
        align-items: center;
        background: none;
        border: none;
    }

    /* --- THE FIXED 420px DROPDOWN --- */
    .notif-wrapper { position: relative; }
    
    .notif-dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: 60px; /* Align with Admin Popup position */
        width: 420px; /* MATCHES ADMIN WIDTH EXACTLY */
        background: white;
        border-radius: 12px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        z-index: 2000;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .notif-dropdown.show { display: block; animation: fadeIn 0.2s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* DROPDOWN HEADER: Synced with Admin Internal Padding */
    .dropdown-header-custom {
        padding: 20px 25px; /* MATCHES ADMIN INTERNAL PADDING */
        border-bottom: 1px solid #f1f5f9; 
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        background-color: #fff;
    }
    .dropdown-header-custom span { font-weight: 700; font-size: 15px; color: #1e293b; }
    .mark-read-custom { font-size: 12px; color: #2563eb; text-decoration: none; font-weight: 500; }

    /* --- THE "KANG TRISHA" CONTAINER --- */
    .notif-content {
        max-height: 500px; /* MATCHES ADMIN BODY HEIGHT */
        overflow-y: auto;
    }

    .notif-empty {
        text-align: center;
        padding: 60px 40px; /* Balanced deep padding */
        color: #94a3b8;
    }
    .notif-empty i { font-size: 40px; display: block; margin-bottom: 15px; opacity: 0.3; }
    .notif-empty span { font-size: 13px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #64748b; }

    /* FOOTER */
    .notif-footer {
        display: block;
        text-align: center;
        padding: 16px;
        background: #f8fafc;
        color: #B89655;
        font-weight: 700;
        font-size: 12px;
        text-decoration: none;
        border-top: 1px solid #eee;
    }

    /* USER SECTION SYNC */
    .user-profile { 
        display: flex; 
        align-items: center; 
        gap: 15px; 
        padding-left: 20px; 
        border-left: 1px solid rgba(255,255,255,0.2); 
        height: 30px; 
    }
    .user-name { font-weight: 600; font-size: 14px; color: white; }
    .logout-btn-custom { 
        text-decoration: none; 
        color: white; 
        font-size: 12px; 
        font-weight: 600; 
        border: 1px solid rgba(255,255,255,0.4); 
        padding: 6px 12px; 
        border-radius: 6px; 
    }

    .badge-dot {
        position: absolute; top: -2px; right: -2px;
        background: #ef4444; color: white; font-size: 10px; font-weight: bold;
        width: 16px; height: 16px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; border: 2px solid #B89655;
    }
</style>

<nav class="navbar">
    <div class="header-left" style="display: flex; align-items: center; gap: 20px;">
        <a href="customer_dashboard.php" class="nav-logo">
            <i class="fas fa-layer-group"></i> RGA Frames
        </a>
        <div class="nav-links">
            <a href="customer_dashboard.php" class="nav-item"><i class="fas fa-home"></i> Home</a>
            <a href="customer_orders.php" class="nav-item"><i class="fas fa-box-open"></i> Orders</a>
        </div>
    </div>

    <div class="nav-actions">
        <div class="notif-wrapper">
            <button class="icon-link" onclick="toggleNotifs()">
                <i class="fas fa-bell"></i>
                <?php if($notif_count > 0): ?>
                    <span class="badge-dot"><?php echo $notif_count; ?></span>
                <?php endif; ?>
            </button>

            <div class="notif-dropdown" id="custNotifDropdown">
                <div class="dropdown-header-custom">
                    <span>Notifications</span>
                    <a href="#" class="mark-read-custom">Mark all read</a>
                </div>
                
                <div class="notif-content">
                    <?php if ($notif_count == 0): ?>
                        <div class="notif-empty">
                            <i class="fas fa-bell-slash"></i>
                            <span>KANG MAY NI</span>
                        </div>
                    <?php else: ?>
                        <?php endif; ?>
                </div>
                
                <a href="customer_notifications.php" class="notif-footer">
                    VIEW ALL NOTIFICATIONS
                </a>
            </div>
        </div>

        <a href="customer_cart.php" class="icon-link">
            <i class="fas fa-shopping-cart"></i>
        </a>

        <div class="user-profile">
            <span class="user-name">Hi, <?php echo $display_name; ?></span>
            <a href="index.php" class="logout-btn-custom">Logout</a>
        </div>
    </div>
</nav>

<script>
function toggleNotifs() {
    const dropdown = document.getElementById("custNotifDropdown");
    dropdown.classList.toggle("show");
}

window.onclick = function(event) {
    if (!event.target.closest('.notif-wrapper')) {
        const dropdown = document.getElementById("custNotifDropdown");
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
}
</script>