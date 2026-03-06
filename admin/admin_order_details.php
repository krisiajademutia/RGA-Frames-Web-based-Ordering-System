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

// Status stepper config
$status_steps = [
    ['key' => 'PENDING',    'label' => 'Pending'],
    ['key' => 'PROCESSING', 'label' => 'Processing'],
    ['key' => 'PICKUP_DELIVERY', 'label' => 'Pick-up / Delivery'], // virtual step
    ['key' => 'COMPLETED',  'label' => 'Completed'],
];
$status_order = ['PENDING'=>0,'PROCESSING'=>1,'READY_FOR_PICKUP'=>2,'FOR_DELIVERY'=>2,'COMPLETED'=>3,'REJECTED'=>3,'CANCELLED'=>3];
$current_step = $status_order[$order['order_status']] ?? 0;

// Payment info
$amount_paid = (float)($order['amount_paid'] ?? 0);
$total_price = (float)($order['total_price'] ?? 0);
$balance_due = max(0, $total_price - $amount_paid);
$payment_status = $order['payment_status'] ?? null;
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
</head>
<body>

<?php include __DIR__ . '/../includes/admin_header.php'; ?>

<div class="container-fluid px-4 admn-ordr-dtls-page">

    <!-- Back -->
    <a href="admin_orders.php" class="admn-ordr-dtls-back">
        ← Back to Order List
    </a>

    <!-- Status Stepper -->
    <div class="admn-ordr-dtls-stepper-wrap">
        <?php foreach ($status_steps as $i => $step):
            $stepKey     = $step['key'];
            $stepIndex   = ($stepKey === 'PICKUP_DELIVERY') ? 2 : $status_order[$stepKey];
            $isDone      = $current_step > $stepIndex;
            $isActive    = $current_step === $stepIndex;
            $isCancelled = in_array($order['order_status'], ['REJECTED','CANCELLED']);
            // For step 4 (Completed), show number 4 if active/done
            $stepNum = $i + 1;
        ?>
            <div class="admn-ordr-dtls-step <?= $isDone || $isActive ? 'done' : '' ?> <?= $isCancelled && $i === 3 ? 'cancelled' : '' ?>">
                <div class="admn-ordr-dtls-step-circle">
                    <?php if ($isDone || ($isActive && $i < 3)): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        <?= $stepNum ?>
                    <?php endif; ?>
                </div>
                <span class="admn-ordr-dtls-step-label"><?= $step['label'] ?></span>
            </div>
            <?php if ($i < count($status_steps) - 1): ?>
                <div class="admn-ordr-dtls-step-line <?= $current_step > $stepIndex ? 'done' : '' ?>"></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Order ID Card -->
    <div class="admn-ordr-dtls-id-card">
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
                        <i class="fas fa-coins text-warning"></i> DELIVERY INFO
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
                    <i class="fas fa-user-circle text-warning"></i> PAYMENT & TOTALS
                </div>
                <div class="admn-ordr-dtls-card-body">
                    <div class="admn-ordr-dtls-row">
                        <span class="admn-ordr-dtls-label">Method</span>
                        <span class="admn-ordr-dtls-value">
                            <?php if (($order['payment_method'] ?? '') === 'GCASH'): ?>
                                <img src="../assets/images/gcash-logo.png" alt="GCash" style="height:20px;" onerror="this.style.display='none'; this.nextSibling.style.display='inline'">
                                <span style="display:none" class="badge bg-primary">GCash</span>
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
                    <div class="admn-ordr-dtls-row admn-ordr-dtls-balance-row">
                        <span class="admn-ordr-dtls-balance-label">Balance Due</span>
                        <span class="admn-ordr-dtls-balance-amount">₱<?= number_format($balance_due, 2) ?></span>
                    </div>

                    <?php if (!empty($order['payment_proof'])): ?>
                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <a href="../<?= htmlspecialchars($order['payment_proof']) ?>" target="_blank"
                           class="admn-ordr-dtls-proof-btn">
                            <i class="fas fa-eye"></i> View Proof of Payment
                        </a>
                        <a href="../<?= htmlspecialchars($order['payment_proof']) ?>" download
                           class="admn-ordr-dtls-download-link">
                            <i class="fas fa-download"></i> Download Proof of Payment
                        </a>
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

        <!-- Table header -->
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

                // Item name + badge config
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

                // Compute total_inch for frame size display
                $frameWidth  = $isReadyMade ? ($item['width'] ?? null)        : ($item['custom_width'] ?? null);
                $frameHeight = $isReadyMade ? ($item['height'] ?? null)       : ($item['custom_height'] ?? null);
                $frameTotalInch = ($frameWidth && $frameHeight) ? ($frameWidth + $frameHeight) * 2 : null;
            ?>
            <div class="admn-ordr-dtls-item-row">
                <!-- Item header: name / badge / qty / subtotal -->
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

                <!-- Specs -->
                <div class="admn-ordr-dtls-specs-grid">

                    <?php if ($isPrintOnly): ?>
                        <!-- PRINT ONLY specs -->
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Paper Size</span>
                            <span><?= $item['print_width'] ?? '—' ?>" × <?= $item['print_height'] ?? '—' ?>" (<?= $item['print_total_inch'] ?? '—' ?> total inches)</span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Paper Type (Print)</span>
                            <span><?= htmlspecialchars($item['paper_name'] ?? '—') ?></span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Pricing</span>
                            <span>Print | ₱<?= number_format($item['print_sub_total'] ?? 0, 2) ?></span>
                        </div>

                    <?php elseif ($isReadyMade): ?>
                        <!-- READY-MADE specs -->
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Frame Size</span>
                            <span><?= $frameWidth ?>" × <?= $frameHeight ?>"<?= $frameTotalInch ? ' (' . $frameTotalInch . ' total inches)' : '' ?></span>
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
                            <span><?= $item['print_width'] ?? '—' ?>" × <?= $item['print_height'] ?? '—' ?>" (<?= $item['print_total_inch'] ?? '—' ?> total inches)</span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Paper Type (Print)</span>
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
                        <!-- CUSTOM FRAME specs -->
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Frame Size</span>
                            <span><?= $frameWidth ?>" × <?= $frameHeight ?>"<?= $frameTotalInch ? ' (' . $frameTotalInch . ' total inches)' : '' ?></span>
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
                            <span>Matboard</span>
                            <span><?= htmlspecialchars($item['matboard_color_name'] ?? 'None') ?></span>
                        </div>
                        <?php if ($hasPrint): ?>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Paper Size</span>
                            <span><?= $item['print_width'] ?? '—' ?>" × <?= $item['print_height'] ?? '—' ?>" (<?= $item['print_total_inch'] ?? '—' ?> total inches)</span>
                        </div>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Paper Type (Print)</span>
                            <span><?= htmlspecialchars($item['paper_name'] ?? '—') ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="admn-ordr-dtls-spec-row">
                            <span>Pricing</span>
                            <span>
                                Frame | ₱<?= number_format($item['calculated_price'] ?? $item['base_price'] ?? 0, 2) ?><br>
                                Matboard | ₱<?= number_format($item['matboard_base_price'] ?? 0, 2) ?><br>
                                Mount | ₱<?= number_format($item['mount_extra'] ?? 0, 2) ?>
                                <?php if ($hasPrint): ?>
                                    <br>Print | ₱<?= number_format($item['print_sub_total'] ?? 0, 2) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <!-- Download Customer Image button (for items with print) -->
                    <?php if ($hasPrint || $isPrintOnly): ?>
                        <?php if (!empty($item['image_path'])): ?>
                        <div class="mt-3">
                            <a href="../<?= htmlspecialchars($item['image_path']) ?>" download
                               class="admn-ordr-dtls-img-download-btn">
                                <i class="fas fa-download"></i> Download Customer Image
                            </a>
                        </div>
                        <?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>