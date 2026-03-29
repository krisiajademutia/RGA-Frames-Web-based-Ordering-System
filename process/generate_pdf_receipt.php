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

// --- 2. IMAGE TO BASE64 (CRITICAL FOR PDF LOGO) ---
$path = '../assets/img/rga_logo.png';
$base64 = '';
if (file_exists($path)) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

// --- 3. LOAD EXTERNAL CSS ---
$css = file_get_contents('../assets/css/style.css');

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        <?= $css ?>
        /* Core PDF Requirements */
        body { font-family: 'DejaVu Sans', sans-serif; }
        .receipt-logo { 
            display: inline-block; 
            vertical-align: middle; 
            width: 50px; 
            height: 50px; 
        }
        .logo-text { 
            display: inline-block; 
            vertical-align: middle; 
            margin-left: 10px;
        }
    </style>
</head>
<body class="pdf-body">
    <div class="header">
        <?php if ($base64): ?>
            <img src="<?= $base64 ?>" class="receipt-logo">
        <?php endif; ?>
        <div class="logo-text">RGA Frames</div>
    </div>

    <div class="info-section">
        <div class="order-title" style="font-size: 18px; font-weight: bold;">Acknowledgement Receipt</div>
        <strong>Order ID:</strong> <?= htmlspecialchars($order['order_reference_no']) ?><br>
        <strong>Date:</strong> <?= date("M d, Y", strtotime($order['created_at'])) ?><br>
        <strong>Delivery:</strong> <?= htmlspecialchars($order['delivery_option']) ?><br>
        <strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr>
                <th style="text-align: left; border-bottom: 2px solid #333;">Item</th>
                <th style="text-align: center; border-bottom: 2px solid #333;">Qty</th>
                <th style="text-align: right; border-bottom: 2px solid #333;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allItems as $item): ?>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee;">
                    <div style="font-weight: bold;"><?= htmlspecialchars($item['frame_category']) ?></div>
                    <div style="font-size: 11px; color: #666;"><?= htmlspecialchars($item['service_type']) ?></div>
                </td>
                <td style="text-align: center; border-bottom: 1px solid #eee;">×<?= $item['quantity'] ?></td>
                <td style="text-align: right; font-weight: bold; border-bottom: 1px solid #eee;">₱<?= number_format($item['sub_total'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="width: 100%; margin-top: 20px;">
        <table style="width: 40%; float: right; border-collapse: collapse;">
            <tr>
                <td style="padding: 5px;">Grand Total</td>
                <td style="text-align: right; padding: 5px;">₱<?= number_format($order['total_price'], 2) ?></td>
            </tr>
            <tr>
                <td style="padding: 5px; color: #2ECC71;">Amount Paid</td>
                <td style="text-align: right; padding: 5px; color: #2ECC71;">-₱<?= number_format($actualAmountPaid, 2) ?></td>
            </tr>
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <td style="padding: 10px;">Balance</td>
                <td style="text-align: right; padding: 10px;">₱<?= number_format($order['total_price'] - $actualAmountPaid, 2) ?></td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>

    <div style="margin-top: 40px; border: 2px solid #2ECC71; color: #2ECC71; display: inline-block; padding: 5px 15px; font-weight: bold;">
        ✓ <?= htmlspecialchars($order['order_status']) ?>
    </div>

    <div style="margin-top: 80px; text-align: center; font-size: 10px; color: #aaa; border-top: 1px solid #eee; padding-top: 10px;">
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