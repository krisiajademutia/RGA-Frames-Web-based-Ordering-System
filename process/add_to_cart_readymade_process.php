<?php
// process/add_to_cart_readymade_process.php

session_start();
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/ReadyMade/Repository/ReadyMadeRepository.php';
require_once __DIR__ . '/../classes/ReadyMade/ReadyMadeService.php';

header('Content-Type: application/json');

// ── Auth guard ───────────────────────────────────────────
if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to your cart.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// ── Input ────────────────────────────────────────────────
$customerId = (int)$_SESSION['user_id'];
$productId  = (int)($_POST['product_id'] ?? 0);
$quantity   = max(1, (int)($_POST['quantity'] ?? 1));

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit();
}

// ── Service call ─────────────────────────────────────────
$repo    = new \Classes\ReadyMade\Repository\ReadyMadeRepository($conn);
$service = new \Classes\ReadyMade\ReadyMadeService($repo);

$result  = $service->addToCart($customerId, $productId, $quantity);
echo json_encode($result);
exit();