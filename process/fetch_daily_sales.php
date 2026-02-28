<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db_connect.php'; 
require_once '../classes/DailySalesRepository.php';
require_once '../classes/DailySalesService.php';
// Instantiate the classes and fetch the data into a variable
$repository = new DailySalesRepository($conn);
$service = new DailySalesService($repository);
$salesData = $service->getFormattedSalesReport();
?>