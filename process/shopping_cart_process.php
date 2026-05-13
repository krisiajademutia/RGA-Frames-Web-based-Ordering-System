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
        // First fetch the current values so we can derive the unit price without a column that doesn't exist.
        $fetch = $conn->prepare(
            "SELECT quantity, sub_total FROM tbl_printing_order_items
             WHERE printing_order_item_id = ? AND order_id IS NULL"
        );
        $fetch->bind_param("i", $itemId);
        $fetch->execute();
        $printRow = $fetch->get_result()->fetch_assoc();

        if ($printRow) {
            $currentPrintQty  = (int)$printRow['quantity'];
            $newPrintQty      = max(1, $currentPrintQty + $delta);
            // Derive unit price from the stored sub_total to avoid a missing-column error.
            $unitPrice        = $currentPrintQty > 0 ? ((float)$printRow['sub_total'] / $currentPrintQty) : 0;
            $newPrintSubTotal = $unitPrice * $newPrintQty;

            $stmt = $conn->prepare(
                "UPDATE tbl_printing_order_items
                 SET quantity  = ?,
                     sub_total = ?
                 WHERE printing_order_item_id = ? AND order_id IS NULL"
            );
            $stmt->bind_param("idi", $newPrintQty, $newPrintSubTotal, $itemId);
            $stmt->execute();
        }
    } else {
        // Fetch current item details, stock, and any linked print record.
        $stmt_check = $conn->prepare("
            SELECT f.frame_category, f.r_product_id, f.quantity,
                   f.printing_order_item_id,
                   IFNULL((SELECT quantity FROM tbl_ready_made_product_stocks s WHERE s.r_product_id = f.r_product_id LIMIT 1), 9999) AS current_stock
            FROM tbl_frame_order_items f
            WHERE f.item_id = ?
        ");
        $stmt_check->bind_param("i", $itemId);
        $stmt_check->execute();
        $row = $stmt_check->get_result()->fetch_assoc();
        
        if ($row) {
            $currentQty = (int)$row['quantity'];
            $newQty = max(1, $currentQty + $delta);
            $stock = (int)$row['current_stock'];
            
            // Validate Stock for Ready Made Frames
            if ($row['frame_category'] === 'READY_MADE' && $newQty > $stock) {
                // If the user tries to increase beyond stock, just ignore the update.
                // We could set a session error message here, but silently ignoring is also an option for simple cart UIs.
            } else {
                $stmt = $conn->prepare(
                    "UPDATE tbl_frame_order_items
                     SET sub_total = (base_price + extra_price) * ?,
                         quantity  = ?
                     WHERE item_id = ?"
                );
                $stmt->bind_param("iii", $newQty, $newQty, $itemId);
                $stmt->execute();

                // If this frame item has a linked print record, keep them in sync.
                if (!empty($row['printing_order_item_id'])) {
                    $printId = (int)$row['printing_order_item_id'];
                    // Fetch current print unit price.
                    $fetchPrint = $conn->prepare(
                        "SELECT quantity, sub_total FROM tbl_printing_order_items
                         WHERE printing_order_item_id = ? AND order_id IS NULL"
                    );
                    $fetchPrint->bind_param("i", $printId);
                    $fetchPrint->execute();
                    $printRow = $fetchPrint->get_result()->fetch_assoc();

                    if ($printRow && (int)$printRow['quantity'] > 0) {
                        $printUnitPrice    = (float)$printRow['sub_total'] / (int)$printRow['quantity'];
                        $newPrintSubTotal  = $printUnitPrice * $newQty;
                        $syncPrint = $conn->prepare(
                            "UPDATE tbl_printing_order_items
                             SET quantity  = ?,
                                 sub_total = ?
                             WHERE printing_order_item_id = ? AND order_id IS NULL"
                        );
                        $syncPrint->bind_param("idi", $newQty, $newPrintSubTotal, $printId);
                        $syncPrint->execute();
                    }
                }
            }
        }
    }
    header("Location: ../customer/customer_cart.php");
    exit;
}

// ── Fetch items for page render ──────────────────────────────────────────────
$cart_items   = $cartService->getCartItems($customer_id);
$total_amount = array_sum(array_column($cart_items, 'sub_total'));