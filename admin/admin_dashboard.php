<?php
session_start();

// 1. Fetch the real-time database numbers
require_once '../process/fetch_dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RGA Frames</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <?php 
    // 2. Inject your reusable header/navbar here
    include_once '../includes/admin_header.php'; 
    ?>

    <main class="admn-dshbrd-wrapper">
        
        <div class="admn-dshbrd-top-section">
            <div class="admn-dshbrd-welcome-box">
                <h1 class="admn-dshbrd-title">Welcome, Admin!</h1>
                <p class="admn-dshbrd-subtitle">Monitor frame sales, and track total earnings from one centralized dashboard.</p>
            </div>
            
            <div class="admn-dshbrd-earnings-side">
                <div class="admn-dshbrd-btn-wrapper">
                    <a href="admin_daily_sales.php" class="admn-dshbrd-btn-daily-sales" style="text-decoration: none;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg> Daily Sales
                    </a>
                </div>
                
                <div class="admn-dshbrd-stat-card admn-dshbrd-total-earnings-card">
                    <div class="admn-dshbrd-stat-content">
                        <span class="admn-dshbrd-stat-label">TOTAL EARNINGS</span>
                        <h2 class="admn-dshbrd-stat-value">₱ <?php echo $formattedEarnings; ?></h2>
                    </div>
                    <div class="admn-dshbrd-stat-icon admn-dshbrd-icon-earnings">
                        <i class="fas fa-arrow-trend-up"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="admn-dshbrd-bottom-section">
            
            <div class="admn-dshbrd-stat-card">
                <div class="admn-dshbrd-stat-content">
                    <span class="admn-dshbrd-stat-label">SOLD READY-MADE<br>FRAMES</span>
                    <h2 class="admn-dshbrd-stat-value"><?php echo $soldReadyMade; ?></h2>
                </div>
                <div class="admn-dshbrd-stat-icon admn-dshbrd-icon-rm">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
            
            <div class="admn-dshbrd-stat-card">
                <div class="admn-dshbrd-stat-content">
                    <span class="admn-dshbrd-stat-label">SOLD CUSTOM<br>FRAMES</span>
                    <h2 class="admn-dshbrd-stat-value"><?php echo $soldCustom; ?></h2>
                </div>
                <div class="admn-dshbrd-stat-icon admn-dshbrd-icon-custom">
                    <i class="fas fa-paint-roller"></i> 
                </div>
            </div>
            
            <div class="admn-dshbrd-stat-card">
                <div class="admn-dshbrd-stat-content">
                    <span class="admn-dshbrd-stat-label">POSTED READY-MADE<br>FRAMES</span>
                    <h2 class="admn-dshbrd-stat-value"><?php echo $postedReadyMade; ?></h2>
                </div>
                <div class="admn-dshbrd-stat-icon admn-dshbrd-icon-posted">
                    <i class="fas fa-cube"></i>
                </div>
            </div>

        </div>
    </main>
    <?php 
    // If you have a footer, include it here. Otherwise, the closing tags below handle it.
    // include_once '../includes/admin_footer.php'; 
    ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>