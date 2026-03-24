<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../process/fetch_daily_sales.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Report - RGA Frames</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <?php include_once '../includes/admin_header.php'; ?>

    <main class="admn-sales-wrapper">
        <div class="admn-sales-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 class="admn-sales-title" style="margin-bottom: 5px;">Daily Sales Report</h1>
                <p class="admn-sales-subtitle" style="margin: 0;">Sales breakdown by category per day</p>
            </div>
            <div>
                <a href="admin_order_breakdown.php" style="display: inline-block; background: #2b2b2b; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <i class="fas fa-list-ul" style="margin-right: 8px;"></i> View Breakdown
                </a>
            </div>
        </div>

        <!-- ── DESKTOP: original table (untouched) ── -->
        <div class="admn-sales-table-container">
            <table class="admn-sales-table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <th>READY-MADE</th>
                        <th>CUSTOM</th>
                        <th>PRINTING</th>
                        <th>TOTAL SOLD</th>
                        <th>EARNINGS</th>
                        <th><i class="fas fa-sliders-h"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($salesData)): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 20px;">No sales data available.</td></tr>
                    <?php else: ?>
                        <?php foreach ($salesData as $row): ?>
                        <tr>
                            <td class="sales-date"><?php echo htmlspecialchars($row['date']); ?></td>
                            <td><span class="sales-pill pill-rm"><?php echo $row['ready_made']; ?></span></td>
                            <td><span class="sales-pill pill-custom"><?php echo $row['custom']; ?></span></td>
                            <td><span class="sales-pill pill-printing"><?php echo $row['printing']; ?></span></td>
                            <td><span class="sales-pill pill-total"><?php echo $row['total_sold']; ?></span></td>
                            <td class="sales-earnings">₱<?php echo htmlspecialchars($row['earnings']); ?></td>
                            <td></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ── MOBILE: card layout (hidden on desktop via CSS) ── -->
        <div class="ds-mobile-cards">
            <?php if (empty($salesData)): ?>
                <div style="text-align:center; padding:3rem 1rem; color:#9ca3af;">
                    <i class="fas fa-chart-bar" style="font-size:2rem; display:block; margin-bottom:0.75rem; color:#d1d5db;"></i>
                    <p style="margin:0; font-size:0.9rem;">No sales data available.</p>
                </div>
            <?php else: ?>
                <?php foreach ($salesData as $row): ?>
                <div class="ds-day-card">
                    <div class="ds-day-card-date">
                        <i class="fas fa-calendar-day"></i>
                        <?= htmlspecialchars($row['date']) ?>
                    </div>
                    <div class="ds-day-card-grid">
                        <div class="ds-day-cell">
                            <span class="ds-day-cell-label">Ready-Made</span>
                            <span class="sales-pill pill-rm"><?= $row['ready_made'] ?></span>
                        </div>
                        <div class="ds-day-cell">
                            <span class="ds-day-cell-label">Custom</span>
                            <span class="sales-pill pill-custom"><?= $row['custom'] ?></span>
                        </div>
                        <div class="ds-day-cell">
                            <span class="ds-day-cell-label">Total Sold</span>
                            <span class="sales-pill pill-total"><?= $row['total_sold'] ?></span>
                        </div>
                        <div class="ds-day-cell">
                            <span class="ds-day-cell-label">Earnings</span>
                            <span class="ds-day-cell-earnings">₱<?= htmlspecialchars($row['earnings']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        

    </main>

</body>
</html>