<?php
// 1. Load your exact database connection file
require_once '../config/db_connect.php'; 
require_once '../classes/Dashboard/Repository/DailySalesRepository.php';

use Classes\Dashboard\Repository\DailySalesRepository;

// 2. Initialize the Repository using your existing $conn variable!
// (No more red lines, because $conn is brought in directly from db_connect.php)
$repository = new DailySalesRepository($conn);

// Fetch all the completed transactions from your master ledger
$transactions = $repository->getTodaysCombinedBreakdown();

// 3. Tell the browser to download this as a CSV (Excel) file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="RGA_Order_Transactions_' . date('Y-m-d') . '.csv"');

// 4. Create the file pointer to send data directly to the download
$output = fopen('php://output', 'w');

// 5. Output the exact Column Headings for the Excel sheet
fputcsv($output, array('Date', 'Time', 'Reference No.', 'Customer Name', 'Item Name', 'Category', 'Qty', 'Total Price (PHP)'));

// 6. Loop through the transactions and push them into the Excel rows
if (!empty($transactions)) {
    foreach ($transactions as $row) {
        $lineData = array(
            $row['order_date'],
            $row['order_time'],
            $row['order_reference_no'],
            $row['customer_name'],
            $row['item_name'],
            $row['category'],
            $row['quantity'],
            $row['total_price']
        );
        fputcsv($output, $lineData);
    }
}

// Close the file output and exit cleanly
fclose($output);
exit();
?>