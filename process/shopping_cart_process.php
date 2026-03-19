<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Cart/Repository/CartRepositoryInterface.php';
require_once __DIR__ . '/../classes/Cart/Repository/CartRepository.php';
require_once __DIR__ . '/../classes/Cart/CartService.php';

use Classes\Cart\Repository\CartRepository;
use Classes\Cart\CartService;

$customer_id  = $_SESSION['user_id'] ?? null;
$cart_items   = [];
$total_amount = 0;

if (!$customer_id) {
    header("Location: ../customer/login.php");
    exit;
}

$repository  = new CartRepository($conn);
$cartService = new CartService($repository);

$action = $_GET['action'] ?? null;

// ── Remove single item ───────────────────────────────────────────────────────
if ($action === 'delete' && isset($_GET['id'])) {
    try {
        $cartService->removeItem(intval($_GET['id']));
        header("Location: ../customer/customer_cart.php?status=deleted");
    } catch (\Exception $e) {
        header("Location: ../customer/customer_cart.php?status=error");
    }
    exit;
}

// ── Remove selected items ────────────────────────────────────────────────────
if ($action === 'delete_selected' && isset($_GET['ids'])) {
    $ids_raw = json_decode($_GET['ids'], true);
    if (!is_array($ids_raw) || empty($ids_raw)) {
        header("Location: ../customer/customer_cart.php");
        exit;
    }
    try {
        $cartService->removeSelectedItems($ids_raw, $customer_id);
        header("Location: ../customer/customer_cart.php?status=deleted_selected");
    } catch (\Exception $e) {
        header("Location: ../customer/customer_cart.php?status=error");
    }
    exit;
}

// ── Clear all items ──────────────────────────────────────────────────────────
if ($action === 'delete_all') {
    try {
        $cartService->removeAllItems($customer_id);
        header("Location: ../customer/customer_cart.php?status=cleared");
    } catch (\Exception $e) {
        header("Location: ../customer/customer_cart.php?status=error");
    }
    exit;
}

// ── Update quantity (redirect-based) ────────────────────────────────────────
if ($action === 'update_qty' && isset($_GET['id'], $_GET['delta'])) {
    $itemId = intval($_GET['id']);
    $delta  = intval($_GET['delta']);
    $stmt   = $conn->prepare(
        "UPDATE tbl_frame_order_items 
         SET quantity  = GREATEST(1, quantity + ?),
             sub_total = (base_price + extra_price) * GREATEST(1, quantity + ?)
         WHERE item_id = ?"
    );
    $stmt->bind_param("iii", $delta, $delta, $itemId);
    $stmt->execute();
    header("Location: ../customer/customer_cart.php");
    exit;
}

// ── Fetch items for page render ──────────────────────────────────────────────
$cart_items   = $cartService->getCartItems($customer_id);
$total_amount = array_sum(array_column($cart_items, 'sub_total'));