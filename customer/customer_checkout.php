<?php
// customer/customer_checkout.php
session_start();
include __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Checkout/CheckoutService.php';
require_once __DIR__ . '/../classes/CustomFrame/CustomFrameService.php';

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}

$customer_id     = (int)$_SESSION['user_id'];
$checkoutService = new CheckoutService($conn);
$customer        = $checkoutService->getCustomerDetails($customer_id);
$isBuyNow        = isset($_SESSION['buy_now_item']);
$cartItems       = [];
$cartTotal       = 0;

if ($isBuyNow) {
   
    // ── OMNI-CHANNEL BUY NOW FLOW ──
    $itemData = $_SESSION['buy_now_item'];
    $itemType = $itemData['item_type'] ?? 'CUSTOM_FRAME'; // Default fallback

    if ($itemType === 'CUSTOM_FRAME') {
        $cfService = new CustomFrameService($conn);
        $prices    = $cfService->calculatePrice($itemData);
        $qty       = (int)($itemData['quantity'] ?? 1);
        $svcLabel  = $itemData['service_type'] === 'FRAME&PRINT' ? 'Frame & Print' : 'Frame only';
        
        $cartItems[] = [
            'is_buy_now'   => true,
            'item_data'    => $itemData,
            'display_name' => 'Custom Frame (' . ($itemData['width'] ?? 0) . '" × ' . ($itemData['height'] ?? 0) . '")',
            'display_meta' => $svcLabel . ' | Qty: ' . $qty,
            'quantity'     => $qty,
            'sub_total'    => $prices['grand_total'],
        ];
        $cartTotal = $prices['grand_total'];

    } elseif ($itemType === 'PRINTING') {
        $qty      = (int)($itemData['quantity'] ?? 1);
        $subTotal = (float)($itemData['total_price'] ?? $itemData['price'] ?? 0);
        
        $cartItems[] = [
            'is_buy_now'   => true,
            'item_data'    => $itemData,
            'display_name' => 'Photo Print (' . ($itemData['width'] ?? 0) . '" × ' . ($itemData['height'] ?? 0) . '")',
            'display_meta' => 'Paper: ' . ($itemData['paper_name'] ?? 'Standard') . ' | Qty: ' . $qty,
            'quantity'     => $qty,
            'sub_total'    => $subTotal,
        ];
        $cartTotal = $subTotal;

    } elseif ($itemType === 'READY_MADE') {
        $qty      = (int)($itemData['quantity'] ?? 1);
        $subTotal = (float)($itemData['total_price'] ?? 0);
        
        $cartItems[] = [
            'is_buy_now'   => true,
            'item_data'    => $itemData,
            'display_name' => 'Ready Made: ' . ($itemData['product_name'] ?? 'Frame'),
            'display_meta' => 'Qty: ' . $qty,
            'quantity'     => $qty,
            'sub_total'    => $subTotal,
        ];
        $cartTotal = $subTotal;
    }
} else {
    // ── OMNI-CHANNEL CART FLOW ──
    $rawCartItems = $checkoutService->getCartItems($customer_id);
    if (empty($rawCartItems)) {
        header("Location: customer_shop_custom.php");
        exit();
    }

    foreach ($rawCartItems as $item) {
        if (isset($item['category_type']) && $item['category_type'] === 'FRAME') {
            $isCustom = !empty($item['c_product_id']);
            $dimensions = $isCustom ? "({$item['width']}\" × {$item['height']}\")" : "";
            $namePrefix = $isCustom ? "Custom: " . $item['custom_design_name'] : "Ready Made: " . $item['ready_name'];
            
            $itemName = trim($namePrefix . ' ' . $dimensions);
            $itemMeta = "Qty: " . $item['quantity'];
        } else {
            $dimensions = "({$item['width_inch']}\" × {$item['height_inch']}\")";
            $itemName = "Photo Print " . $dimensions;
            $itemMeta = "Paper: " . ($item['paper_name'] ?? 'Standard') . " | Qty: " . $item['quantity'];
        }
        
        $cartItems[] = [
            'is_buy_now'   => false,
            'display_name' => $itemName,
            'display_meta' => $itemMeta,
            'quantity'     => $item['quantity'],
            'sub_total'    => $item['sub_total'],
            'raw_data'     => $item
        ];
        $cartTotal += (float)$item['sub_total'];
    }
}

