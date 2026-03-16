<?php
// process/buy_now_readymade_process.php

session_start();
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/ReadyMade/Repository/ReadyMadeRepository.php';
require_once __DIR__ . '/../classes/ReadyMade/ReadyMadeService.php';

header('Content-Type: application/json');

// ── Auth guard ───────────────────────────────────────────
if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    echo json_encode(['success' => false, 'message' => 'Please log in to continue.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// ── Input ────────────────────────────────────────────────
$productId = (int)($_POST['product_id'] ?? 0);
$quantity  = max(1, (int)($_POST['quantity'] ?? 1));

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit();
}

// ── Service call ─────────────────────────────────────────
$repo    = new \Classes\ReadyMade\Repository\ReadyMadeRepository($conn);
$service = new \Classes\ReadyMade\ReadyMadeService($repo);

$result  = $service->buildBuyNowPayload($productId, $quantity);

if (!$result['success']) {
    echo json_encode($result);
    exit();
}

// Store in session for the checkout page (same pattern as custom frame / printing)
$_SESSION['buy_now_item'] = $result['payload'];

echo json_encode(['success' => true, 'redirect' => '../customer/customer_checkout.php']);
exit();