<?php
require '../config/db_connect.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : null;
if (!$orderId) { die("Error: No Order ID provided."); }

// --- 1. DATA FETCHING ---
$stmt = $conn->prepare("SELECT * FROM tbl_orders WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
if (!$order) { die("Order not found."); }

$actualAmountPaid = 0;
$stmtPay = $conn->prepare("SELECT total_amount FROM tbl_payment WHERE order_id = ?");
if ($stmtPay) {
    $stmtPay->bind_param("i", $orderId);
    $stmtPay->execute();
    $paymentData = $stmtPay->get_result()->fetch_assoc();
    $actualAmountPaid = $paymentData['total_amount'] ?? 0;
}

// Fetch Frame Items
$stmtFrames = $conn->prepare("SELECT frame_category, service_type, quantity, sub_total FROM tbl_frame_order_items WHERE order_id = ?");
$stmtFrames->bind_param("i", $orderId);
$stmtFrames->execute();
$framesResult = $stmtFrames->get_result();

$allItems = [];
$hasCustomFrameAndPrint = false;
$subtotal = 0;

while ($row = $framesResult->fetch_assoc()) { 
    $allItems[] = $row; 
    $subtotal += $row['sub_total'];
    // Flag for Custom Print Bundles
    if ($row['frame_category'] === 'CUSTOM' && $row['service_type'] === 'FRAME&PRINT') {
        $hasCustomFrameAndPrint = true;
    }
}

// Fetch Printing Items (With Bundle Logic)
$stmtPrint = $conn->prepare("SELECT 'Printing Service' as frame_category, CONCAT(width_inch, 'x', height_inch, ' inch') as service_type, quantity, sub_total FROM tbl_printing_order_items WHERE order_id = ?");
$stmtPrint->bind_param("i", $orderId);
$stmtPrint->execute();
$printResult = $stmtPrint->get_result();

while ($row = $printResult->fetch_assoc()) { 
    // Only add if it's not part of the bundle OR it's an extra paid print
    if (!$hasCustomFrameAndPrint || $row['sub_total'] > 0) {
        $allItems[] = $row; 
        $subtotal += $row['sub_total'];
    }
}

// Final Calculations
$discount = $subtotal - $order['total_price'];
$actualBalance = $order['total_price'] - $actualAmountPaid;

// --- 2. IMAGE TO BASE64 ---
$path = '../assets/img/rga_logo.png';
$base64 = '';
if (file_exists($path)) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

$css = file_get_contents('../assets/css/style.css');

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        <?= $css ?>
        body { font-family: 'DejaVu Sans', sans-serif; color: #333; }
        .receipt-logo { display: inline-block; vertical-align: middle; width: 50px; height: 50px; }
        .logo-text { display: inline-block; vertical-align: middle; margin-left: 10px; font-size: 24px; font-weight: bold; color: #0F473A; }
        th { border-bottom: 0.5pt solid #0F473A; padding: 10px 5px; font-size: 11px; text-transform: uppercase; }
        td { padding: 10px 5px; border-bottom: 0.1pt solid #eee; }
        .balance-label { background-color: #F3EBD9; font-weight: bold; padding: 10px; color: #0F473A; }
    </style>
</head>
<body class="pdf-body">
    <div class="header" style="margin-bottom: 30px;">
        <?php if ($base64): ?>
            <img src="<?= $base64 ?>" class="receipt-logo">
        <?php endif; ?>
        <div class="logo-text">RGA Frames</div>
    </div>

    <div class="info-section">
        <div class="order-title" style="font-size: 18px; font-weight: bold; margin-bottom: 10px;">Acknowledgement Receipt</div>
        <strong>Order ID:</strong> <?= htmlspecialchars($order['order_reference_no']) ?><br>
        <strong>Date:</strong> <?= date("M d, Y", strtotime($order['created_at'])) ?><br>
        <strong>Delivery:</strong> <?= htmlspecialchars($order['delivery_option']) ?><br>
        <strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-top: 25px;">
        <thead>
            <tr>
                <th style="text-align: left; width: 60%;">Item</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allItems as $item): ?>
            <tr>
                <td>
                    <div style="font-weight: bold; font-size: 14px;"><?= htmlspecialchars($item['frame_category']) ?></div>
                    <div style="font-size: 11px; color: #777;"><?= htmlspecialchars($item['service_type']) ?></div>
                </td>
                <td style="text-align: center;">×<?= $item['quantity'] ?></td>
                <td style="text-align: right; font-weight: bold;">₱<?= number_format($item['sub_total'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="width: 100%; margin-top: 20px;">
        <table style="width: 45%; float: right; border-collapse: collapse;">
            <tr>
                <td style="border: none; padding: 4px;">Subtotal</td>
                <td style="text-align: right; border: none; padding: 4px;">₱<?= number_format($subtotal, 2) ?></td>
            </tr>
            <?php if ($discount > 0): ?>
            <tr>
                <td style="border: none; padding: 4px; color: #e74c3c;">Discount</td>
                <td style="text-align: right; border: none; padding: 4px; color: #e74c3c;">-₱<?= number_format($discount, 2) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td style="border: none; padding: 8px 4px 4px 4px; font-weight: bold; border-top: 0.5pt solid #eee;">Grand Total</td>
                <td style="text-align: right; border: none; padding: 8px 4px 4px 4px; font-weight: bold; border-top: 0.5pt solid #eee;">₱<?= number_format($order['total_price'], 2) ?></td>
            </tr>
            <tr>
                <td style="border: none; padding: 4px; color: #2ECC71;">Amount Paid</td>
                <td style="text-align: right; border: none; padding: 4px; color: #2ECC71;">-₱<?= number_format($actualAmountPaid, 2) ?></td>
            </tr>
            <tr>
                <td class="balance-label" style="border: none;">Balance</td>
                <td class="balance-label" style="text-align: right; border: none;">₱<?= number_format($actualBalance, 2) ?></td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>

    <div style="margin-top: 30px; border: 1pt solid #2ECC71; color: #2ECC71; display: inline-block; padding: 5px 15px; font-weight: bold; border-radius: 4px; text-transform: uppercase;">
        ✓ <?= htmlspecialchars($order['order_status']) ?>
    </div>

    <div style="margin-top: 60px; text-align: center; font-size: 10px; color: #aaa; border-top: 0.5pt solid #eee; padding-top: 15px;">
        Thank you for choosing RGA Frames! This is your official proof of transaction.<br>
        RGA Frames • Philippine-made quality framing services
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); 
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Receipt_" . $order['order_reference_no'] . ".pdf", array("Attachment" => 1));