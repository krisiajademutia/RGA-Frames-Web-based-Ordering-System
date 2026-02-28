<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Call the process file to get the $salesData variable
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
        <div class="admn-sales-header">
            <h1 class="admn-sales-title">Daily Sales Report</h1>
            <p class="admn-sales-subtitle">Sales breakdown by category per day</p>
        </div>

        <div class="admn-sales-table-container">
            <table class="admn-sales-table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <th>READY-MADE</th>
                        <th>CUSTOM</th>
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
                            <td><span class="sales-pill pill-total"><?php echo $row['total_sold']; ?></span></td>
                            <td class="sales-earnings">₱<?php echo htmlspecialchars($row['earnings']); ?></td>
                            <td></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
