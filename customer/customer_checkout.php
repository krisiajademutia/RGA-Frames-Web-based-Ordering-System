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
    $cfService = new CustomFrameService($conn);
    $itemData  = $_SESSION['buy_now_item'];
    $prices    = $cfService->calculatePrice($itemData);
    $qty       = (int)($itemData['quantity'] ?? 1);
    $svcLabel  = $itemData['service_type'] === 'FRAME&PRINT' ? 'Frame & Print' : 'Frame only';
    $cartItems[] = [
        'is_buy_now'   => true,
        'item_data'    => $itemData,
        'display_name' => 'Custom Frame (' . $itemData['custom_width'] . '" × ' . $itemData['custom_height'] . '")',
        'display_meta' => $svcLabel . ' | Qty: ' . $qty,
        'quantity'     => $qty,
        'sub_total'    => $prices['grand_total'],
    ];
    $cartTotal = $prices['grand_total'];
} else {
    $cartItems = $checkoutService->getCartItems($customer_id);
    if (empty($cartItems)) {
        header("Location: customer_shop_custom.php");
        exit();
    }
    foreach ($cartItems as $item) {
        $cartTotal += (float)$item['sub_total'];
    }
}
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
    <style>
        /* ── Checkout Page ── */
        .chk-page {
            background: #fff;
            min-height: 100vh;
            padding: 6rem 2rem 4rem;
        }
        .chk-heading { font-size: 2rem; font-weight: 800; color: #0f3d33; margin: 0 0 0.15rem; }
        .chk-subheading { font-size: 0.92rem; color: #6b7280; margin: 0 0 2rem; }

        .chk-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
            align-items: start;
        }
        @media (max-width: 900px) { .chk-layout { grid-template-columns: 1fr; } }

        /* Section cards */
        .chk-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1.25rem;
        }
        .chk-card-header {
            background: #0f3d33;
            color: #fff;
            padding: 0.75rem 1.25rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .chk-card-body { padding: 1.25rem; background: #fff; }

        /* Customer details */
        .chk-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 600px) { .chk-details-grid { grid-template-columns: 1fr; } }

        .chk-field-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            color: #374151;
            text-transform: uppercase;
            margin-bottom: 0.4rem;
            display: block;
        }
        .chk-field-value {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #374151;
            background: #f9fafb;
        }

        /* Delivery radio options */
        .chk-free-delivery-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            border-radius: 20px;
            padding: 0.3rem 0.85rem;
            font-size: 0.78rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .chk-radio-option {
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 0.85rem 1.1rem;
            margin-bottom: 0.6rem;
            cursor: pointer;
            transition: border-color 0.15s, background 0.15s;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .chk-radio-option:hover { border-color: #0f3d33; }
        .chk-radio-option.selected { background: #d1e8e2; border-color: #0f3d33; }
        .chk-radio-option input[type="radio"] { margin-top: 2px; accent-color: #0f3d33; flex-shrink: 0; }
        .chk-radio-title { font-size: 0.88rem; font-weight: 700; color: #111827; }
        .chk-radio-sub   { font-size: 0.77rem; color: #6b7280; margin-top: 1px; }

        .chk-address-wrap { margin-top: 0.85rem; }
        .chk-textarea {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #374151;
            font-family: inherit;
            resize: vertical;
            outline: none;
            transition: border-color 0.15s;
        }
        .chk-textarea:focus { border-color: #0f3d33; }

        /* GCash section */
        .chk-gcash-body { margin-top: 1rem; }
        .chk-gcash-number {
            background: #f0fdf4;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            font-size: 0.83rem;
            color: #065f46;
            margin-bottom: 1rem;
        }
        .chk-input-field {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #374151;
            outline: none;
            transition: border-color 0.15s;
            margin-top: 0.35rem;
        }
        .chk-input-field:focus { border-color: #0f3d33; }

        .chk-upload-zone {
            border: 2px dashed #d1d5db;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            color: #9ca3af;
            margin-top: 0.35rem;
            transition: border-color 0.15s, background 0.15s;
        }
        .chk-upload-zone:hover { border-color: #0f3d33; background: #f0fdf4; color: #0f3d33; }
        .chk-upload-zone i { font-size: 1.5rem; display: block; margin-bottom: 0.35rem; }
        .chk-upload-zone p { font-size: 0.82rem; font-weight: 600; margin: 0; }
        .chk-upload-zone span { font-size: 0.72rem; }

        /* Order Summary */
        .chk-summary-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            position: sticky;
            top: 5.5rem;
        }
        .chk-summary-header {
            background: #0f3d33;
            color: #fff;
            padding: 0.75rem 1.25rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .chk-summary-body { padding: 1rem 1.25rem; background: #fff; }

        .chk-item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.6rem 0;
            border-bottom: 1px solid #f3f4f6;
            gap: 0.5rem;
        }
        .chk-item-row:last-child { border-bottom: none; }
        .chk-item-name { font-size: 0.83rem; font-weight: 600; color: #111827; }
        .chk-item-meta { font-size: 0.73rem; color: #9ca3af; margin-top: 1px; }
        .chk-item-qty  { font-size: 0.78rem; color: #6b7280; margin: 0 0.5rem; white-space: nowrap; }
        .chk-item-price { font-size: 0.85rem; font-weight: 700; color: #0f3d33; white-space: nowrap; }

        .chk-subtotal-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.83rem;
            color: #6b7280;
            border-top: 1px solid #f3f4f6;
            margin-top: 0.5rem;
        }
        .chk-total-section { padding: 1rem 1.25rem; border-top: 1px solid #e5e7eb; }
        .chk-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chk-total-label { font-size: 0.9rem; font-weight: 700; color: #374151; }
        .chk-total-val   { font-size: 1.6rem; font-weight: 900; color: #0f3d33; }

        .chk-submit-btn {
            width: 100%;
            padding: 0.85rem;
            background: #0f3d33;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 1rem;
            transition: background 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .chk-submit-btn:hover:not(:disabled)    { background: #0a2e26; }
        .chk-submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

        /* Receipt preview */
        .chk-receipt-preview {
            display: none;
            margin-top: 0.75rem;
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid #a7f3d0;
        }
        .chk-receipt-preview img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            display: block;
            border-radius: 8px;
        }
        .chk-receipt-preview-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
            border-radius: 8px;
        }
        .chk-receipt-preview:hover .chk-receipt-preview-overlay {
            background: rgba(0,0,0,0.35);
        }
        .chk-receipt-preview-overlay span {
            color: #fff;
            font-size: 0.82rem;
            font-weight: 700;
            opacity: 0;
            transition: opacity 0.2s;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .chk-receipt-preview:hover .chk-receipt-preview-overlay span { opacity: 1; }
        .chk-receipt-change {
            margin-top: 0.4rem;
            font-size: 0.75rem;
            color: #0f3d33;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            background: none;
            border: none;
            padding: 0;
        }

        /* Lightbox */
        .chk-lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.85);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .chk-lightbox.open { display: flex; }
        .chk-lightbox-inner { position: relative; max-width: 90vw; max-height: 90vh; }
        .chk-lightbox-inner img {
            max-width: 100%;
            max-height: 90vh;
            border-radius: 10px;
            object-fit: contain;
            display: block;
        }
        .chk-lightbox-close {
            position: absolute;
            top: -2.5rem;
            right: 0;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            line-height: 1;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/customer_header.php'; ?>

<div class="chk-page">
    <div style="max-width:1200px; margin:0 auto 1rem;">
        <h1 class="chk-heading">Checkout</h1>
        <p class="chk-subheading">Confirm your order.</p>
    </div>

    <div class="chk-layout">

        <!-- ── LEFT COLUMN ── -->
        <div>
            <form id="checkout-form" enctype="multipart/form-data">
                <?php if ($isBuyNow): ?>
                <input type="hidden" name="is_buy_now" value="1">
                <?php endif; ?>

                <!-- Customer Details -->
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

                <!-- Fulfillment -->
                <div class="chk-card">
                    <div class="chk-card-header">Fulfillment</div>
                    <div class="chk-card-body">
                        <div class="chk-free-delivery-badge">
                            <i class="fas fa-truck"></i> Your order qualifies for free delivery!
                        </div>

                        <label class="chk-radio-option selected" id="lbl-pickup">
                            <input type="radio" name="delivery_option" value="PICKUP" checked
                                   onchange="onDeliveryChange(this)">
                            <div>
                                <div class="chk-radio-title">Pickup</div>
                                <div class="chk-radio-sub">Pick up at our store</div>
                            </div>
                        </label>

                        <label class="chk-radio-option" id="lbl-delivery">
                            <input type="radio" name="delivery_option" value="DELIVERY"
                                   onchange="onDeliveryChange(this)">
                            <div>
                                <div class="chk-radio-title">Delivery (Handled by Owner)</div>
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

                <!-- Payment Method -->
                <div class="chk-card">
                    <div class="chk-card-header">Payment Method</div>
                    <div class="chk-card-body">

                        <label class="chk-radio-option selected" id="lbl-cash">
                            <input type="radio" name="payment_method" value="CASH" checked
                                   onchange="onPaymentChange(this)">
                            <div>
                                <div class="chk-radio-title">Cash</div>
                                <div class="chk-radio-sub">Pay at least 50% upon pickup/delivery</div>
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
                                   placeholder="e.g. <?= number_format($cartTotal, 2, '.', '') ?>">

                            <label class="chk-field-label" style="margin-top:1rem;">Upload GCash Receipt</label>
                            <div class="chk-upload-zone" id="receipt-dropzone" onclick="document.getElementById('receipt_file').click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload image</p>
                                <span>JPG, PNG supported</span>
                            </div>
                            <input type="file" id="receipt_file" name="receipt_image"
                                   accept="image/jpeg,image/png,image/jpg,image/webp"
                                   style="display:none;" onchange="onReceiptSelected(this)">

                            <!-- Preview (shown after upload) -->
                            <div class="chk-receipt-preview" id="receipt-preview" onclick="openReceiptLightbox()">
                                <img id="receipt-preview-img" src="" alt="Receipt Preview">
                                <div class="chk-receipt-preview-overlay">
                                    <span><i class="fas fa-expand"></i> View Full Size</span>
                                </div>
                            </div>
                            <button type="button" class="chk-receipt-change" id="receipt-change-btn"
                                    style="display:none;" onclick="document.getElementById('receipt_file').click()">
                                <i class="fas fa-redo"></i> Change image
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <!-- ── RIGHT COLUMN — Order Summary ── -->
        <div>
            <div class="chk-summary-card">
                <div class="chk-summary-header">Order Summary</div>
                <div class="chk-summary-body">
                    <?php foreach ($cartItems as $item):
                        if (!empty($item['is_buy_now'])) {
                            $displayName = $item['display_name'];
                            $displayMeta = $item['display_meta'];
                            $qty         = $item['quantity'];
                            $subtotal    = (float)$item['sub_total'];
                        } else {
                            $isCustom    = !empty($item['c_product_id']);
                            $displayName = $isCustom
                                ? ($item['custom_design_name'] ?? 'Custom Frame')
                                : ($item['ready_name'] ?? 'Frame');
                            $svcType     = $item['service_type'] === 'FRAME&PRINT' ? 'Frame & Print' : 'Frame only';
                            $displayMeta = $svcType;
                            $qty         = (int)$item['quantity'];
                            $subtotal    = (float)$item['sub_total'];
                        }
                    ?>
                    <div class="chk-item-row">
                        <div style="flex:1;">
                            <div class="chk-item-name"><?= htmlspecialchars($displayName) ?></div>
                            <div class="chk-item-meta"><?= htmlspecialchars($displayMeta) ?></div>
                        </div>
                        <div style="display:flex; align-items:center;">
                            <span class="chk-item-qty">×<?= $qty ?></span>
                            <span class="chk-item-price">₱<?= number_format($subtotal, 2) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="chk-subtotal-row">
                        <span>Subtotal</span>
                        <span>×<?= array_sum(array_column($cartItems, 'quantity')) ?> &nbsp; ₱<?= number_format($cartTotal, 2) ?></span>
                    </div>
                </div>

                <div class="chk-total-section">
                    <div class="chk-total-row">
                        <span class="chk-total-label">Total</span>
                        <span class="chk-total-val" id="summary-total">₱<?= number_format($cartTotal, 2) ?></span>
                    </div>
                    <button type="submit" form="checkout-form" id="btn-place-order" class="chk-submit-btn">
                        Place Order
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Receipt Lightbox -->
<div class="chk-lightbox" id="chk-lightbox">
    <div class="chk-lightbox-inner">
        <button class="chk-lightbox-close" onclick="closeReceiptLightbox()">
            <i class="fas fa-times"></i>
        </button>
        <img id="chk-lightbox-img" src="" alt="Receipt Full View">
    </div>
</div>

<?php include __DIR__ . '/../includes/idx_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const subtotal = <?= $cartTotal ?>;

    function onDeliveryChange(radio) {
        document.getElementById('lbl-pickup').classList.remove('selected');
        document.getElementById('lbl-delivery').classList.remove('selected');
        radio.closest('label').classList.add('selected');
        const isDelivery = radio.value === 'DELIVERY';
        document.getElementById('address_wrapper').style.display = isDelivery ? 'block' : 'none';
        const fee   = isDelivery ? 150 : 0;
        const total = subtotal + fee;
        document.getElementById('summary-total').textContent =
            '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function onPaymentChange(radio) {
        document.getElementById('lbl-cash').classList.remove('selected');
        document.getElementById('lbl-gcash').classList.remove('selected');
        radio.closest('label').classList.add('selected');
        document.getElementById('gcash_wrapper').style.display = radio.value === 'GCASH' ? 'block' : 'none';
    }

    function onReceiptSelected(input) {
        if (!input.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => {
            // Show preview, hide dropzone
            document.getElementById('receipt-dropzone').style.display = 'none';
            const preview = document.getElementById('receipt-preview');
            document.getElementById('receipt-preview-img').src = e.target.result;
            preview.style.display = 'block';
            document.getElementById('receipt-change-btn').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }

    function openReceiptLightbox() {
        const src = document.getElementById('receipt-preview-img').src;
        document.getElementById('chk-lightbox-img').src = src;
        document.getElementById('chk-lightbox').classList.add('open');
    }

    function closeReceiptLightbox() {
        document.getElementById('chk-lightbox').classList.remove('open');
    }

    // Close lightbox on backdrop click
    document.getElementById('chk-lightbox')?.addEventListener('click', e => {
        if (e.target.id === 'chk-lightbox') closeReceiptLightbox();
    });

    document.getElementById('checkout-form').addEventListener('submit', async e => {
        e.preventDefault();

        const delivery = document.querySelector('input[name="delivery_option"]:checked').value;
        const payment  = document.querySelector('input[name="payment_method"]:checked').value;

        if (delivery === 'DELIVERY' && !document.getElementById('delivery_address').value.trim()) {
            Swal.fire('Missing Address', 'Please enter your delivery address.', 'warning'); return;
        }
        if (payment === 'GCASH' && !document.getElementById('receipt_file').files.length) {
            Swal.fire('Missing Receipt', 'Please upload your GCash receipt.', 'warning'); return;
        }

        const btn = document.getElementById('btn-place-order');
        btn.disabled     = true;
        btn.innerHTML    = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        try {
            const res  = await fetch('../process/checkout_process.php', {
                method: 'POST',
                body:   new FormData(e.target)
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ icon:'success', title:'Order Placed!', text:data.message, confirmButtonColor:'#0f3d33' })
                    .then(() => { window.location.href = 'customer_orders.php'; });
            } else {
                Swal.fire('Error', data.message, 'error');
                btn.disabled  = false;
                btn.innerHTML = 'Place Order';
            }
        } catch (err) {
            Swal.fire('Error', 'Network error. Please try again.', 'error');
            btn.disabled  = false;
            btn.innerHTML = 'Place Order';
        }
    });
</script>
</body>
</html>