<?php
// admin/admin_order_details.php
session_start();
include __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Order/OrderService.php';

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid order ID.");
}

$order_id = (int)$_GET['id'];
$service  = new OrderService($conn);
$order    = $service->getFullOrderDetails($order_id);

if (!$order) {
    die("Order not found.");
}

$status_steps = [
    ['key' => 'PENDING',         'label' => 'Pending'],
    ['key' => 'PROCESSING',      'label' => 'Processing'],
    ['key' => 'PICKUP_DELIVERY', 'label' => 'Pick-up / Delivery'],
    ['key' => 'COMPLETED',       'label' => 'Completed'],
];
$status_order = ['PENDING'=>0,'PROCESSING'=>1,'READY_FOR_PICKUP'=>2,'FOR_DELIVERY'=>2,'COMPLETED'=>3,'REJECTED'=>3,'CANCELLED'=>3];
$current_step = $status_order[$order['order_status']] ?? 0;

$total_price    = (float)($order['total_price']  ?? 0);
$proofs         = $order['proofs'] ?? [];
$amount_paid = 0;
foreach ($proofs as $proof) {
    if (($proof['verification_status'] ?? '') === 'Verified') {
        $amount_paid += (float)$proof['uploaded_amount'];
    }
}
$balance_due    = max(0, $total_price - $amount_paid);
$payment_status = $order['payment_status'] ?? null;
$payment_id     = (int)($order['payment_id'] ?? 0);

