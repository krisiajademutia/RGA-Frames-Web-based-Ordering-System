<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Dummy Data
$total_posted = 0; $active_products = 0; $total_sold = 0; $total_earnings = 0.00;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - RGA Frames</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            /* YOUR UPLOADED PALETTE */
            --palette-green: #A7C957;
            --palette-gold: #B89655;
            --palette-brown: #795338;
            
            --bg-light: #fdfcf9; /* Subtle cream background to match brown/gold */
            --text-main: #2d241e; /* Deep brown-black for readability */
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0; padding: 0;
            background-color: var(--bg-light);
            color: var(--text-main);
            padding-top: 100px;
        }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px 50px; }
        
        .welcome-header { margin-bottom: 40px; border-left: 5px solid var(--palette-gold); padding-left: 20px; }
        .section-title { font-size: 28px; font-weight: 800; color: var(--palette-brown); margin: 0; }
        .section-subtitle { font-size: 15px; color: #8a7e72; margin-top: 5px; }

        /* --- STATS GRID --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            border: 1px solid #e9e3d8;
            transition: all 0.3s ease;
            position: relative;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(121, 83, 56, 0.1);
            border-color: var(--palette-gold);
        }

        .icon-box {
            width: 55px; height: 55px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 14px; font-size: 24px; margin-bottom: 20px;
        }

        /* Applying Palette to Icons */
        .icon-inventory { background: rgba(167, 201, 87, 0.15); color: var(--palette-green); }
        .icon-active { background: rgba(184, 150, 85, 0.15); color: var(--palette-gold); }
        .icon-sold { background: rgba(121, 83, 56, 0.15); color: var(--palette-brown); }
        .icon-revenue { background: #795338; color: white; } /* High contrast for revenue */

        .stat-label { font-size: 12px; font-weight: 700; color: #8a7e72; text-transform: uppercase; letter-spacing: 1.2px; }
        .stat-number { font-size: 34px; font-weight: 800; color: var(--palette-brown); margin: 10px 0; }
        
        /* --- PRODUCT SECTION --- */
        .product-section {
            background: white;
            padding: 35px;
            border-radius: 20px;
            border: 1px solid #e9e3d8;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        .btn-add-product {
            background-color: var(--palette-green);
            color: white; padding: 12px 24px; border-radius: 10px;
            text-decoration: none; font-weight: 700; font-size: 14px;
            display: flex; align-items: center; gap: 8px;
            transition: background 0.2s;
        }
        .btn-add-product:hover { background-color: #8fae4a; }

        .filter-bar {
            display: flex; align-items: center; gap: 10px;
            background: #f4f1ea; padding: 6px; border-radius: 12px;
            width: fit-content; margin-top: 20px;
        }

        .filter-tab {
            padding: 10px 22px; border-radius: 8px; font-size: 13px;
            cursor: pointer; font-weight: 700; transition: 0.2s;
        }
        .tab-active { background: var(--palette-brown); color: white; }
        .tab-inactive { color: var(--palette-brown); opacity: 0.6; }

        .search-box input {
            padding: 12px 16px; border: 1px solid #e9e3d8;
            border-radius: 10px; width: 300px; outline: none;
            background: #fdfcf9;
        }
        
        .empty-placeholder {
            text-align: center; padding: 80px 0;
            border: 2px dashed #e9e3d8; border-radius: 20px;
            margin-top: 30px; background: #faf9f6;
        }
        .empty-placeholder i { font-size: 50px; color: var(--palette-gold); opacity: 0.4; margin-bottom: 20px; }
    </style>
</head>
<body>

    <?php include 'admin_header.php'; ?>

    <div class="container">
        
        <div class="welcome-header">
            <h2 class="section-title">Business Overview</h2>
            <p class="section-subtitle">Managing RGA Frames Inventory & Performance</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon-box icon-inventory"><i class="fas fa-boxes"></i></div>
                <div class="stat-label">Total Inventory</div>
                <div class="stat-number"><?php echo $total_posted; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon-box icon-active"><i class="fas fa-eye"></i></div>
                <div class="stat-label">Live Frames</div>
                <div class="stat-number"><?php echo $active_products; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon-box icon-sold"><i class="fas fa-hand-holding-heart"></i></div>
                <div class="stat-label">Total Sales</div>
                <div class="stat-number"><?php echo $total_sold; ?></div>
            </div>

            <div class="stat-card" style="background: linear-gradient(145deg, #ffffff, #fdfcf9); border-bottom: 4px solid var(--palette-gold);">
                <div class="icon-box icon-revenue"><i class="fas fa-peso-sign"></i></div>
                <div class="stat-label">Total Earnings</div>
                <div class="stat-number" style="color: var(--palette-brown);">₱<?php echo number_format($total_earnings, 2); ?></div>
            </div>
        </div>

        <div class="product-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h2 class="section-title" style="font-size: 22px;">Product Management</h2>
                <a href="admin_post_frames.php" class="btn-add-product">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div class="filter-bar">
                    <div class="filter-tab tab-active">All Frames</div>
                    <div class="filter-tab tab-inactive">In Stock</div>
                    <div class="filter-tab tab-inactive">Out of Stock</div>
                </div>

                <div class="search-box">
                    <input type="text" placeholder="Search by frame name or ID...">
                </div>
            </div>
            
            <div class="empty-placeholder">
                <i class="fas fa-folder-open"></i>
                <p style="font-weight: 600; color: var(--palette-brown);">Your gallery is currently empty.</p>
                <p style="font-size: 14px; color: rgb(138, 126, 114);">Start by adding your first frame to the inventory.</p>
            </div>
        </div>
    </div>

</body>
</html>