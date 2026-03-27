<?php
require 'config/db_connect.php';
require_once 'vendor/autoload.php'; // Ensure Dompdf is installed via composer

use Dompdf\Dompdf;
use Dompdf\Options;

$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if (!$orderId) {
    die("Error: No Order ID provided.");
}

// 1. Data Fetching
// Fetch Order Details
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
    $actualAmountPaid = isset($paymentData['total_amount']) ? $paymentData['total_amount'] : 0;
}

// Fetch Items
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

// 2. Build HTML for PDF (Matching UI spacing and elements)
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: "DejaVu Sans", sans-serif; 
            padding: 40px; 
            color: #333; 
            font-size: 12px;
            line-height: 1.5;
        }
        .header { margin-bottom: 25px; }
        .logo-text { color: #8B5E3C; font-size: 24px; font-weight: bold; margin: 0 0 5px 0; }
        .divider { border-top: 1px solid #eee; margin: 15px 0; }
        
        .info-section { margin-bottom: 30px; font-size: 13px; color: #666; line-height: 1.6; }
        .order-title { color: #333; font-size: 18px; font-weight: bold; margin: 0 0 8px 0; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { 
            text-align: left; padding: 10px; border-bottom: 1px solid #eee; 
            color: #8B5E3C; font-size: 11px; text-transform: uppercase; 
            letter-spacing: 0.5px; font-weight: bold;
        }
        td { padding: 15px 10px; border-bottom: 1px solid #f8f8f8; font-size: 14px; vertical-align: top; }

        .item-description { color: #333; font-weight: 500; }
        .item-service { font-size: 11px; color: #888; margin-top: 2px; }

        /* Totals Section Styling to match UI */
        .totals-wrapper { width: 100%; margin-top: 20px; }
        .totals-table { width: 250px; margin-left: auto; border-collapse: collapse; }
        .totals-table td { border: none; padding: 5px 10px; font-size: 13px; color: #666; }
        .totals-table .amount { text-align: right; color: #333; font-weight: bold; }
        
        .balance-row td { background-color: #FDF4EB; color: #8B5E3C; font-weight: bold; padding: 10px; }

        .status-stamp { 
            border: 2px solid #2ECC71; color: #2ECC71; padding: 5px 15px; 
            font-weight: bold; display: inline-block; margin-top: 20px; 
            border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .footer { text-align: center; font-size: 11px; color: #aaa; margin-top: 60px; padding-top: 20px; border-top: 1px solid #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-text">RGA Frames</div>
        <div class="divider"></div>
    </div>

    <div class="info-section">
        <div class="order-title">Acknowledgement Receipt</div>
        <strong>Order ID:</strong> ' . htmlspecialchars($order['order_reference_no']) . '<br>
        <strong>Date:</strong> ' . date("M d, Y", strtotime($order['created_at'])) . '<br>
        <strong>Delivery:</strong> ' . htmlspecialchars($order['delivery_option']) . '<br>
        <strong>Payment Method:</strong> ' . htmlspecialchars($order['payment_method']) . '
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 60%;">Item</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($allItems as $item) {
            $html .= '
            <tr>
                <td>
                    <div class="item-description">' . htmlspecialchars($item['frame_category']) . '</div>
                    <div class="item-service">' . htmlspecialchars($item['service_type']) . '</div>
                </td>
                <td style="text-align: center;">×' . $item['quantity'] . '</td>
                <td style="text-align: right; font-weight: bold;">₱' . number_format($item['sub_total'], 2) . '</td>
            </tr>';
        }

        $html .= '
        </tbody>
    </table>

    <div class="totals-wrapper">
        <table class="totals-table">
            <tr>
                <td>Grand Total</td>
                <td class="amount">₱' . number_format($order['total_price'], 2) . '</td>
            </tr>
            <tr>
                <td style="color: #2ECC71;">Amount Paid</td>
                <td class="amount" style="color: #2ECC71;">-₱' . number_format($actualAmountPaid, 2) . '</td>
            </tr>
            <tr class="balance-row">
                <td>Balance</td>
                <td class="amount">₱' . number_format($order['total_price'] - $actualAmountPaid, 2) . '</td>
            </tr>
        </table>
    </div>

    <div class="status-stamp">✓ ' . htmlspecialchars($order['order_status']) . '</div>

    <div class="footer">
        Thank you for choosing RGA Frames! This is your official proof of transaction.<br>
        RGA Frames • Philippine-made quality framing services
    </div>
</body>
</html>';

// 3. Render PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); 
$options->set('defaultFont', 'DejaVu Sans'); // Required for Peso symbol support

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Receipt_" . $order['order_reference_no'] . ".pdf", array("Attachment" => 1));