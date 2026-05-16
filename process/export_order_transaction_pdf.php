<?php
if (!defined('DEBUGPNG')) {
    define('DEBUGPNG', false);
}
require '../config/db_connect.php';
require_once '../classes/Dashboard/Repository/DailySalesRepository.php';
require_once '../vendor/autoload.php';

use Classes\Dashboard\Repository\DailySalesRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

$repository = new DailySalesRepository($conn);
$transactions = $repository->getTodaysCombinedBreakdown();

$path = '../assets/img/rga_logo.png';
$base64 = '';
if (file_exists($path)) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #333; }
        .receipt-logo { display: inline-block; vertical-align: middle; width: 50px; height: 50px; }
        .logo-text { display: inline-block; vertical-align: middle; margin-left: 10px; font-size: 24px; font-weight: bold; color: #0F473A; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10px; }
        th { border-bottom: 2px solid #0F473A; padding: 8px 5px; font-size: 10px; text-transform: uppercase; text-align: left; background-color: #f8f9fa; }
        td { padding: 8px 5px; border-bottom: 1px solid #eee; }
        .header { margin-bottom: 20px; }
        .report-title { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .report-date { font-size: 12px; color: #666; margin-bottom: 20px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <?php if ($base64): ?>
            <img src="<?= $base64 ?>" class="receipt-logo">
        <?php endif; ?>
        <div class="logo-text">RGA Frames</div>
    </div>

    <div class="report-title">Order Breakdown Report</div>
    <div class="report-date">Generated on: <?= date("F j, Y, g:i a") ?></div>

    <table>
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Ref #</th>
                <th>Customer Name</th>
                <th>Item Name</th>
                <th>Category</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($transactions)): ?>
                <tr><td colspan="7" class="text-center">No completed orders yet.</td></tr>
            <?php else: ?>
                <?php foreach ($transactions as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['order_date'] . ' ' . $row['order_time']) ?></td>
                    <td><?= htmlspecialchars($row['order_reference_no']) ?></td>
                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                    <td><?= htmlspecialchars(str_replace('_', ' ', $row['category'])) ?></td>
                    <td class="text-center"><?= $row['quantity'] ?></td>
                    <td class="text-right">PHP <?= number_format($row['total_price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
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
$dompdf->setPaper('A4', 'landscape'); // Use landscape for wider table
$dompdf->render();
$dompdf->stream("RGA_Order_Transactions_" . date('Y-m-d') . ".pdf", array("Attachment" => 1));
