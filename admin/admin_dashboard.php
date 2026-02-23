<?php
session_start();
include __DIR__ . '/../config/db_connect.php';


// Security Check: Only admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

// Fetch admin name (optional, for welcome message)
$admin_name = htmlspecialchars($_SESSION['first_name'] ?? 'Admin');

// Dummy data (replace with real DB queries later)
$total_posted     = 0;
$active_products  = 0;
$total_sold       = 0;
$total_earnings   = 0.00;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RGA Frames</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Georgia:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --palette-green: #A7C957;
            --palette-gold:  #B89655;
            --palette-brown: #795338;
            --bg-light:      #fdfcf9;
            --text-main:     #2d241e;
        }

        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            font-size: 1.1rem;
            line-height: 1.6;
            padding-top: 80px;           /* Clears fixed navbar on desktop */
        }

        @media (max-width: 991px) {
            body {
                padding-top: 140px;      /* Clears taller collapsed navbar on mobile */
            }
        }

        @media (max-width: 576px) {
            body {
                padding-top: 130px;
            }
        }

        .container {
            max-width: 1300px;
        }

        .welcome-header {
            margin-bottom: 2.5rem;
            border-left: 5px solid var(--palette-gold);
            padding-left: 1.2rem;
        }

        .section-title {
            font-family: 'Georgia', serif;
            font-size: 2.1rem;
            font-weight: 700;
            color: var(--palette-brown);
            margin-bottom: 0.5rem;
        }

        .section-subtitle {
            color: #8a7e72;
            font-size: 1rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            padding: 1.8rem;
            border-radius: 14px;
            border: 1px solid #e9e3d8;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 28px rgba(121, 83, 56, 0.12);
        }

        .icon-box {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .icon-inventory { background: rgba(167, 201, 87, 0.15); color: var(--palette-green); }
        .icon-active    { background: rgba(184, 150, 85, 0.15); color: var(--palette-gold); }
        .icon-sold      { background: rgba(121, 83, 56, 0.15); color: var(--palette-brown); }
        .icon-revenue   { background: var(--palette-brown); color: white; }

        .stat-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #8a7e72;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .stat-number {
            font-size: 2.3rem;
            font-weight: 800;
            color: var(--palette-brown);
            margin: 0.4rem 0;
        }

        /* Product Section */
        .product-section {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            border: 1px solid #e9e3d8;
            box-shadow: 0 4px 18px rgba(0,0,0,0.04);
        }

        .btn-add-product {
            background-color: var(--palette-green);
            color: white;
            padding: 0.7rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-add-product:hover {
            background-color: #8fae4a;
            transform: translateY(-1px);
        }

        .filter-bar {
            background: #f8f5f0;
            padding: 0.5rem;
            border-radius: 10px;
            display: inline-flex;
            gap: 0.5rem;
        }

        .filter-tab {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .tab-active {
            background: var(--palette-brown);
            color: white;
        }

        .tab-inactive {
            color: var(--palette-brown);
            opacity: 0.7;
        }

        .search-box input {
            padding: 0.7rem 1.2rem;
            border: 1px solid #e9e3d8;
            border-radius: 10px;
            width: 280px;
            background: #fdfcf9;
        }

        .empty-placeholder {
            text-align: center;
            padding: 5rem 1rem;
            border: 2px dashed #e9e3d8;
            border-radius: 16px;
            background: #faf9f6;
        }

        .empty-placeholder i {
            font-size: 3.5rem;
            color: var(--palette-gold);
            opacity: 0.5;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../includes/admin_header.php'; ?>

    <div class="container mt-4">
        <!-- Success / Error Messages -->
        <?php
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    ' . htmlspecialchars($_SESSION['success']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ' . htmlspecialchars($_SESSION['error']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            unset($_SESSION['error']);
        }
        ?>

        <div class="welcome-header">
            <h2 class="section-title">Welcome, <?php echo $admin_name; ?></h2>
            <p class="section-subtitle">Manage RGA Frames inventory, orders & performance</p>
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
                <div class="stat-number">₱<?php echo number_format($total_earnings, 2); ?></div>
            </div>
        </div>

        <div class="product-section">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <h2 class="section-title" style="font-size: 1.8rem; margin: 0;">Product Management</h2>
                <a href="admin_post_frames.php" class="btn-add-product">
                    <i class="fas fa-plus me-1"></i> Add New Product
                </a>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div class="filter-bar">
                    <div class="filter-tab tab-active">All Frames</div>
                    <div class="filter-tab tab-inactive">In Stock</div>
                    <div class="filter-tab tab-inactive">Out of Stock</div>
                </div>

                <div class="search-box">
                    <input type="text" placeholder="Search by frame name or ID..." class="form-control">
                </div>
            </div>

            <div class="empty-placeholder">
                <i class="fas fa-folder-open"></i>
                <p style="font-weight: 600; color: var(--palette-brown); margin: 1rem 0 0.5rem;">
                    Your gallery is currently empty.
                </p>
                <p style="color: #8a7e72;">
                    Start by adding your first frame to the inventory.
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>