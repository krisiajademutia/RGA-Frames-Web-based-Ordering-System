<?php
// customer/customer_order_details.php
session_start();
include __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Order/CustomerOrderService.php';

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: customer_orders.php");
    exit();
}

$customer_id = (int)$_SESSION['user_id'];
$order_id    = (int)$_GET['id'];
$service     = new CustomerOrderService($conn);
$order       = $service->getOrderDetails($order_id, $customer_id);
if (!$order) { header("Location: customer_orders.php"); exit(); }

/* ── Stepper config ── */
$stepper_steps = [
    ['label' => 'Pending',          'keys' => ['PENDING']],
    ['label' => 'Processing',       'keys' => ['PROCESSING']],
    ['label' => 'Pick-up / Delivery','keys' => ['READY_FOR_PICKUP','FOR_DELIVERY']],
    ['label' => 'Completed',        'keys' => ['COMPLETED']],
];
$status_index = [
    'PENDING'          => 0,
    'PROCESSING'       => 1,
    'READY_FOR_PICKUP' => 2,
    'FOR_DELIVERY'     => 2,
    'COMPLETED'        => 3,
    'CANCELLED'        => 3,
    'REJECTED'         => 3,
];
$cur   = $status_index[$order['order_status']] ?? 0;
$isErr = in_array($order['order_status'], ['CANCELLED','REJECTED']);

/* ── Flags ── */
$isCompleted = $order['order_status'] === 'COMPLETED';
$isPending   = $order['order_status'] === 'PENDING';
$isGcash     = $order['payment_method'] === 'GCASH';

$total_price    = (float)($order['total_price']  ?? 0);
$amount_paid    = (float)($order['amount_paid']  ?? 0);
$balance_due    = max(0, $total_price - $amount_paid);
$payment_status = $order['payment_status'] ?? 'PENDING';
$payment_id     = (int)($order['payment_id'] ?? 0);
$proofs         = $order['proofs'] ?? [];
$items          = $order['items']  ?? [];

/* ── Banner ── */
$bannerMap = [
    'PENDING'          => ['Your order is awaiting confirmation',
                           "We've received your order and will review it shortly. You'll be notified once it's accepted.",
                           'cst-ord-dtls-banner-pending'],
    'PROCESSING'       => ['Your order is being prepared.',
                           "Our team is working on your order. We will notify you once it's ready for pickup or delivery.",
                           'cst-ord-dtls-banner-processing'],
    'READY_FOR_PICKUP' => ['Your order is ready for pick-up!',
                           'Please bring a valid ID and your order reference number when you come to the store.',
                           'cst-ord-dtls-banner-ready'],
    'FOR_DELIVERY'     => ['Your order is out for delivery!',
                           "Your frame is on its way. Please be available to receive it.",
                           'cst-ord-dtls-banner-ready'],
    'COMPLETED'        => ['Order Completed — Thank you!',
                           'Your order has been completed. You can download your receipt below.',
                           'cst-ord-dtls-banner-completed'],
    'CANCELLED'        => ['Your order has been cancelled.',
                           'Please contact us for more information.',
                           'cst-ord-dtls-banner-rejected'],
    'REJECTED'         => ['Your order has been rejected.',
                           'Please contact us for more information.',
                           'cst-ord-dtls-banner-rejected'],
];
$banner = $bannerMap[$order['order_status']] ?? $bannerMap['PENDING'];

/* ── Status badge ── */
$statusBadgeMap = [
    'PENDING'          => ['Pending',           'cst-ord-badge-pending'],
    'PROCESSING'       => ['Processing',        'cst-ord-badge-processing'],
    'READY_FOR_PICKUP' => ['Ready for Pick-up', 'cst-ord-badge-pickup'],
    'FOR_DELIVERY'     => ['Out for Delivery',  'cst-ord-badge-delivery'],
    'COMPLETED'        => ['Completed',         'cst-ord-badge-completed'],
    'CANCELLED'        => ['Cancelled',         'cst-ord-badge-cancelled'],
    'REJECTED'         => ['Rejected',          'cst-ord-badge-rejected'],
];
[$badgeLabel, $badgeClass] = $statusBadgeMap[$order['order_status']] ?? ['Unknown','cst-ord-badge-pending'];

