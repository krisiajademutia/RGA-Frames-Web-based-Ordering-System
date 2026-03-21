<?php
// process/checkout_process.php
session_start();
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Checkout/CheckoutService.php';
require_once __DIR__ . '/../classes/CustomFrame/CustomFrameService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    echo json_encode(['success' => false, 'message' => 'Please log in to place an order.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$customer_id     = (int)$_SESSION['user_id'];
$checkoutService = new CheckoutService($conn);

// Detect if this is a Buy Now checkout or a Cart checkout
$isBuyNow = isset($_SESSION['buy_now_item']) && is_array($_SESSION['buy_now_item']) && !empty($_SESSION['buy_now_item']);

try {
    if ($isBuyNow) {
        // ── BUY NOW FLOW ──
        $buyNowItemData = $_SESSION['buy_now_item'];
        $itemType = $buyNowItemData['item_type'] ?? 'CUSTOM_FRAME';

        // Calculate Subtotal based on product type
        if ($itemType === 'CUSTOM_FRAME') {
            $cfService = new CustomFrameService($conn);
            $prices = $cfService->calculatePrice($buyNowItemData);
            $cartTotal = $prices['grand_total'];
        } else {
            // For Printing and Ready Made
            $cartTotal = (float)($buyNowItemData['total_price'] ?? 0);
        }

        $cartItems = []; // Fake empty cart array since this is Buy Now
        
        // Pass to the CheckoutService which perfectly handles the database insert
        $response = $checkoutService->processCheckout($customer_id, $_POST, $_FILES, $cartItems, $cartTotal, true, $buyNowItemData);
        
        if ($response['success']) {
            unset($_SESSION['buy_now_item']);
            $_SESSION['order_success_pending'] = true; // <-- ADDED THIS
        }
        echo json_encode($response);
        exit();

    } else {
        // ── NORMAL CART FLOW ──
        // This grabs Frames, Printing, AND Ready-Made items beautifully!
        $cartItems = $checkoutService->getCartItems($customer_id);
        
        if (empty($cartItems)) {
            echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
            exit();
        }

        $cartTotal = 0;
        foreach($cartItems as $item) {
            $cartTotal += (float)($item['sub_total'] ?? 0);
        }

        // Pass to the CheckoutService
        $response = $checkoutService->processCheckout($customer_id, $_POST, $_FILES, $cartItems, $cartTotal, false, null);
        
        if ($response['success']) {
            $_SESSION['order_success_pending'] = true; // <-- ADDED THIS
        }
        echo json_encode($response);
        exit();
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
}