<?php
require 'config/db_connect.php';

$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if (!$orderId) {
    die("Error: No Order ID provided.");
}

// 1. Fetch Order Details from tbl_orders
$stmt = $conn->prepare("SELECT * FROM tbl_orders WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// 2. Fetch Frame Items
$stmtFrames = $conn->prepare("SELECT frame_category, service_type, quantity, sub_total FROM tbl_frame_order_items WHERE order_id = ?");
$stmtFrames->bind_param("i", $orderId);
$stmtFrames->execute();
$framesResult = $stmtFrames->get_result();

// 3. Fetch Printing Items (Mapping your specific columns)
$stmtPrint = $conn->prepare("SELECT 'Printing Service' as frame_category, CONCAT(width_inch, 'x', height_inch, ' inch') as service_type, quantity, sub_total FROM tbl_printing_order_items WHERE order_id = ?");
$stmtPrint->bind_param("i", $orderId);
$stmtPrint->execute();
$printResult = $stmtPrint->get_result();

// 4. Combine all items into one array for the table loop
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            background-color: #fff; 
            margin: 0; 
            padding: 40px; 
            color: #333;
            -webkit-font-smoothing: antialiased;
        }

        .action-bar { 
            max-width: 800px; 
            margin: 0 auto 30px auto; 
            text-align: right; 
        }
        .btn-download { 
            background: #8B5E3C; 
            color: white; 
            padding: 10px 20px; 
            text-decoration: none; 
            border-radius: 4px; 
            font-weight: 600; 
            display: inline-block; 
            font-size: 14px;
        }

        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 25px;
        }
        .logo-text {
            color: #8B5E3C;
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        
        .divider {
            border: 0;
            border-top: 1px solid #eee;
            margin: 15px 0;
        }

        .info-section {
            margin-bottom: 30px;
            font-size: 13px;
            color: #666;
            line-height: 1.6;
        }
        .order-title {
            color: #333;
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 8px 0;
        }
        .info-section strong {
            color: #444;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #eee;
            color: #8B5E3C;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
        }
        td {
            padding: 15px 10px;
            border-bottom: 1px solid #f8f8f8;
            font-size: 14px;
            vertical-align: top;
        }

        .item-description {
            color: #333;
            font-weight: 500;
        }
        .item-service {
            font-size: 11px;
            color: #888;
            margin-top: 2px;
        }

        .totals-section {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .totals-table {
            width: 250px;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .totals-table td {
            border: none;
            padding: 5px 10px;
            font-size: 13px;
            color: #666;
        }
        .totals-table .amount {
            text-align: right;
            color: #333;
            font-weight: bold;
        }
        
        .balance-row td {
            background-color: #FDF4EB; 
            color: #8B5E3C;
            font-weight: bold;
            padding: 10px;
        }

        .status-stamp {
            border: 2px solid #2ECC71;
            color: #2ECC71;
            padding: 5px 15px;
            font-weight: bold;
            display: inline-block;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #aaa;
            margin-top: 60px;
            padding-top: 20px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <a href="generate_pdf_receipt.php?order_id=<?= $orderId ?>" class="btn-download">
            <i class="fas fa-file-pdf"></i> Download PDF Receipt
        </a>
    </div>

    <div class="receipt-container">
        
        <div class="header">
            <div class="logo-text">RGA Frames</div>
            <hr class="divider">
        </div>

        <div class="info-section">
            <div class="order-title">Order Receipt</div>
            <strong>Order ID:</strong> <?= htmlspecialchars($order['order_reference_no']) ?><br>
            <strong>Date:</strong> <?= date("M d, Y", strtotime($order['created_at'])) ?><br>
            <strong>Delivery:</strong> <?= htmlspecialchars($order['delivery_option']) ?><br>
            <strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?>
        </div>

        <table>
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
                            ₱<?= number_format($item['sub_total'], 2) ?>
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
                    <td class="amount">₱<?= number_format($order['total_price'], 2) ?></td>
                </tr>
                <tr>
                    <td style="color: #2ECC71;">Downpayment Paid</td>
                    <td class="amount" style="color: #2ECC71;">-₱<?= number_format($order['discount_amount'], 2) ?></td>
                </tr>
                <tr class="balance-row">
                    <td>Balance</td>
                    <td class="amount">₱<?= number_format($order['total_price'] - $order['discount_amount'], 2) ?></td>
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