<?php
require '../config/db_connect.php';

$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if (!$orderId) {
    die("Error: No Order ID provided.");
}

// 1. Fetch Order Details
$stmt = $conn->prepare("SELECT * FROM tbl_orders WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// 2. Fetch Payment Data
$stmtPay = $conn->prepare("SELECT total_amount FROM tbl_payment WHERE order_id = ?");
$stmtPay->bind_param("i", $orderId);
$stmtPay->execute();
$paymentData = $stmtPay->get_result()->fetch_assoc();
$actualAmountPaid = isset($paymentData['total_amount']) ? $paymentData['total_amount'] : 0;

// 3. Fetch Items (Frames & Printing)
$stmtFrames = $conn->prepare("SELECT frame_category, service_type, quantity, sub_total FROM tbl_frame_order_items WHERE order_id = ?");
$stmtFrames->bind_param("i", $orderId);
$stmtFrames->execute();
$framesResult = $stmtFrames->get_result();

$stmtPrint = $conn->prepare("SELECT 'Printing Service' as frame_category, CONCAT(width_inch, 'x', height_inch, ' inch') as service_type, quantity, sub_total FROM tbl_printing_order_items WHERE order_id = ?");
$stmtPrint->bind_param("i", $orderId);
$stmtPrint->execute();
$printResult = $stmtPrint->get_result();

$allItems = [];
while ($row = $framesResult->fetch_assoc()) { $allItems[] = $row; }
while ($row = $printResult->fetch_assoc()) { $allItems[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?= htmlspecialchars($order['order_reference_no']) ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css"> 
</head>
<body class="receipt-page">

    <div class="action-bar">
        <a href="../customer/customer_order_details.php?id=<?= $orderId ?>" class="cst-ord-dtls-back">
            <i class="fas fa-arrow-left"></i> Back to Order Details
        </a>

        <a href="generate_pdf_receipt.php?order_id=<?= $orderId ?>" class="btn-download">
            <i class="fas fa-file-pdf"></i> Download PDF Receipt
        </a>
    </div>

    <div class="receipt-container">
        <div class="header">
            <img src="../assets/img/rga_logo.png" alt="RGA Frames Logo" class="receipt-logo">
            <div class="logo-text">RGA Frames</div>        
        </div>


        <div class="info-section">
            <div class="order-title">Acknowledgement Receipt</div>
            <strong>Order ID:</strong> <?= htmlspecialchars($order['order_reference_no']) ?><br>
            <strong>Date:</strong> <?= date("M d, Y", strtotime($order['created_at'])) ?><br>
            <strong>Delivery:</strong> <?= htmlspecialchars($order['delivery_option']) ?><br>
            <strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?>
        </div>

        <table class="receipt-table">
            <thead>
                <tr>
                    <th style="width: 60%;">Item</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($allItems)): ?>
                    <?php foreach ($allItems as $item): ?>
                    <tr>
                        <td>
                            <div class="item-description"><?= htmlspecialchars($item['frame_category']) ?></div>
                            <div class="item-service"><?= htmlspecialchars($item['service_type']) ?></div>
                        </td>
                        <td style="text-align: center;">×<?= $item['quantity'] ?></td>
                        <td style="text-align: right; font-weight: bold;">
                            &#8369;<?= number_format($item['sub_total'], 2) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: #999; padding: 30px;">
                            No items found in this order.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Grand Total</td>
                    <td class="amount">&#8369;<?= number_format($order['total_price'], 2) ?></td>
                </tr>
                <tr>
                    <td style="color: #2ECC71;">Amount Paid</td>
                    <td class="amount" style="color: #2ECC71;">-&#8369;<?= number_format($actualAmountPaid, 2) ?></td>
                </tr>
                <tr class="balance-row">
                    <td>Balance</td>
                    <td class="amount">&#8369;<?= number_format($order['total_price'] - $actualAmountPaid, 2) ?></td>
                </tr>
            </table>
        </div>

        <div class="status-stamp">
            ✓ <?= htmlspecialchars($order['order_status']) ?>
        </div>

        <div class="footer">
            Thank you for choosing RGA Frames! This is your official proof of transaction.<br>
            RGA Frames • Philippine-made quality framing services
        </div>
    </div>

</body>
</html>