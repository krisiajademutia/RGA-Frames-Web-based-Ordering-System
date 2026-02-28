<?php
// process/fetch_dashboard.php

include_once __DIR__ . '/../config/db_connect.php';
require_once '../classes/DashboardStats.php';

$databaseConnection = $conn;

$dashboardStats = new DashboardStats($databaseConnection);

// 3. Fetch all metrics
$totalEarnings = $dashboardStats->getTotalEarnings();
$soldReadyMade = $dashboardStats->getSoldReadyMadeFrames();
$soldCustom = $dashboardStats->getSoldCustomFrames();
$postedReadyMade = $dashboardStats->getPostedReadyMadeFrames();

// 4. Format numbers for UI (e.g., adding commas to currency)
$formattedEarnings = number_format($totalEarnings, 2);
?>