// ── Discount & delivery eligibility ─────────────────────
// The CheckoutService calculateDiscount function perfectly handles our unified cartItems format!
$discount         = $checkoutService->calculateDiscount($customer_id, $customer, $cartItems, $cartTotal);
$deliveryUnlocked = $checkoutService->isDeliveryUnlocked($cartItems);
$totalQty         = array_sum(array_column($cartItems, 'quantity'));
$discountedTotal  = round($cartTotal - $discount['discount_amount'], 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — RGA Frames</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include __DIR__ . '/../includes/customer_header.php'; ?>

<div class="chk-page">
    <div style="max-width:1200px; margin:0 auto 1rem;">
        <h1 class="chk-heading">Checkout</h1>
        <p class="chk-subheading">Confirm your order.</p>
    </div>

    <div class="chk-layout">

        <div>
            <form id="checkout-form" enctype="multipart/form-data">
                <?php if ($isBuyNow): ?>
                <input type="hidden" name="is_buy_now" value="1">
                <?php endif; ?>

                <div class="chk-card">
                    <div class="chk-card-header">Customer Details</div>
                    <div class="chk-card-body">
                        <div class="chk-details-grid">
                            <div>
                                <label class="chk-field-label">Full Name</label>
                                <div class="chk-field-value">
                                    <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                                </div>
                            </div>
                            <div>
                                <label class="chk-field-label">Phone Number</label>
                                <div class="chk-field-value">
                                    <?= htmlspecialchars($customer['phone_number']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chk-card">
                    <div class="chk-card-header">Fulfillment</div>
                    <div class="chk-card-body">

                        <?php if ($deliveryUnlocked): ?>
                            <div class="chk-free-delivery-badge">
                                <i class="fas fa-truck"></i> Your order qualifies for delivery!
                            </div>
                        <?php else: ?>
                            <div class="chk-delivery-locked-notice">
                                <i class="fas fa-info-circle" style="flex-shrink:0; margin-top:0.15rem;"></i>
                                <span>Delivery is available for orders of <strong><?= CheckoutService::BULK_QTY_THRESHOLD ?> or more frames/prints</strong>. Your order has <strong><?= $totalQty ?></strong> item<?= $totalQty !== 1 ? 's' : '' ?>.</span>
                            </div>
                        <?php endif; ?>

                        <label class="chk-radio-option selected" id="lbl-pickup">
                            <input type="radio" name="delivery_option" value="PICKUP" checked
                                   onchange="onDeliveryChange(this)">
                            <div>
                                <div class="chk-radio-title">Pickup</div>
                                <div class="chk-radio-sub">Pick up at our store</div>
                            </div>
                        </label>

                        <label class="chk-radio-option <?= !$deliveryUnlocked ? 'chk-radio-disabled' : '' ?>"
                               id="lbl-delivery">
                            <input type="radio" name="delivery_option" value="DELIVERY"
                                   <?= !$deliveryUnlocked ? 'disabled' : '' ?>
                                   onchange="onDeliveryChange(this)">
                            <div>
                                <div class="chk-radio-title">
                                    Delivery (Handled by Owner)
                                    <?php if (!$deliveryUnlocked): ?>
                                        <span class="chk-radio-lock">
                                            <i class="fas fa-lock"></i> 30+ items required
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="chk-radio-sub">We'll deliver to your address.</div>
                            </div>
                        </label>

                        <div id="address_wrapper" class="chk-address-wrap" style="display:none;">
                            <label class="chk-field-label">Delivery Address</label>
                            <textarea name="delivery_address" id="delivery_address"
                                      class="chk-textarea" rows="3"
                                      placeholder="Complete delivery address."></textarea>
                        </div>
                    </div>
                </div>

                <div class="chk-card">
                    <div class="chk-card-header">Payment Method</div>
                    <div class="chk-card-body">

                        <label class="chk-radio-option selected" id="lbl-cash">
                            <input type="radio" name="payment_method" value="CASH" checked
                                   onchange="onPaymentChange(this)">
                            <div>
                                <div class="chk-radio-title">Cash</div>
                                <div class="chk-radio-sub"></div>
                            </div>
                        </label>

                        <label class="chk-radio-option" id="lbl-gcash">
                            <input type="radio" name="payment_method" value="GCASH"
                                   onchange="onPaymentChange(this)">
                            <div>
                                <div class="chk-radio-title">GCash</div>
                                <div class="chk-radio-sub">Send via GCash and upload your receipt.</div>
                            </div>
                        </label>

                        <div id="gcash_wrapper" style="display:none;" class="chk-gcash-body">
                            <div class="chk-gcash-number">
                                <i class="fas fa-mobile-alt"></i>
                                Send payment to GCash: <strong>0912-345-6789</strong> (RGA Frames)
                            </div>

                            <label class="chk-field-label">Amount Paid (₱)</label>
                            <input type="number" name="gcash_amount" id="gcash_amount"
                                   class="chk-input-field" step="0.01" min="0"
                                   placeholder="e.g. <?= number_format($discountedTotal, 2, '.', '') ?>">

                            <label class="chk-field-label" style="margin-top:1rem;">Upload GCash Receipt</label>
                            <div class="chk-upload-zone" id="receipt-dropzone"
                                 onclick="document.getElementById('receipt_file').click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload image</p>
                                <span>JPG, PNG supported</span>
                            </div>
                            <input type="file" id="receipt_file" name="receipt_image"
                                   accept="image/jpeg,image/png,image/jpg,image/webp"
                                   style="display:none;" onchange="onReceiptSelected(this)">

                            <div class="chk-receipt-preview" id="receipt-preview"
                                 onclick="openReceiptLightbox()">
                                <img id="receipt-preview-img" src="" alt="Receipt Preview">
                                <div class="chk-receipt-preview-overlay">
                                    <span><i class="fas fa-expand"></i> View Full Size</span>
                                </div>
                            </div>
                            <button type="button" class="chk-receipt-change" id="receipt-change-btn"
                                    style="display:none;"
                                    onclick="document.getElementById('receipt_file').click()">
                                <i class="fas fa-redo"></i> Change image
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <div>
            <div class="chk-summary-card">
                <div class="chk-summary-header">Order Summary</div>
                <div class="chk-summary-body">

                    <?php foreach ($cartItems as $item): ?>
                    <div class="chk-item-row">
                        <div style="flex:1;">
                            <div class="chk-item-name"><?= htmlspecialchars($item['display_name']) ?></div>
                            <div class="chk-item-meta"><?= htmlspecialchars($item['display_meta']) ?></div>
                        </div>
                        <div style="display:flex; align-items:center;">
                            <span class="chk-item-qty">×<?= $item['quantity'] ?></span>
                            <span class="chk-item-price">₱<?= number_format($item['sub_total'], 2) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="chk-subtotal-row">
                        <span>Subtotal</span>
                        <span>×<?= $totalQty ?> &nbsp; ₱<?= number_format($cartTotal, 2) ?></span>
                    </div>

                    <?php if ($discount['qualified']): ?>
                    <div class="chk-discount-row">
                        <span><i class="fas fa-tag"></i> 20% Discount</span>
                        <span class="chk-discount-val">−₱<?= number_format($discount['discount_amount'], 2) ?></span>
                    </div>
                    <?php endif; ?>

                </div>

                <div class="chk-total-section">

                    <div class="chk-delivery-fee-row" id="delivery-fee-row" style="display:none;">
                        <span>Delivery Fee</span>
                        <span>+₱150.00</span>
                    </div>

                    <div class="chk-total-row">
                        <span class="chk-total-label">Total</span>
                        <span class="chk-total-val" id="summary-total">₱<?= number_format($discountedTotal, 2) ?></span>
                    </div>
                    <button type="submit" form="checkout-form" id="btn-place-order" class="chk-submit-btn">
                        Place Order
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="chk-lightbox" id="chk-lightbox">
    <div class="chk-lightbox-inner">
        <button class="chk-lightbox-close" onclick="closeReceiptLightbox()">
            <i class="fas fa-times"></i>
        </button>
        <img id="chk-lightbox-img" src="" alt="Receipt Full View">
    </div>
</div>

<?php include __DIR__ . '/../includes/idx_footer.php'; ?>

<script>
    const discountedSubtotal = <?= $discountedTotal ?>;
    const deliveryUnlocked   = <?= $deliveryUnlocked ? 'true' : 'false' ?>;
</script>
<script src="../assets/js/customer_checkout.js"></script>
</body>
</html>