$isPending   = $order['order_status'] === 'PENDING';
$isRejected  = in_array($order['order_status'], ['REJECTED','CANCELLED']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= $order_id ?> - RGA Frames</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php include __DIR__ . '/../includes/admin_header.php'; ?>

<div class="container-fluid px-4 admn-ordr-dtls-page">

    <!-- Back -->
    <a href="admin_orders.php?status=<?= $order['order_status'] ?>" class="admn-ordr-dtls-back">
        ← Back to Order List
    </a>

    <!-- Status Stepper -->
    <div class="admn-ordr-dtls-stepper-wrap">
        <?php foreach ($status_steps as $i => $step):
            $stepKey   = $step['key'];
            $stepIndex = ($stepKey === 'PICKUP_DELIVERY') ? 2 : ($status_order[$stepKey] ?? $i);
            $isFailed  = $isRejected && $i === 3;

            // When rejected/cancelled — don't mark any step as done, just show X on last
            $isDone   = !$isRejected && ($current_step > $stepIndex);
            $isActive = !$isRejected && ($current_step === $stepIndex);
            $stepNum  = $i + 1;
        ?>
            <div class="admn-ordr-dtls-step <?= $isDone ? 'done' : '' ?> <?= $isActive ? 'done' : '' ?> <?= $isFailed ? 'cancelled' : '' ?>">
                <div class="admn-ordr-dtls-step-circle">
                    <?php if ($isFailed): ?>
                        <i class="fas fa-times"></i>
                    <?php elseif ($isDone || ($isActive && $i < 3)): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        <?= $stepNum ?>
                    <?php endif; ?>
                </div>
                <span class="admn-ordr-dtls-step-label"><?= $step['label'] ?></span>
            </div>
            <?php if ($i < count($status_steps) - 1): ?>
                <div class="admn-ordr-dtls-step-line <?= (!$isRejected && $current_step > $stepIndex) ? 'done' : '' ?>"></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Order ID Card -->
    <div class="admn-ordr-dtls-id-card">
        <div class="admn-ordr-dtls-id-left">
            <div class="admn-ordr-dtls-id-header">ORDER</div>
            <div class="admn-ordr-dtls-id-number"># <?= $order_id ?></div>
            <div class="admn-ordr-dtls-id-meta">
                <span class="admn-ordr-dtls-ref-pill"><?= htmlspecialchars($order['order_reference_no'] ?? '—') ?></span>
                <span class="admn-ordr-dtls-placed">
                    <i class="fas fa-circle text-warning" style="font-size:0.5rem; vertical-align:middle;"></i>
                    Placed on <?= date('M d, Y | g:i A', strtotime($order['created_at'])) ?>
                </span>
            </div>
        </div>

        <?php if ($isPending): ?>
        <!-- Accept / Reject buttons (PENDING only) -->
        <div class="admn-ordr-dtls-id-actions">
            <button class="admn-ordr-dtls-accept-btn" id="btn-accept-order"
                    data-id="<?= $order_id ?>">
                <i class="fas fa-check"></i> Accept
            </button>
            <button class="admn-ordr-dtls-reject-btn" id="btn-reject-order"
                    data-id="<?= $order_id ?>">
                <i class="fas fa-times"></i> Reject
            </button>
        </div>
        <?php elseif (!$isRejected): ?>
        <!-- Status change button for active orders -->
        <div class="admn-ordr-dtls-id-actions">
            <?php
            $nextStatus = match($order['order_status']) {
                'PROCESSING'       => ['status' => 'READY_FOR_PICKUP', 'label' => 'Mark Ready for Pick-up', 'icon' => 'fa-store'],
                'READY_FOR_PICKUP' => ['status' => 'COMPLETED',        'label' => 'Mark as Completed',      'icon' => 'fa-check-double'],
                'FOR_DELIVERY'     => ['status' => 'COMPLETED',        'label' => 'Mark as Completed',      'icon' => 'fa-check-double'],
                default            => null,
            };
            if ($nextStatus):
            ?>
            <button class="admn-ordr-dtls-next-btn" id="btn-next-status"
                    data-id="<?= $order_id ?>"
                    data-status="<?= $nextStatus['status'] ?>">
                <i class="fas <?= $nextStatus['icon'] ?>"></i> <?= $nextStatus['label'] ?>
            </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Customer + Payment Row -->
    <div class="row g-4 mb-4">

        <!-- Customer Details -->
        <div class="col-lg-6">
            <div class="admn-ordr-dtls-card admn-ordr-dtls-card-blue">
                <div class="admn-ordr-dtls-card-header">
                    <i class="fas fa-user-circle text-warning"></i> CUSTOMER DETAILS
                </div>
                <div class="admn-ordr-dtls-card-body">
                    <div class="admn-ordr-dtls-row">
                        <span class="admn-ordr-dtls-label">Full Name</span>
                        <span class="admn-ordr-dtls-value"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></span>
                    </div>
                    <div class="admn-ordr-dtls-row">
                        <span class="admn-ordr-dtls-label">Username</span>
                        <span class="admn-ordr-dtls-value">@<?= htmlspecialchars($order['username'] ?? '—') ?></span>
                    </div>
                    <div class="admn-ordr-dtls-row">
                        <span class="admn-ordr-dtls-label">Phone</span>
                        <span class="admn-ordr-dtls-value"><?= htmlspecialchars($order['phone_number'] ?? '—') ?></span>
                    </div>
                    <div class="admn-ordr-dtls-row">
                        <span class="admn-ordr-dtls-label">Email</span>
                        <span class="admn-ordr-dtls-value"><?= htmlspecialchars($order['email'] ?? '—') ?></span>
                    </div>

                    <div class="admn-ordr-dtls-section-divider">
                        <i class="fas fa-truck text-warning"></i> DELIVERY INFO
                    </div>
                    <div class="admn-ordr-dtls-row">
                        <span class="admn-ordr-dtls-label">Option</span>
                        <span class="admn-ordr-dtls-value">
                            <?php if (($order['delivery_option'] ?? '') === 'DELIVERY'): ?>
                                <span class="admn-ordr-dtls-option-pill delivery">
                                    <i class="fas fa-truck"></i> Delivery
                                </span>
                            <?php else: ?>
                                <span class="admn-ordr-dtls-option-pill pickup">
                                    <i class="fas fa-store"></i> Pick-up
                                </span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if (($order['delivery_option'] ?? '') === 'DELIVERY' && !empty($order['delivery_address'])): ?>
                    <div class="admn-ordr-dtls-row">
                        <span class="admn-ordr-dtls-label">Address</span>
                        <span class="admn-ordr-dtls-value"><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Payment & Totals -->
        <div class="col-lg-6">
            <div class="admn-ordr-dtls-card admn-ordr-dtls-card-green">
                <div class="admn-ordr-dtls-card-header">
                    <i class="fas fa-receipt text-warning"></i> PAYMENT & TOTALS
                </div>
                <div class="admn-ordr-dtls-card-body">
                    <div class="admn-ordr-dtls-row">
                        <span class="admn-ordr-dtls-label">Method</span>
                            <span class="admn-ordr-dtls-value">
                                <?php if (strtoupper($order['payment_method'] ?? '') === 'GCASH'): ?>
                                    <span class="badge bg-primary" style="font-size: 14px; padding: 5px 10px;"><i class="fas fa-mobile-alt"></i> GCash</span>
                                <?php else: ?>
                                    <span class="admn-ordr-dtls-cash-badge"><i class="fas fa-money-bill-wave"></i> Cash</span>
                                <?php endif; ?>
                        </span>
                    </div>
                    <div class="admn-ordr-dtls-row">
                        <span class="admn-ordr-dtls-label">Status</span>
                        <span class="admn-ordr-dtls-value">
                            <?php
                            $ps = $payment_status ?? 'PENDING';
                            // If order is rejected/cancelled, override payment display
                            if ($isRejected) {
                                $psClass = 'admn-ordr-dtls-pay-pending';
                                $psLabel = '— Order ' . ucfirst(strtolower($order['order_status']));
                            } else {
                                $psClass = match($ps) {
                                    'FULL'    => 'admn-ordr-dtls-pay-full',
                                    'PARTIAL' => 'admn-ordr-dtls-pay-partial',
                                    default   => 'admn-ordr-dtls-pay-pending',
                                };
                                $psLabel = match($ps) {
                                    'FULL'    => 'Fully Paid',
                                    'PARTIAL' => '⚠ Partial Payment',
                                    default   => 'Pending',
                                };
                            }
                            ?>
                            <span class="admn-ordr-dtls-pay-badge <?= $psClass ?>"><?= $psLabel ?></span>
                        </span>
                    </div>

                    <hr class="admn-ordr-dtls-divider-line">

                    <div class="admn-ordr-dtls-row admn-ordr-dtls-total-row">
                        <span class="admn-ordr-dtls-label">Grand Total</span>
                        <span class="admn-ordr-dtls-total-amount">₱<?= number_format($total_price, 2) ?></span>
                    </div>
                    <?php if ($amount_paid > 0): ?>
                    <div class="admn-ordr-dtls-row">
                        <span class="admn-ordr-dtls-label">Downpayment</span>
                        <span class="admn-ordr-dtls-downpayment">- ₱<?= number_format($amount_paid, 2) ?></span>
                    </div>
                    <hr class="admn-ordr-dtls-divider-line">
                    <?php endif; ?>
                    <div class="admn-ordr-dtls-row admn-ordr-dtls-balance-row" style="align-items: center;">
                        <span class="admn-ordr-dtls-balance-label">Balance Due</span>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="admn-ordr-dtls-balance-amount">
                                <?= $isRejected ? '—' : '₱' . number_format($balance_due, 2) ?>
                            </span>
                            <?php if ($balance_due > 0 && !$isRejected && $payment_id > 0): ?>
                            <button type="button" onclick="logCashPayment(<?= $payment_id ?>, <?= $balance_due ?>)" style="background: #0f3d33; color: white; border: none; padding: 5px 12px; border-radius: 5px; font-size: 13px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <i class="fas fa-coins"></i> Add Cash
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Payment Proof Uploads -->
                    <?php if (!empty($proofs)): ?>
                    <div class="admn-ordr-dtls-proofs-wrap mt-3">
                        <div class="admn-ordr-dtls-proofs-label">
                            <i class="fas fa-receipt text-warning"></i> Payment Receipts
                            <span class="admn-ordr-dtls-proofs-count"><?= count($proofs) ?></span>
                        </div>
                        <?php foreach ($proofs as $i => $proof):
                            $vsClass = match($proof['verification_status']) {
                                'Verified' => 'admn-ordr-dtls-vs-verified',
                                'Rejected' => 'admn-ordr-dtls-vs-rejected',
                                default    => 'admn-ordr-dtls-vs-pending',
                            };
                        ?>
                        <div class="admn-ordr-dtls-proof-item" id="proof-item-<?= $proof['upload_id'] ?>">
                            <div class="admn-ordr-dtls-proof-item-left">
                                <?php if ($proof['payment_proof'] === 'Admin: Walk-in Cash Payment'): ?>
                                    <div class="admn-ordr-dtls-proof-thumb" style="display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #e2eaec; border: 2px dashed #0f3d33; color: #0f3d33; padding: 10px; cursor: default;">
                                        <i class="fas fa-money-bill-wave" style="font-size: 24px; margin-bottom: 5px;"></i>
                                        <span style="font-size: 11px; font-weight: bold; text-align: center;">CASH<br>RECEIVED</span>
                                    </div>
                                <?php else: ?>
                                    <img src="../<?= htmlspecialchars($proof['payment_proof']) ?>"
                                         alt="Receipt <?= $i+1 ?>"
                                         class="admn-ordr-dtls-proof-thumb"
                                         data-fullsrc="../<?= htmlspecialchars($proof['payment_proof']) ?>"
                                         data-label="Receipt #<?= $i+1 ?> — ₱<?= number_format($proof['uploaded_amount'], 2) ?>"
                                         onclick="openImageViewer(this)">
                                <?php endif; ?>
                            </div>
                            <div class="admn-ordr-dtls-proof-item-right">
                                <div class="admn-ordr-dtls-proof-amount">
                                    ₱<?= number_format($proof['uploaded_amount'], 2) ?>
                                </div>
                                <div class="admn-ordr-dtls-proof-date">
                                    <i class="fas fa-clock"></i>
                                    <?= date('M d, Y g:i A', strtotime($proof['upload_date'])) ?>
                                </div>
                                <span class="admn-ordr-dtls-vs-badge <?= $vsClass ?>">
                                    <?= $proof['verification_status'] ?>
                                </span>
                                <?php if ($proof['verification_status'] === 'Pending Verification' || $proof['verification_status'] === 'Pending'): ?>
                                <div class="admn-ordr-dtls-proof-actions mt-2">
                                    <button class="admn-ordr-dtls-verify-btn"
                                            onclick="verifyProof(<?= $proof['upload_id'] ?>, <?= $payment_id ?>)">
                                        <i class="fas fa-check"></i> Verify
                                    </button>
                                    <button class="admn-ordr-dtls-reject-proof-btn"
                                            onclick="rejectProof(<?= $proof['upload_id'] ?>)">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </div>
                                <?php endif; ?>
                                <?php if ($proof['payment_proof'] !== 'Admin: Walk-in Cash Payment'): ?>
                                <div class="admn-ordr-dtls-proof-dl mt-1">
                                    <a href="download_image.php?path=<?= urlencode($proof['payment_proof']) ?>&name=receipt_<?= $order_id ?>_<?= $i+1 ?>"
                                       class="admn-ordr-dtls-download-link">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php elseif ($order['payment_method'] === 'GCASH'): ?>
                    <div class="admn-ordr-dtls-no-proof mt-3">
                        <i class="fas fa-hourglass-half"></i> Awaiting payment receipt from customer.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Table -->
    <div class="admn-ordr-dtls-items-wrap">
        <div class="admn-ordr-dtls-items-header">
            <i class="fas fa-cubes text-warning"></i> ORDER DETAILS
        </div>

        <div class="admn-ordr-dtls-table-head">
            <div class="admn-ordr-dtls-col-item">ITEM DETAILS</div>
            <div class="admn-ordr-dtls-col-type">TYPE</div>
            <div class="admn-ordr-dtls-col-qty">QUANTITY</div>
            <div class="admn-ordr-dtls-col-sub">SUBTOTAL</div>
        </div>

        <?php if (empty($order['items'])): ?>
            <div class="p-4 text-center text-muted">No items found for this order.</div>
        <?php else: ?>
            <?php foreach ($order['items'] as $item):
                $frameCategory = $item['frame_category'] ?? null;
                $serviceType   = $item['service_type']   ?? null;
                $isReadyMade   = $frameCategory === 'READY_MADE';
                $isCustom      = $frameCategory === 'CUSTOM';
                $hasPrint      = $serviceType === 'FRAME&PRINT';
                $isPrintOnly   = empty($item['r_product_id']) && empty($item['c_product_id']);

                if ($isPrintOnly) {
                    $itemName   = 'Printing Service';
                    $itemIcon   = 'fas fa-print';
                    $itemColor  = 'admn-ordr-dtls-item-print';
                    $badgeClass = 'admn-ordr-dtls-badge-print';
                    $badgeLabel = '<i class="fas fa-print"></i> Print Only';
                } elseif ($isReadyMade && $hasPrint) {
                    $itemName   = htmlspecialchars($item['ready_name'] ?? 'Ready-made Frame') . ' + Print';
                    $itemIcon   = 'fas fa-images';
                    $itemColor  = 'admn-ordr-dtls-item-readymade';
                    $badgeClass = 'admn-ordr-dtls-badge-frameprint';
                    $badgeLabel = '<i class="fas fa-border-all"></i> Frame & Print';
                } elseif ($isReadyMade) {
                    $itemName   = htmlspecialchars($item['ready_name'] ?? 'Ready-made Frame');
                    $itemIcon   = 'fas fa-border-all';
                    $itemColor  = 'admn-ordr-dtls-item-readymade';
                    $badgeClass = 'admn-ordr-dtls-badge-readymade';
                    $badgeLabel = '<i class="fas fa-tag"></i> Ready-made';
                } elseif ($isCustom && $hasPrint) {
                    $itemName   = 'Custom Frame + Print';
                    $itemIcon   = 'fas fa-pencil-ruler';
                    $itemColor  = 'admn-ordr-dtls-item-custom';
                    $badgeClass = 'admn-ordr-dtls-badge-frameprint';
                    $badgeLabel = '<i class="fas fa-border-all"></i> Frame & Print';
                } else {
                    $itemName   = 'Custom Frame';
                    $itemIcon   = 'fas fa-pencil-ruler';
                    $itemColor  = 'admn-ordr-dtls-item-custom';
                    $badgeClass = 'admn-ordr-dtls-badge-custom';
                    $badgeLabel = '<i class="fas fa-magic"></i> Custom';
                }

                $frameWidth     = $isReadyMade ? ($item['width'] ?? null)        : ($item['custom_width'] ?? null);
                $frameHeight    = $isReadyMade ? ($item['height'] ?? null)       : ($item['custom_height'] ?? null);
                $frameTotalInch = ($frameWidth && $frameHeight) ? ($frameWidth + $frameHeight) : null;
            ?>
            <div class="admn-ordr-dtls-item-row">
                <div class="admn-ordr-dtls-item-top">
                    <div class="admn-ordr-dtls-col-item">
                        <span class="admn-ordr-dtls-item-bullet">•</span>
                        <span class="admn-ordr-dtls-item-name <?= $itemColor ?>">
                            <i class="<?= $itemIcon ?>"></i> <?= $itemName ?>
                        </span>
                    </div>
                    <div class="admn-ordr-dtls-col-type">
                        <span class="admn-ordr-dtls-type-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                    </div>
                    <div class="admn-ordr-dtls-col-qty">x<?= $item['quantity'] ?? 1 ?></div>
                    <div class="admn-ordr-dtls-col-sub">₱<?= number_format($item['sub_total'] ?? 0, 2) ?></div>
                </div>

                <div class="admn-ordr-dtls-specs-grid">

                    <?php if ($isPrintOnly): ?>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Paper Size</span>
                            <span><?= $item['print_width'] ?? '—' ?>" × <?= $item['print_height'] ?? '—' ?>"</span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Paper Type</span>
                            <span><?= htmlspecialchars($item['paper_name'] ?? '—') ?></span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Pricing</span>
                            <span>Print | ₱<?= number_format($item['print_sub_total'] ?? 0, 2) ?></span>
                        </div>

                    <?php elseif ($isReadyMade): ?>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Frame Size</span>
                            <span><?= $frameWidth ?>" × <?= $frameHeight ?>"<?= $frameTotalInch ? ' (' . $frameTotalInch . ' united inches)' : '' ?></span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Design</span>
                            <span><?= htmlspecialchars($item['design_name'] ?? '—') ?></span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Color</span>
                            <span><?= htmlspecialchars($item['color_name'] ?? '—') ?></span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Frame Type</span>
                            <span><?= htmlspecialchars($item['type_name'] ?? '—') ?></span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Mount</span>
                            <span><?= htmlspecialchars($item['mount_name'] ?? '—') ?></span>
                        </div>
                        <?php if ($hasPrint): ?>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Paper Size</span>
                            <span><?= $item['print_width'] ?? '—' ?>" × <?= $item['print_height'] ?? '—' ?>"</span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Paper Type</span>
                            <span><?= htmlspecialchars($item['paper_name'] ?? '—') ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Pricing</span>
                            <span>
                                Frame | ₱<?= number_format($item['product_price'] ?? $item['base_price'] ?? 0, 2) ?><br>
                                Mount | ₱<?= number_format($item['mount_extra'] ?? 0, 2) ?>
                                <?php if ($hasPrint): ?>
                                    <br>Print | ₱<?= number_format($item['print_sub_total'] ?? 0, 2) ?>
                                <?php endif; ?>
                            </span>
                        </div>

                    <?php else: ?>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Frame Size</span>
                            <span><?= $frameWidth ?>" × <?= $frameHeight ?>"<?= $frameTotalInch ? ' (' . $frameTotalInch . ' united inches)' : '' ?></span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Design</span>
                            <span><?= htmlspecialchars($item['design_name'] ?? '—') ?></span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Color</span>
                            <span><?= htmlspecialchars($item['color_name'] ?? '—') ?></span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Frame Type</span>
                            <span><?= htmlspecialchars($item['type_name'] ?? '—') ?></span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Mount</span>
                            <span><?= htmlspecialchars($item['mount_name'] ?? '—') ?></span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Matboard (Primary)</span>
                            <span><?= htmlspecialchars($item['matboard_color_name'] ?? 'None') ?></span>
                        </div>
                        <?php if (isset($item['secondary_matboard_color_name']) && $item['secondary_matboard_color_name'] !== ''): ?>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Matboard (Secondary)</span>
                            <span><?= htmlspecialchars($item['secondary_matboard_color_name']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($hasPrint): ?>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Paper Size</span>
                            <span><?= $item['print_width'] ?? '—' ?>" × <?= $item['print_height'] ?? '—' ?>"</span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Paper Type</span>
                            <span><?= htmlspecialchars($item['paper_name'] ?? '—') ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Pricing</span>
                            <span>
                                Frame | ₱<?= number_format($item['calculated_price'] ?? 0, 2) ?><br>
                                <?php
                                $hasPrimaryMat   = !empty($item['matboard_color_name']);
                                $hasSecondaryMat = !empty($item['secondary_matboard_color_name']);
                                $matCharge       = ($hasPrimaryMat && $hasSecondaryMat)
                                    ? (float)($item['matboard_base_price'] ?? 0)
                                    : 0;
                                ?>
                                Matboard | ₱<?= number_format($matCharge, 2) ?><?= ($hasPrimaryMat && $hasSecondaryMat) ? ' (double-matting)' : '' ?><br>
                                Mount | ₱<?= number_format($item['mount_extra'] ?? 0, 2) ?>
                                <?php if ($hasPrint): ?>
                                    <br>Print | ₱<?= number_format($item['print_sub_total'] ?? 0, 2) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if (($hasPrint || $isPrintOnly) && !empty($item['image_path'])): ?>
                    <div class="admn-ordr-dtls-img-preview-wrap mt-3">
                        <img src="../<?= htmlspecialchars($item['image_path']) ?>"
                             alt="Customer Image"
                             class="admn-ordr-dtls-img-thumb"
                             data-fullsrc="../<?= htmlspecialchars($item['image_path']) ?>"
                             data-label="Customer Print Image"
                             onclick="openImageViewer(this)">
                        <div class="admn-ordr-dtls-img-actions">
                            <button class="admn-ordr-dtls-proof-btn"
                                    onclick="openImageViewer(this.closest('.admn-ordr-dtls-img-preview-wrap').querySelector('img'))">
                                <i class="fas fa-expand"></i> View Full
                            </button>
                            <a href="download_image.php?path=<?= urlencode($item['image_path']) ?>&name=print_image_order_<?= $order_id ?>"
                               class="admn-ordr-dtls-img-download-btn">
                                <i class="fas fa-download"></i> Download Original
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Grand Total Footer -->
        <div class="admn-ordr-dtls-grand-total">
            <span>GRAND TOTAL:</span>
            <span class="admn-ordr-dtls-grand-amount">₱<?= number_format($total_price, 2) ?></span>
        </div>
    </div>

</div>

<script>
// Accept Order
document.getElementById('btn-accept-order')?.addEventListener('click', function () {
    const orderId = this.dataset.id;
    Swal.fire({
        title: 'Accept this order?',
        text: 'The order will be moved to Processing.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0F473A',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: '<i class="fas fa-check"></i> Yes, Accept',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            updateOrderStatus(orderId, 'PROCESSING');
        }
    });
});

// Reject Order
document.getElementById('btn-reject-order')?.addEventListener('click', function () {
    const orderId = this.dataset.id;
    Swal.fire({
        title: 'Reject this order?',
        text: 'This action cannot be undone. The order will be marked as Rejected.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: '<i class="fas fa-times"></i> Yes, Reject',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            updateOrderStatus(orderId, 'REJECTED');
        }
    });
});

