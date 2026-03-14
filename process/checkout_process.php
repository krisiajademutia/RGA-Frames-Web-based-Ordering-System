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

if ($isBuyNow) {
    // ── Buy Now flow ─────────────────────────────────────
    $cfService = new CustomFrameService($conn);
    $itemData  = $_SESSION['buy_now_item'];
    $prices    = $cfService->calculatePrice($itemData);

    $delivery_option = strtoupper(trim($_POST['delivery_option'] ?? 'PICKUP'));
    $payment_method  = strtoupper(trim($_POST['payment_method']  ?? 'CASH'));
    $address         = $delivery_option === 'DELIVERY' ? trim($_POST['delivery_address'] ?? '') : null;

    if ($delivery_option === 'DELIVERY' && empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Please enter your delivery address.']);
        exit();
    }

    // Build pseudo cart so discount + delivery unlock checks work consistently
    $pseudoCart = [['quantity' => max(1, (int)($itemData['quantity'] ?? 1)), 'sub_total' => $prices['grand_total']]];

    // Guard: delivery only when unlocked
    if ($delivery_option === 'DELIVERY' && !$checkoutService->isDeliveryUnlocked($pseudoCart)) {
        echo json_encode(['success' => false, 'message' => 'Delivery requires a minimum of 30 frames.']);
        exit();
    }

    $customer     = $checkoutService->getCustomerDetails($customer_id);
    $discount     = $checkoutService->calculateDiscount($customer_id, $customer, $pseudoCart, $prices['grand_total']);
    $delivery_fee = $delivery_option === 'DELIVERY' ? 150.00 : 0.00;
    $final_total  = round(($prices['grand_total'] - $discount['discount_amount']) + $delivery_fee, 2);
    if ($final_total < 0) $final_total = 0.00;

    // Handle GCash receipt
    $paymentProof = null;
    if ($payment_method === 'GCASH') {
        if (!isset($_FILES['receipt_image']) || $_FILES['receipt_image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'GCash receipt is required.']);
            exit();
        }
        $uploadDir = __DIR__ . '/../uploads/uploaded_receipts/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['receipt_image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid receipt format.']);
            exit();
        }
        $fileName = 'gcash_' . $customer_id . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($_FILES['receipt_image']['tmp_name'], $uploadDir . $fileName)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload receipt.']);
            exit();
        }
        $paymentProof = [
            'file_path' => 'uploads/uploaded_receipts/' . $fileName,
            'amount'    => (float)($_POST['gcash_amount'] ?? 0),
        ];
    }

    // Inject calculated values into itemData for buyNow
    $itemData['payment_method']   = $payment_method;
    $itemData['delivery_option']  = $delivery_option;
    $itemData['delivery_address'] = $address;
    $itemData['discount_amount']  = $discount['discount_amount'];
    $itemData['final_total']      = $final_total;

    $result = $cfService->buyNow($customer_id, $itemData);

    if ($result['success']) {
        // Insert GCash proof if needed
        if ($paymentProof && !empty($result['order_id'])) {
            $stmt = $conn->prepare("SELECT payment_id FROM tbl_payment WHERE order_id = ?");
            $stmt->bind_param("i", $result['order_id']);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if ($row) {
                $stmt2 = $conn->prepare("
                    INSERT INTO tbl_payment_proof_uploads
                        (payment_id, payment_proof, uploaded_amount, verification_status)
                    VALUES (?, ?, ?, 'Pending Verification')
                ");
                $stmt2->bind_param("isd", $row['payment_id'], $paymentProof['file_path'], $paymentProof['amount']);
                $stmt2->execute();
            }
        }
        unset($_SESSION['buy_now_item']);
        echo json_encode(['success' => true, 'message' => 'Order placed successfully!']);
    } else {
        echo json_encode($result);
    }

} else {
    // ── Cart flow ─────────────────────────────────────────
    $stmt = $conn->prepare("
        SELECT ci.* FROM tbl_frame_order_items ci
        JOIN tbl_cart c ON ci.cart_id = c.cart_id
        WHERE c.customer_id = ? AND ci.source_type = 'CART'
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result    = $stmt->get_result();
    $cartItems = [];
    $cartTotal = 0.00;
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $cartTotal  += (float)$row['sub_total'];
    }

    if (empty($cartItems)) {
        echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
        exit();
    }

    $response = $checkoutService->processCheckout($customer_id, $_POST, $_FILES, $cartItems, $cartTotal);
    echo json_encode($response);
}
exit();