/* ── Service / category ── */
$hasFrame = false; $hasPrint = false; $isCustom = false;
foreach ($items as $item) {
    if (!empty($item['r_product_id']) || !empty($item['c_product_id'])) $hasFrame = true;
    if ($item['service_type'] === 'FRAME&PRINT' || !empty($item['image_path'])) $hasPrint = true;
    if (!empty($item['c_product_id'])) $isCustom = true;
}
$serviceLabel  = $hasFrame ? ($hasPrint ? 'Frame & Print' : 'Frame only') : 'Print only';
$categoryLabel = $isCustom ? 'Custom Frame' : ($hasFrame ? 'Ready-made' : 'Print');

/* ── Payment status ── */
$psLabel = match($payment_status) { 'FULL'=>'Fully Paid', 'PARTIAL'=>'Partial Payment', default=>'Unpaid' };
$psClass = match($payment_status) { 'FULL'=>'cst-ord-dtls-ps-full', 'PARTIAL'=>'cst-ord-dtls-ps-partial', default=>'cst-ord-dtls-ps-unpaid' };
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= $order_id ?> — RGA Frames</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include __DIR__ . '/../includes/customer_header.php'; ?>

<div class="cst-ord-dtls-page">

    <!-- ── Back ── -->
    <a href="customer_orders.php?status=<?= $order['order_status'] ?>" class="cst-ord-dtls-back">
        ← Back to &nbsp;Order List
    </a>

    <!-- ── Banner ── -->
    <div class="cst-ord-dtls-banner <?= $banner[2] ?>">
        <h2 class="cst-ord-dtls-banner-title"><?= htmlspecialchars($banner[0]) ?></h2>
        <p  class="cst-ord-dtls-banner-sub"><?= htmlspecialchars($banner[1]) ?></p>
    </div>

    <!-- ── Stepper ── -->
    <div class="cst-ord-dtls-stepper-wrap">
        <?php foreach ($stepper_steps as $i => $step):
            $isDone   = !$isErr && $cur > $i;
            $isActive = $cur === $i;
            $isFailed = $isErr && $i === 3;

            if ($isDone)        $cls = 'step-done';
            elseif ($isFailed)  $cls = 'step-error';
            elseif ($isActive)  $cls = 'step-active';
            else                $cls = '';
        ?>
        <div class="cst-ord-dtls-step <?= $cls ?>">
            <div class="cst-ord-dtls-step-circle">
                <?php if ($isDone): ?>
                    <i class="fas fa-check"></i>
                <?php elseif ($isFailed): ?>
                    <i class="fas fa-times"></i>
                <?php else: ?>
                    <?= $i + 1 ?>
                <?php endif; ?>
            </div>
            <span class="cst-ord-dtls-step-label"><?= $step['label'] ?></span>
        </div>
        <?php if ($i < count($stepper_steps) - 1): ?>
        <div class="cst-ord-dtls-step-line <?= (!$isErr && $cur > $i) ? 'line-done' : '' ?>"></div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- ── Order header card ── -->
    <div class="cst-ord-dtls-order-card">
        <div class="cst-ord-dtls-order-card-left">
            <span class="cst-ord-dtls-order-label">ORDER</span>
            <span class="cst-ord-dtls-order-id"># <?= $order_id ?></span>
            <div class="cst-ord-dtls-order-meta">
                <span class="cst-ord-dtls-ref-badge"><?= htmlspecialchars($order['order_reference_no']) ?></span>
                <span class="cst-ord-dtls-placed">
                    <span class="cst-ord-card-date-dot"></span>
                    Placed on <?= date('M d, Y', strtotime($order['created_at'])) ?> | <?= date('g:i A', strtotime($order['created_at'])) ?>
                </span>
            </div>
        </div>
        <div class="cst-ord-dtls-order-card-right">
            <span class="cst-ord-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
            <?php if ($isCompleted): ?>
            <a href="../process/download_receipt.php?order_id=<?= $order_id ?>" class="cst-ord-dtls-dl-receipt-btn">
                <i class="fas fa-download"></i> Download Receipt
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Delivery + Payment row ── -->
    <div class="cst-ord-dtls-info-row-wrap">

        <!-- Delivery Information -->
        <div class="cst-ord-dtls-info-card">
            <h3 class="cst-ord-dtls-info-card-title">DELIVERY INFORMATION</h3>
            <div class="cst-ord-dtls-info-line">
                <span class="cst-ord-dtls-info-lbl">Option</span>
                <span class="cst-ord-dtls-info-val">
                    <span class="cst-ord-tag cst-ord-tag-pickup">
                        <i class="fas fa-<?= $order['delivery_option'] === 'DELIVERY' ? 'truck' : 'store' ?>"></i>
                        <?= $order['delivery_option'] === 'DELIVERY' ? 'Delivery' : 'Pickup' ?>
                    </span>
                </span>
            </div>
            <div class="cst-ord-dtls-info-line">
                <span class="cst-ord-dtls-info-lbl">Address</span>
                <span class="cst-ord-dtls-info-val">
                    <?= ($order['delivery_option'] === 'DELIVERY' && !empty($order['delivery_address']))
                        ? htmlspecialchars($order['delivery_address'])
                        : 'Pick up at store' ?>
                </span>
            </div>
            <div class="cst-ord-dtls-info-line">
                <span class="cst-ord-dtls-info-lbl">Service</span>
                <span class="cst-ord-dtls-info-val">
                    <span class="cst-ord-tag cst-ord-tag-service">
                        <i class="fas fa-border-all"></i> <?= htmlspecialchars($serviceLabel) ?>
                    </span>
                </span>
            </div>
            <div class="cst-ord-dtls-info-line">
                <span class="cst-ord-dtls-info-lbl">Category</span>
                <span class="cst-ord-dtls-info-val">
                    <span class="cst-ord-tag <?= $isCustom ? 'cst-ord-tag-custom' : 'cst-ord-tag-readymade' ?>">
                        <i class="fas fa-<?= $isCustom ? 'paint-brush' : 'tag' ?>"></i>
                        <?= htmlspecialchars($categoryLabel) ?>
                    </span>
                </span>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="cst-ord-dtls-info-card">
            <h3 class="cst-ord-dtls-info-card-title">PAYMENT SUMMARY</h3>
            <div class="cst-ord-dtls-info-line">
                <span class="cst-ord-dtls-info-lbl">Method</span>
                <span class="cst-ord-dtls-info-val">
                    <?php if ($order['payment_method'] === 'CASH'): ?>
                    <span class="cst-ord-tag cst-ord-tag-cash"><i class="fas fa-dollar-sign"></i> Cash</span>
                    <?php else: ?>
                    <span class="cst-ord-tag cst-ord-tag-gcash"><i class="fas fa-mobile-alt"></i> GCash</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="cst-ord-dtls-info-line">
                <span class="cst-ord-dtls-info-lbl">Status</span>
                <span class="cst-ord-dtls-info-val">
                    <span class="cst-ord-dtls-ps-badge <?= $psClass ?>">
                        <?php if ($payment_status === 'PARTIAL'): ?>
                        <i class="fas fa-exclamation-triangle" style="font-size:.65rem;"></i>
                        <?php endif; ?>
                        <?= $psLabel ?>
                    </span>
                </span>
            </div>

            <div class="cst-ord-dtls-payment-divider"></div>

            <div class="cst-ord-dtls-pay-row">
                <span class="cst-ord-dtls-pay-label">Grand Total</span>
                <span class="cst-ord-dtls-pay-val">₱<?= number_format($total_price, 2) ?></span>
            </div>
            <div class="cst-ord-dtls-pay-row">
                <span class="cst-ord-dtls-pay-label">Downpayment</span>
                <span class="cst-ord-dtls-pay-val down">- ₱<?= number_format($amount_paid, 2) ?></span>
            </div>
            <div class="cst-ord-dtls-payment-divider"></div>
            <div class="cst-ord-dtls-pay-row balance">
                <span class="cst-ord-dtls-pay-label">Balance Due</span>
                <span class="cst-ord-dtls-pay-val balance-val">₱<?= number_format($balance_due, 2) ?></span>
            </div>

            <?php if (!empty($proofs)): ?>
            <div class="cst-ord-dtls-proof-btns">
                <button class="cst-ord-dtls-view-proof-btn" onclick="openProofViewer()">
                    View Proof of Payment
                </button>
                <a href="../process/download_payment_proof.php?payment_id=<?= $payment_id ?>"
                   class="cst-ord-dtls-dl-proof-btn">
                    <i class="fas fa-download"></i> Download Proof of Payment
                </a>
            </div>
            <?php elseif ($isGcash && $isPending): ?>
            <div class="cst-ord-dtls-upload-wrap">
                <p class="cst-ord-dtls-upload-note">
                    <i class="fas fa-info-circle"></i>
                    Please upload your GCash receipt to confirm your payment.
                </p>
                <button class="cst-ord-dtls-upload-btn" onclick="openUploadModal()">
                    <i class="fas fa-upload"></i> Upload Receipt
                </button>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ── Order Details Table ── -->
    <div class="cst-ord-dtls-items-card">
        <div class="cst-ord-dtls-items-header">
            <i class="fas fa-box-open" style="color:#fbbf24;"></i>
            ORDER DETAILS
        </div>
        <table class="cst-ord-dtls-table">
            <thead>
                <tr>
                    <th>ITEM DETAILS</th>
                    <th>TYPE</th>
                    <th>QUANTITY</th>
                    <th>SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item):
                $itemIsCustom = $item['frame_category'] === 'CUSTOM';
                $itemIsReady  = $item['frame_category'] === 'READY_MADE';
                $itemHasPrint = $item['service_type'] === 'FRAME&PRINT' || !empty($item['image_path']);
                $isPrintOnly  = empty($item['r_product_id']) && empty($item['c_product_id']);

                // Fetch frame image
                $frameDesignId = $itemIsCustom ? ($item['cfp_frame_design_id'] ?? null) : ($item['rm_frame_design_id'] ?? null);
                $imgName = null;
                if ($frameDesignId) {
                    $imgStmt = $conn->prepare("SELECT image_name FROM tbl_frame_design_images WHERE frame_design_id=? AND is_primary=1 LIMIT 1");
                    $imgStmt->bind_param("i", $frameDesignId);
                    $imgStmt->execute();
                    $imgRow = $imgStmt->get_result()->fetch_assoc();
                    $imgName = $imgRow['image_name'] ?? null;
                }

                // Item name
                if ($itemIsReady)      $itemName = $item['ready_name'] ?? 'Ready-Made Frame';
                elseif ($itemIsCustom) $itemName = $itemHasPrint ? 'Custom Frame + Print' : ($item['design_name'] ?? 'Custom Frame');
                else                   $itemName = 'Print Service';

                $typeLabel = $isPrintOnly ? 'Print only' : ($itemIsCustom ? 'Custom Frame' : 'Ready-made');
                $typeClass = $isPrintOnly ? 'cst-ord-tag-service' : ($itemIsCustom ? 'cst-ord-tag-custom' : 'cst-ord-tag-readymade');

                // Sizes
                $sw = $itemIsReady ? ($item['width'] ?? null)        : ($item['custom_width']  ?? null);
                $sh = $itemIsReady ? ($item['height'] ?? null)       : ($item['custom_height'] ?? null);
            ?>
            <tr>
                <td>
                    <div class="cst-ord-dtls-item-cell">
                        <div class="cst-ord-dtls-item-thumb">
                            <?php if ($imgName): ?>
                            <img src="../uploads/designs/<?= htmlspecialchars($imgName) ?>" alt="">
                            <?php else: ?>
                            <i class="fas fa-border-all"></i>
                            <?php endif; ?>
                        </div>
                        <div class="cst-ord-dtls-item-info">
                            <strong><?= htmlspecialchars($itemName) ?></strong>
                            <?php if ($sw && $sh): ?>
                            <span class="item-spec">
                                <span class="item-spec-key">Frame Size</span>
                                <span class="item-spec-val"><?= $sw ?>" × <?= $sh ?>" (<?= (int)$sw * (int)$sh ?> total inches)</span>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($item['design_name'])): ?>
                            <span class="item-spec">
                                <span class="item-spec-key">Design</span>
                                <span class="item-spec-val"><?= htmlspecialchars($item['design_name']) ?></span>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($item['color_name'])): ?>
                            <span class="item-spec">
                                <span class="item-spec-key">Color</span>
                                <span class="item-spec-val"><?= htmlspecialchars($item['color_name']) ?></span>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($item['frame_type'])): ?>
                            <span class="item-spec">
                                <span class="item-spec-key">Frame Type</span>
                                <span class="item-spec-val"><?= htmlspecialchars($item['frame_type']) ?></span>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($item['mount_type'])): ?>
                            <span class="item-spec">
                                <span class="item-spec-key">Mount</span>
                                <span class="item-spec-val"><?= htmlspecialchars($item['mount_type']) ?></span>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($item['matboard_color_name'])): ?>
                            <span class="item-spec">
                                <span class="item-spec-key">Matboard</span>
                                <span class="item-spec-val"><?= htmlspecialchars($item['matboard_color_name']) ?></span>
                            </span>
                            <?php endif; ?>
                            <?php if ($itemHasPrint && !empty($item['dimension'])): ?>
                            <span class="item-spec">
                                <span class="item-spec-key">Print size</span>
                                <span class="item-spec-val"><?= htmlspecialchars($item['dimension']) ?></span>
                            </span>
                            <?php endif; ?>
                            <?php if ($itemHasPrint && !empty($item['paper_name'])): ?>
                            <span class="item-spec">
                                <span class="item-spec-key">Paper</span>
                                <span class="item-spec-val"><?= htmlspecialchars($item['paper_name']) ?></span>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="cst-ord-tag <?= $typeClass ?>">
                        <i class="fas fa-tag"></i> <?= $typeLabel ?>
                    </span>
                </td>
                <td class="cst-ord-dtls-qty">×<?= $item['quantity'] ?></td>
                <td class="cst-ord-dtls-subtotal">₱<?= number_format($item['sub_total'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="cst-ord-dtls-grand-total">
            <span class="cst-ord-dtls-grand-total-label">GRAND TOTAL:</span>
            <span class="cst-ord-dtls-grand-total-val">₱<?= number_format($total_price, 2) ?></span>
        </div>
    </div>

    <!-- ── Attached Documents ── -->
    <div class="cst-ord-dtls-docs-card">
        <div class="cst-ord-dtls-docs-header">
            <i class="fas fa-paperclip"></i>
            ATTACHED DOCUMENTS
        </div>
        <p class="cst-ord-dtls-docs-sub">Click any document to view full size.</p>
        <?php
        $docs = [];
        foreach ($proofs as $proof) {
            $docs[] = ['src' => '../'.$proof['payment_proof'], 'label' => 'Payment Proof'];
        }
        foreach ($items as $idx => $item) {
            if (!empty($item['image_path'])) {
                $docs[] = ['src' => '../'.$item['image_path'], 'label' => 'Wedding Photo (Item '.($idx+1).')'];
            }
        }
        ?>
        <?php if (empty($docs)): ?>
        <p class="cst-ord-dtls-no-docs">No documents attached to this order.</p>
        <?php else: ?>
        <div class="cst-ord-dtls-docs-grid">
            <?php foreach ($docs as $doc): ?>
            <div class="cst-ord-dtls-doc-thumb"
                 onclick="openDocViewer('<?= htmlspecialchars($doc['src']) ?>', '<?= htmlspecialchars($doc['label']) ?>')">
                <img src="<?= htmlspecialchars($doc['src']) ?>" alt="<?= htmlspecialchars($doc['label']) ?>">
                <div class="cst-ord-dtls-doc-thumb-label">
                    <i class="fas fa-up-right-and-down-left-from-center"></i>
                    <?= htmlspecialchars($doc['label']) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /.cst-ord-dtls-page -->

<!-- Proof Viewer Modal -->
<?php if (!empty($proofs)): ?>
<div id="cst-proof-modal" class="cst-upload-modal-overlay" style="display:none;">
    <div class="cst-upload-modal" style="max-width:520px;">
        <div class="cst-upload-modal-header">
            <span><i class="fas fa-receipt"></i> Proof of Payment</span>
            <button onclick="document.getElementById('cst-proof-modal').style.display='none'" class="cst-upload-modal-close"><i class="fas fa-times"></i></button>
        </div>
        <div class="cst-upload-modal-body">
            <?php foreach ($proofs as $idx => $proof):
                $vsClass = match($proof['verification_status']) {
                    'Verified' => 'cst-ord-dtls-vs-verified',
                    'Rejected' => 'cst-ord-dtls-vs-rejected',
                    default    => 'cst-ord-dtls-vs-pending',
                };
            ?>
            <div class="cst-ord-dtls-proof-viewer-item">
                <div class="cst-ord-dtls-proof-viewer-meta">
                    <span><i class="fas fa-receipt"></i> Receipt #<?= $idx + 1 ?></span>
                    <span class="cst-ord-dtls-vs-badge <?= $vsClass ?>"><?= $proof['verification_status'] ?></span>
                    <span class="cst-ord-dtls-proof-date"><?= date('M d, Y g:i A', strtotime($proof['upload_date'])) ?></span>
                </div>
                <img src="../<?= htmlspecialchars($proof['payment_proof']) ?>" alt="Receipt #<?= $idx + 1 ?>"
                     style="width:100%;border-radius:10px;margin-top:0.5rem;">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Document Viewer Modal -->
<div id="cst-doc-modal" class="cst-upload-modal-overlay" style="display:none;">
    <div class="cst-upload-modal" style="max-width:620px;">
        <div class="cst-upload-modal-header">
            <span id="cst-doc-modal-label">Document</span>
            <button onclick="document.getElementById('cst-doc-modal').style.display='none'" class="cst-upload-modal-close"><i class="fas fa-times"></i></button>
        </div>
        <div class="cst-upload-modal-body" style="text-align:center;">
            <img id="cst-doc-modal-img" src="" alt="" style="max-width:100%;border-radius:10px;">
        </div>
    </div>
</div>

<!-- Upload Receipt Modal -->
<?php if ($isGcash && $isPending): ?>
<div id="cst-upload-modal" class="cst-upload-modal-overlay" style="display:none;">
    <div class="cst-upload-modal">
        <div class="cst-upload-modal-header">
            <span><i class="fas fa-receipt"></i> Upload GCash Receipt</span>
            <button onclick="closeUploadModal()" class="cst-upload-modal-close"><i class="fas fa-times"></i></button>
        </div>
        <div class="cst-upload-modal-body">
            <p class="cst-upload-balance-note">
                Balance Due: <strong>₱<?= number_format($balance_due, 2) ?></strong>
            </p>
            <label class="cst-upload-label">Amount on Receipt (₱)</label>
            <input type="number" id="cst-upload-amount" class="cst-upload-input"
                   placeholder="e.g. 500.00" step="0.01" min="1" max="<?= $balance_due ?>">
            <label class="cst-upload-label" style="margin-top:1rem;">Receipt Image</label>
            <div class="cst-upload-dropzone" id="cst-upload-dropzone"
                 onclick="document.getElementById('cst-upload-file').click()">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Click to upload or drag & drop</p>
                <span>JPG, PNG, WEBP — max 10MB</span>
            </div>
            <input type="file" id="cst-upload-file" accept="image/jpeg,image/png,image/webp"
                   style="display:none;" onchange="previewFile(this)">
            <div id="cst-upload-preview" style="display:none;margin-top:0.75rem;">
                <img id="cst-upload-preview-img" src="" alt="Preview" style="max-width:100%;border-radius:10px;">
            </div>
        </div>
        <div class="cst-upload-modal-footer">
            <button onclick="closeUploadModal()" class="cst-upload-cancel-btn">Cancel</button>
            <button onclick="submitReceipt(<?= $order_id ?>, <?= $payment_id ?>)" class="cst-upload-submit-btn">
                <i class="fas fa-upload"></i> Submit Receipt
            </button>
        </div>
    </div>
</div>
<?php endif; ?>


<?php include __DIR__ . '/../includes/idx_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openProofViewer()  { document.getElementById('cst-proof-modal').style.display = 'flex'; }
function openUploadModal()  { document.getElementById('cst-upload-modal').style.display = 'flex'; }
function closeUploadModal() { document.getElementById('cst-upload-modal').style.display = 'none'; }

function openDocViewer(src, label) {
    document.getElementById('cst-doc-modal-img').src           = src;
    document.getElementById('cst-doc-modal-label').textContent = label;
    document.getElementById('cst-doc-modal').style.display     = 'flex';
}

function previewFile(input) {
    if (!input.files?.[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('cst-upload-preview-img').src  = e.target.result;
        document.getElementById('cst-upload-preview').style.display  = 'block';
        document.getElementById('cst-upload-dropzone').style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}

const dz = document.getElementById('cst-upload-dropzone');
if (dz) {
    dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('dragging'); });
    dz.addEventListener('dragleave', () => dz.classList.remove('dragging'));
    dz.addEventListener('drop', e => {
        e.preventDefault(); dz.classList.remove('dragging');
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer(); dt.items.add(file);
            const inp = document.getElementById('cst-upload-file');
            inp.files = dt.files; previewFile(inp);
        }
    });
}

async function submitReceipt(orderId, paymentId) {
    const amount = parseFloat(document.getElementById('cst-upload-amount').value);
    const file   = document.getElementById('cst-upload-file').files[0];
    if (!amount || amount <= 0) { Swal.fire('Missing Amount','Please enter the amount on your receipt.','warning'); return; }
    if (!file)                  { Swal.fire('No File','Please select your receipt image.','warning'); return; }
    const fd = new FormData();
    fd.append('order_id', orderId);
    fd.append('payment_id', paymentId);
    fd.append('uploaded_amount', amount);
    fd.append('receipt', file);
    const btn = document.querySelector('.cst-upload-submit-btn');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    try {
        const res  = await fetch('../process/upload_receipt.php', { method:'POST', body:fd });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon:'success', title:'Receipt Uploaded!', text:'We will verify your receipt shortly.', confirmButtonColor:'#0F473A' })
                .then(() => location.reload());
        } else {
            Swal.fire('Error', data.message || 'Something went wrong.', 'error');
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-upload"></i> Submit Receipt';
        }
    } catch(e) {
        Swal.fire('Error','Network error. Please try again.','error');
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-upload"></i> Submit Receipt';
    }
}

['cst-proof-modal','cst-doc-modal','cst-upload-modal'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', e => {
        if (e.target.id === id) e.target.style.display = 'none';
    });
});
</script>
</body>
</html>