// Next Status Button
document.getElementById('btn-next-status')?.addEventListener('click', function () {
    const orderId  = this.dataset.id;
    const newStatus = this.dataset.status;
    const label    = this.textContent.trim();
    Swal.fire({
        title: 'Update order status?',
        html: `Move order to <strong>${newStatus.replace('_', ' ')}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0F473A',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: `<i class="fas fa-check"></i> Confirm`,
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            updateOrderStatus(orderId, newStatus);
        }
    });
});

function verifyProof(uploadId, paymentId) {
    Swal.fire({
        title: 'Verify this receipt?',
        text: 'This will mark the receipt as verified and update the payment status.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0F473A',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: '<i class="fas fa-check"></i> Yes, Verify',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../process/verify_payment_proof.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `upload_id=${uploadId}&payment_id=${paymentId}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Verified!', timer: 1500, showConfirmButton: false,
                        confirmButtonColor: '#0F473A' })
                    .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Something went wrong.', 'error');
                }
            });
        }
    });
}

function rejectProof(uploadId) {
    Swal.fire({
        title: 'Reject this receipt?',
        text: 'This will mark the receipt as rejected.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: '<i class="fas fa-times"></i> Yes, Reject',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../process/verify_payment_proof.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `upload_id=${uploadId}&action=reject`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Rejected!', timer: 1500, showConfirmButton: false })
                    .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Something went wrong.', 'error');
                }
            });
        }
    });
}

