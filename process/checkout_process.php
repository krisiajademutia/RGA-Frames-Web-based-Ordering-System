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
$isBuyNow        = !empty($_POST['is_buy_now']) && isset($_SESSION['buy_now_item']);
$buyNowItemData  = $isBuyNow ? $_SESSION['buy_now_item'] : null;

$cartItems = [];
$cartTotal = 0.00;

if ($isBuyNow) {
    // ── OMNI-CHANNEL BUY NOW FLOW ──
    $itemType = $buyNowItemData['item_type'] ?? 'CUSTOM_FRAME';

    // Calculate subtotal based on item type (matching customer_checkout.php)
    if ($itemType === 'CUSTOM_FRAME') {
        $cfService = new CustomFrameService($conn);
        $prices    = $cfService->calculatePrice($buyNowItemData);
        $subTotal  = $prices['grand_total'];
    } else {
        $subTotal  = (float)($buyNowItemData['total_price'] ?? $buyNowItemData['price'] ?? 0);
    }

    // Build pseudo-cart for the CheckoutService
    $cartItems[] = [
        'quantity'  => (int)($buyNowItemData['quantity'] ?? 1),
        'sub_total' => $subTotal
    ];
    $cartTotal = $subTotal;

} else {
    // ── OMNI-CHANNEL CART FLOW ──
    // Use the upgraded service to fetch BOTH Frames and Prints
    $cartItems = $checkoutService->getCartItems($customer_id);

    if (empty($cartItems)) {
        echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
        exit();
    }

    // Sum up the cart total
    foreach ($cartItems as $item) {
        $cartTotal += (float)$item['sub_total'];
    }
}

// ── THE MAGIC HANDOFF ──
// Let the upgraded CheckoutService handle EVERYTHING (Discounts, GCash, Database Inserts)
$response = $checkoutService->processCheckout(
    $customer_id, 
    $_POST, 
    $_FILES, 
    $cartItems, 
    $cartTotal, 
    $isBuyNow, 
    $buyNowItemData
);

// If Buy Now was successful, clear the floating session data
if ($response['success'] && $isBuyNow) {
    unset($_SESSION['buy_now_item']);
}

echo json_encode($response);
exit();