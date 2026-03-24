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
    <title>Order Breakdown - RGA Frames</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admn-bkdn-body">

    <?php include_once '../includes/admin_header.php'; ?>

    <main class="admn-bkdn-wrapper">
        <a href="admin_daily_sales.php" class="admn-bkdn-back-btn">
            <i class="fas fa-arrow-left"></i> Back to Daily Sales
        </a>

        <h1 class="admn-bkdn-title">Order Breakdown</h1>
        <div style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
            <a href="/rga_frames/process/export_order_transaction.php" class="admn-bkdn-btn-export">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                </svg>
                Download to Excel
            </a>
        </div>

        <div class="admn-bkdn-grid">
            
            <div class="admn-bkdn-card">
                <table class="admn-bkdn-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Ref #</th>
                            <th>Customer Name</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th class="admn-bkdn-text-center">Qty</th>
                            <th class="admn-bkdn-text-right">Order Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($combinedBreakdown)): ?>
                            <tr><td colspan="7" class="admn-bkdn-empty">No completed orders yet.</td></tr>
                        <?php else: ?>
                            <?php foreach($combinedBreakdown as $row): ?>
                            <tr>
                                <td class="admn-bkdn-fw-600" style="white-space: nowrap;">
                                    <?= htmlspecialchars($row['order_date']) ?>
                                    <br>
                                    <span style="font-size: 0.8rem; color: #6b7280; font-weight: normal;">
                                        <?= isset($row['order_time']) ? htmlspecialchars($row['order_time']) : '' ?>
                                    </span>
                                </td>
                                
                                <td class="admn-bkdn-text-muted">
                                    <a href="admin_order_details.php?id=<?= $row['order_id'] ?>" 
                                       style="color: #2563eb; text-decoration: none; font-weight: 700; transition: color 0.2s;">
                                        <?= htmlspecialchars($row['order_reference_no']) ?>
                                    </a>
                                </td>
                                <td class="admn-bkdn-fw-600"><?= htmlspecialchars($row['customer_name']) ?></td>
                                <td class="admn-bkdn-fw-600"><?= htmlspecialchars($row['item_name']) ?></td>
                                <td class="admn-bkdn-text-muted"><?= htmlspecialchars(str_replace('_', ' ', $row['category'])) ?></td>
                                
                                <td class="admn-bkdn-text-center">
                                    <span class="admn-bkdn-fw-bold"><?= $row['quantity'] ?></span>
                                </td>
                                
                                <td class="admn-bkdn-text-right">
                                    <span class="admn-bkdn-text-success">₱<?= number_format($row['total_price'], 2) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
        </div>
    </main>

</body>
</html>