function updateOrderStatus(orderId, newStatus) {
    fetch('../process/update_order_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `order_id=${orderId}&new_status=${newStatus}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Status Updated!',
                text: `Order has been moved to ${newStatus.replace(/_/g, ' ')}.`,
                confirmButtonColor: '#0F473A',
                timer: 1800,
                showConfirmButton: false,
            }).then(() => {
                window.location.href = `admin_order_details.php?id=${orderId}`;
            });
        } else {
            Swal.fire('Error', data.message || 'Something went wrong.', 'error');
        }
    })
    .catch(() => Swal.fire('Error', 'Request failed.', 'error'));
}


function openImageViewer(imgEl) {
    const src   = imgEl.dataset.fullsrc || imgEl.src;
    const label = imgEl.dataset.label   || 'Image';
    const rawPath = imgEl.dataset.fullsrc
        ? imgEl.dataset.fullsrc.replace(/^.*admin\//, '').replace(/^\.\.\//, '')
        : '';

    document.getElementById('admn-img-viewer-img').src         = src;
    document.getElementById('admn-img-viewer-label').textContent = label;
    document.getElementById('admn-img-viewer-size').textContent  = '';

    const dlHref = 'download_image.php?path=' + encodeURIComponent(rawPath)
                 + '&name=' + encodeURIComponent(label.replace(/\s+/g, '_').toLowerCase());
    document.getElementById('admn-img-viewer-download').href = dlHref;

    document.getElementById('admn-img-viewer').style.display = 'flex';
    document.body.style.overflow = 'hidden';

    const imgTag = document.getElementById('admn-img-viewer-img');
    imgTag.onload = function () {
        document.getElementById('admn-img-viewer-size').textContent =
            imgTag.naturalWidth + ' x ' + imgTag.naturalHeight + ' px';
    };
}

function closeImageViewer() {
    document.getElementById('admn-img-viewer').style.display = 'none';
    document.getElementById('admn-img-viewer-img').src = '';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeImageViewer();
});

