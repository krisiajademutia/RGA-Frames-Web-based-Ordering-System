<?php
require 'config/db_connect.php';
require_once 'vendor/autoload.php'; // Ensure Dompdf is installed via composer

use Dompdf\Dompdf;
use Dompdf\Options;

$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if (!$orderId) {
    die("Error: No Order ID provided.");
}

// 1. Data Fetching (Identical to your view receipt)
$stmt = $conn->prepare("SELECT * FROM tbl_orders WHERE order_id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) { die("Order not found."); }

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

// 2. Build HTML for PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
        
        font-family: "DejaVu Sans", sans-serif; 
        padding: 20px; 
        color: #333; 
        font-size: 12px;
    }
    
    /* Ensure the Peso symbol and checkmark are rendered correctly */
    .currency, .checkmark {
        font-family: "DejaVu Sans", sans-serif;
    }
        .header { margin-bottom: 20px; }
        .logo-text { color: #8B5E3C; font-size: 24px; font-weight: bold; margin: 0; }
        .divider { border-top: 1px solid #eee; margin: 15px 0; }
        
        .info-section { margin-bottom: 30px; font-size: 13px; color: #666; line-height: 1.6; }
        .order-title { color: #333; font-size: 18px; font-weight: bold; margin-bottom: 8px; }

        table { width: 100%; border-collapse: collapse; }
        th { 
            text-align: left; padding: 10px; border-bottom: 1px solid #eee; 
            color: #8B5E3C; font-size: 11px; text-transform: uppercase; 
        }
        td { padding: 12px 10px; border-bottom: 1px solid #f8f8f8; font-size: 14px; vertical-align: top; }

        .item-description { color: #333; font-weight: bold; }
        .item-service { font-size: 11px; color: #888; margin-top: 2px; }

        /* Totals Alignment Fix for PDF */
        .total-row td { border: none; padding: 5px 10px; }
        .total-label { text-align: left; color: #666; }
        .total-amount { text-align: right; font-weight: bold; color: #333; }
        
        .balance-row td { background-color: #FDF4EB; padding: 10px; }
        .balance-label { color: #8B5E3C; font-weight: bold; }
        
        .status-stamp { 
            border: 2px solid #2ECC71; color: #2ECC71; padding: 5px 15px; 
            font-weight: bold; display: inline-block; margin-top: 20px; 
            border-radius: 4px; text-transform: uppercase; 
        }
        .footer { text-align: center; font-size: 11px; color: #aaa; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-text">RGA Frames</div>
        <div class="divider"></div>
    </div>

    <div class="info-section">
        <div class="order-title">Order Receipt</div>
        <strong>Order ID:</strong> ' . htmlspecialchars($order['order_reference_no']) . '<br>
        <strong>Date:</strong> ' . date("M d, Y", strtotime($order['created_at'])) . '<br>
        <strong>Delivery:</strong> ' . htmlspecialchars($order['delivery_option']) . '<br>
        <strong>Payment Method:</strong> ' . htmlspecialchars($order['payment_method']) . '
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 60%;">Item</th>
                <th style="text-align: center; width: 15%;">Qty</th>
                <th style="text-align: right; width: 25%;">Amount</th>
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
            <tr class="total-row">
                <td class="total-label" colspan="2" style="text-align:right; padding-right: 50px;">Grand Total</td>
                <td class="total-amount">₱' . number_format($order['total_price'], 2) . '</td>
            </tr>
            <tr class="total-row">
                <td class="total-label" colspan="2" style="text-align:right; padding-right: 50px; color: #2ECC71;">Downpayment Paid</td>
                <td class="total-amount" style="color: #2ECC71;">-₱' . number_format($order['discount_amount'], 2) . '</td>
            </tr>
            <tr class="total-row balance-row">
                <td class="total-label balance-label" colspan="2" style="text-align:right; padding-right: 50px;">Balance</td>
                <td class="total-amount">₱' . number_format($order['total_price'] - $order['discount_amount'], 2) . '</td>
            </tr>
        </tbody>
    </table>

    <div class="status-stamp">✓ ' . htmlspecialchars($order['order_status']) . '</div>

    <div class="footer">
        Thank you for choosing RGA Frames!<br>
        RGA Frames • Philippine-made quality framing services
    </div>
</body>
</html>';

// 3. Render PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); 

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Receipt_" . $order['order_reference_no'] . ".pdf", array("Attachment" => 1));