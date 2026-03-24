<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db_connect.php'; 
require_once '../classes/Dashboard/Repository/DailySalesRepository.php';
require_once '../classes/Dashboard/DailySalesService.php';

$repository = new \Classes\Dashboard\Repository\DailySalesRepository($conn);
$service = new \Classes\Dashboard\DailySalesService($repository);

$salesData = $service->getFormattedSalesReport();
$combinedBreakdown = $service->getTodaysCombinedBreakdown(); // This holds our single master table data!
?>