function logCashPayment(paymentId, maxAmount) {
    Swal.fire({
        title: '<span style="font-size: 22px;">Log Cash Payment 💵</span>',
        html: `
            <p style="font-size: 14px; color: #555; margin-bottom: 15px;">Enter the exact cash amount received from the customer.</p>
            <div style="text-align: left; margin-bottom: 5px; font-size: 14px; font-weight: bold; color: #0f3d33;">Amount Received (₱):</div>
            <input type="number" id="cash-amount" class="swal2-input" style="margin: 0; width: 100%; box-sizing: border-box;" min="1" max="${maxAmount}" step="0.01" placeholder="e.g. 500">
        `,
        iconColor: '#0f3d33',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Save Payment',
        confirmButtonColor: '#0f3d33',
        cancelButtonColor: '#9ca3af',
        preConfirm: () => {
            const amount = document.getElementById('cash-amount').value;
            if (!amount || amount <= 0 || isNaN(amount)) {
                Swal.showValidationMessage('Please enter a valid amount');
                return false;
            }
            if (amount > maxAmount) {
                Swal.showValidationMessage('Amount cannot exceed the balance due (₱' + maxAmount.toFixed(2) + ')');
                return false;
            }
            return amount;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const amount = result.value;
            fetch('../process/admin_log_cash_payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `payment_id=${paymentId}&amount=${amount}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Payment Saved! 🎉',
                        text: 'The cash payment was successfully recorded.',
                        icon: 'success',
                        confirmButtonColor: '#0f3d33'
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Something went wrong.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Request failed.', 'error'));
        }
    });
}
</script>


<!-- Image Viewer Lightbox -->
<div id="admn-img-viewer" style="display:none;">
    <div id="admn-img-viewer-backdrop" onclick="closeImageViewer()"></div>
    <div id="admn-img-viewer-content">
        <button id="admn-img-viewer-close" onclick="closeImageViewer()">
            <i class="fas fa-times"></i>
        </button>
        <div id="admn-img-viewer-label"></div>
        <div id="admn-img-viewer-imgwrap">
            <img id="admn-img-viewer-img" src="" alt="">
        </div>
        <div id="admn-img-viewer-footer">
            <a id="admn-img-viewer-download" href="#" class="admn-img-viewer-dl-btn">
                <i class="fas fa-download"></i> Download Original
            </a>
            <span id="admn-img-viewer-size" class="admn-img-viewer-size-info"></span>
        </div>
    </div>
</div>

</body>
</html>