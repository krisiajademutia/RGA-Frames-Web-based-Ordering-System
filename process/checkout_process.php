<?php
// process/checkout_process.php

// ────────────────────────────────────────────────
//   Remove or comment these out in production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ────────────────────────────────────────────────

session_start();
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Checkout/CheckoutService.php';
require_once __DIR__ . '/../classes/Order/DirectOrderFactory.php'; // New Factory
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

// Improved Buy Now detection
$isBuyNow = isset($_SESSION['buy_now_item']) && is_array($_SESSION['buy_now_item']) && !empty($_SESSION['buy_now_item']);

if ($isBuyNow) {
    // ── 1. Preparation ─────────────────────────────────────
    $itemData = $_SESSION['buy_now_item'];
    $itemType = $itemData['item_type'] ?? 'CUSTOM_FRAME';

    // ONLY use CustomFrameService if the item is actually a custom frame
    if ($itemType === 'CUSTOM_FRAME') {
        $cfService = new CustomFrameService($conn);
        $prices = $cfService->calculatePrice($itemData);
        $subTotalForDiscount = $prices['grand_total'];
    } else {
        // For Printing or Ready-Made, the price is usually already in the session
        $subTotalForDiscount = (float)($itemData['total_price'] ?? 0);
        $prices = ['grand_total' => $subTotalForDiscount];
    }
    
    // We still need CustomFrameService specifically for its unique calculation logic
    //$cfService = new CustomFrameService($conn);
    // $prices    = $cfService->calculatePrice($itemData);

    // ── 2. Discount & Delivery Calculation (Your Logic) ────
    $customer     = $checkoutService->getCustomerDetails($customer_id);
    $fakeCartItem = [['quantity' => (int)($itemData['quantity'] ?? 1)]];
    $discountData = $checkoutService->calculateDiscount($customer_id, $customer, $fakeCartItem, $prices['grand_total']);

    $delivery_option = strtoupper(trim($_POST['delivery_option'] ?? 'PICKUP'));
    $payment_method  = strtoupper(trim($_POST['payment_method']  ?? 'CASH'));
    $address         = $delivery_option === 'DELIVERY' ? trim($_POST['delivery_address'] ?? '') : null;
    $delivery_fee    = $delivery_option === 'DELIVERY' ? 150.00 : 0.00;

    // Final Calculation including your logic
    $finalTotal = round($prices['grand_total'] - $discountData['discount_amount'] + $delivery_fee, 2);

    if ($delivery_option === 'DELIVERY' && empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Please enter your delivery address.']);
        exit();
    }

    // ── 3. GCash Proof Handling ────────────────────────────
    $paymentProof = null;
    if ($payment_method === 'GCASH') {
        if (!isset($_FILES['receipt_image']) || $_FILES['receipt_image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'GCash receipt is required.']);
            exit();
        }

        $uploadDir = __DIR__ . '/../uploads/uploaded_receipts/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['receipt_image']['name'], PATHINFO_EXTENSION));
        $fileName = 'gcash_' . $customer_id . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['receipt_image']['tmp_name'], $destPath)) {
            $paymentProof = [
                'file_path' => 'uploads/uploaded_receipts/' . $fileName,
                'amount'    => (float)($_POST['gcash_amount'] ?? 0),
            ];
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload receipt.']);
            exit();
        }
    }

    // ── 4. Execute via Factory ─────────────────────────────
    try {
        // Prepare the data bundle for the Service
        $itemData['payment_method']  = $payment_method;
        $itemData['delivery_option'] = $delivery_option;
        $itemData['delivery_address'] = $address;
        $itemData['sub_total']       = $prices['grand_total'];   
        $itemData['discount_amount'] = $discountData['discount_amount'];
        $itemData['final_total']     = $finalTotal; 

        // Get the right service (Custom, Printing, etc.)
        $orderService = DirectOrderFactory::getService($conn, $itemType);
        $result = $orderService->placeBuyNow($customer_id, $itemData);

        if ($result['success']) {
            // Save GCash proof to DB if it exists
            if ($paymentProof && !empty($result['order_id'])) {
                $stmt = $conn->prepare("SELECT payment_id FROM tbl_payment WHERE order_id = ?");
                $stmt->bind_param("i", $result['order_id']);
                $stmt->execute();
                $pRow = $stmt->get_result()->fetch_assoc();

                if ($pRow) {
                    $stmt2 = $conn->prepare("
                        INSERT INTO tbl_payment_proof_uploads 
                        (payment_id, payment_proof, uploaded_amount, verification_status)
                        VALUES (?, ?, ?, 'Pending Verification')
                    ");
                    $stmt2->bind_param("isd", $pRow['payment_id'], $paymentProof['file_path'], $paymentProof['amount']);
                    $stmt2->execute();
                }
            }

            unset($_SESSION['buy_now_item']);
            echo json_encode([
                'success' => true,
                'message' => 'Order placed successfully!',
                'ref_no'  => $result['ref_no'] ?? null
            ]);
        } else {
            echo json_encode($result);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
    }

} else {
    // ── Normal Cart Flow ──────────────────────────────────
    // (Your existing cart logic remains unchanged)
    $stmt = $conn->prepare("
        SELECT ci.* FROM tbl_frame_order_items ci
        JOIN tbl_cart c ON ci.cart_id = c.cart_id
        WHERE c.customer_id = ? AND ci.source_type = 'CART'
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $cartTotal = 0;
    foreach($cartItems as $item) $cartTotal += (float)$item['sub_total'];

    if (empty($cartItems)) {
        echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
        exit();
    }

    $response = $checkoutService->processCheckout($customer_id, $_POST, $_FILES, $cartItems, $cartTotal);
    echo json_encode($response);
}