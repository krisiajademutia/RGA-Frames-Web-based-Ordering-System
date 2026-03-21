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

$action = $_GET['action'] ?? $_POST['action'] ?? null;

// ── Save selected items to session then redirect to checkout ─────────────────
if ($action === 'save_selected' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = $_POST['selected_items'] ?? '[]';
    $ids = json_decode($raw, true);

    if (!is_array($ids) || empty($ids)) {
        header("Location: ../customer/customer_cart.php");
        exit;
    }

    // Separate frame IDs (plain integers) from print IDs (p_ prefixed)
    $frameIds = [];
    $printIds = [];
    foreach ($ids as $id) {
        $id = (string)$id;
        if (str_starts_with($id, 'p_')) {
            $pid = intval(substr($id, 2));
            if ($pid > 0) $printIds[] = $pid;
        } else {
            $fid = intval($id);
            if ($fid > 0) $frameIds[] = $fid;
        }
    }

    if (empty($frameIds) && empty($printIds)) {
        header("Location: ../customer/customer_cart.php");
        exit;
    }

    $_SESSION['selected_cart_items']       = $frameIds;
    $_SESSION['selected_print_cart_items'] = $printIds;
    unset($_SESSION['buy_now_item']);
    header("Location: ../customer/customer_checkout.php");
    exit;
}

// ── Remove single standalone print item ─────────────────────────────────────
if ($action === 'delete_print' && isset($_GET['id'])) {
    try {
        $cartService->removePrintItem(intval($_GET['id']));
        header("Location: ../customer/customer_cart.php?status=deleted");
    } catch (\Exception $e) {
        header("Location: ../customer/customer_cart.php?status=error");
    }
    exit;
}

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
    $frameIds = [];
    $printIds = [];
    foreach ($ids_raw as $id) {
        $id = (string)$id;
        if (str_starts_with($id, 'p_')) {
            $pid = intval(substr($id, 2));
            if ($pid > 0) $printIds[] = $pid;
        } else {
            $fid = intval($id);
            if ($fid > 0) $frameIds[] = $fid;
        }
    }
    try {
        if (!empty($frameIds)) $cartService->removeSelectedItems($frameIds, $customer_id);
        if (!empty($printIds))  $cartService->removeSelectedPrintItems($printIds, $customer_id);
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
    $type   = $_GET['type'] ?? 'frame';

    if ($type === 'print') {
        $stmt = $conn->prepare(
            "UPDATE tbl_printing_order_items
             SET quantity  = GREATEST(1, quantity + ?),
                 sub_total = (sub_total / quantity) * GREATEST(1, quantity + ?)
             WHERE printing_order_item_id = ? AND order_id IS NULL"
        );
        $stmt->bind_param("iii", $delta, $delta, $itemId);
    } else {
        $stmt = $conn->prepare(
            "UPDATE tbl_frame_order_items 
             SET quantity  = GREATEST(1, quantity + ?),
                 sub_total = (base_price + extra_price) * GREATEST(1, quantity + ?)
             WHERE item_id = ?"
        );
        $stmt->bind_param("iii", $delta, $delta, $itemId);
    }
    $stmt->execute();
    header("Location: ../customer/customer_cart.php");
    exit;
}

// ── Fetch items for page render ──────────────────────────────────────────────
$cart_items   = $cartService->getCartItems($customer_id);
$total_amount = array_sum(array_column($cart_items, 'sub_total'));