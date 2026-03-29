<?php
ob_start(); // Start output buffering to trap any invisible errors
// process/checkout_process.php
session_start();
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Checkout/Repository/CheckoutRepository.php';
require_once __DIR__ . '/../classes/Checkout/CheckoutService.php';
require_once __DIR__ . '/../classes/CustomFrame/CustomFrameService.php';
require_once __DIR__ . '/../classes/Notification/NotificationRepository.php';
require_once __DIR__ . '/../classes/Notification/NotificationService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Please log in to place an order.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$customer_id     = (int)$_SESSION['user_id'];

// Instantiate Repositories
$checkoutRepo    = new CheckoutRepository($conn);
$notifRepo       = new NotificationRepository($conn);

// Instantiate Services with injected Repositories
$cfService       = new CustomFrameService($conn);
$checkoutService = new CheckoutService($checkoutRepo, $cfService);
$notifService    = new NotificationService($notifRepo); 

$isBuyNow = isset($_SESSION['buy_now_item']) && is_array($_SESSION['buy_now_item']) && !empty($_SESSION['buy_now_item']);

try {
    if ($isBuyNow) {
        // ── BUY NOW FLOW ──
        $buyNowItemData = $_SESSION['buy_now_item'];
        $itemType = $buyNowItemData['item_type'] ?? 'CUSTOM_FRAME';

        if ($itemType === 'CUSTOM_FRAME') {
            $prices = $cfService->calculatePrice($buyNowItemData);
            $cartTotal = $prices['grand_total'];
        } else {
            $cartTotal = (float)($buyNowItemData['total_price'] ?? 0);
        }

        $cartItems = []; 
        $response = $checkoutService->processCheckout($customer_id, $_POST, $_FILES, $cartItems, $cartTotal, true, $buyNowItemData);
        
        if ($response['success']) {
            unset($_SESSION['buy_now_item']);
            $_SESSION['order_success_pending'] = true; 
            
            $new_order_id = $response['order_id'] ?? 0;
            $ref = $response['ref_no'] ?? "New Order";

            if ($new_order_id == 0) {
                $new_order_id = $checkoutService->getLatestOrderId($customer_id);
            }

            $notifService->notifyCustomer($customer_id, $new_order_id, "Order Received", "Thank you! We have received your order ($ref) and will review it shortly.");
            $notifService->notifyAdmin($new_order_id, "New Order Alert", "A new order ($ref) has been placed by a customer.");
        }
        
        ob_clean(); // WIPE AWAY ANY HIDDEN ERRORS
        echo json_encode($response);
        exit();

    } else {
        // ── NORMAL CART FLOW ──
        $cartItems = $checkoutService->getCartItems($customer_id);
        
        if (empty($cartItems)) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
            exit();
        }

        $cartTotal = 0;
        foreach($cartItems as $item) {
            $cartTotal += (float)($item['sub_total'] ?? 0);
        }

        $response = $checkoutService->processCheckout($customer_id, $_POST, $_FILES, $cartItems, $cartTotal, false, null);
        
        if ($response['success']) {
            $_SESSION['order_success_pending'] = true; 
            
            $new_order_id = $response['order_id'] ?? 0;
            $ref = $response['ref_no'] ?? "New Order";

            if ($new_order_id == 0) {
                $new_order_id = $checkoutService->getLatestOrderId($customer_id);
            }

            $notifService->notifyCustomer($customer_id, $new_order_id, "Order Received", "Thank you! We have received your order ($ref) and will review it shortly.");
            $notifService->notifyAdmin($new_order_id, "New Order Alert", "A new order ($ref) has been placed by a customer.");
        }
        
        ob_clean(); // WIPE AWAY ANY HIDDEN ERRORS
        echo json_encode($response);
        exit();
    }
} catch (Throwable $e) {
    ob_clean(); // WIPE AWAY ANY HIDDEN ERRORS
    echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
}
?>