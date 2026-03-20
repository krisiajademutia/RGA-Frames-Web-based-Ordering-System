<?php
// customer/customer_orders.php
session_start();
include __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Order/CustomerOrderService.php';

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}

$customer_id = (int)$_SESSION['user_id'];
$repo = new CustomerOrderRepository($conn);
$itemRepo = new OrderItemRepository($conn);
$service = new CustomerOrderService($repo, $itemRepo);
//$service     = new CustomerOrderService($conn);

$activeTab = $_GET['status'] ?? 'ALL';
$search    = trim($_GET['search'] ?? '');
$allowed   = ['ALL','PENDING','PROCESSING','READY_FOR_PICKUP','FOR_DELIVERY','COMPLETED','CANCELLED','REJECTED'];
if (!in_array($activeTab, $allowed)) $activeTab = 'ALL';

$counts = $service->getTabCounts($customer_id);
$orders = $service->getOrders($customer_id, $activeTab, $search);

$tabs = [
    ['key'=>'ALL',              'label'=>'All Orders',        'count'=>$counts['all_orders']       ?? 0],
    ['key'=>'PENDING',          'label'=>'Pending',           'count'=>$counts['pending']           ?? 0],
    ['key'=>'PROCESSING',       'label'=>'Processing',        'count'=>$counts['processing']        ?? 0],
    ['key'=>'READY_FOR_PICKUP', 'label'=>'Ready for Pick-up', 'count'=>$counts['ready_for_pickup']  ?? 0],
    ['key'=>'FOR_DELIVERY',     'label'=>'Out for Delivery',  'count'=>$counts['for_delivery']      ?? 0],
    ['key'=>'COMPLETED',        'label'=>'Completed',         'count'=>$counts['completed']         ?? 0],
    ['key'=>'CANCELLED',        'label'=>'Cancelled',         'count'=>($counts['cancelled'] ?? 0) + ($counts['rejected'] ?? 0)],
];

$statusBadge = [
    'PENDING'          => ['Pending',           'cst-ord-badge-pending'],
    'PROCESSING'       => ['Processing',        'cst-ord-badge-processing'],
    'READY_FOR_PICKUP' => ['Ready for Pick-up', 'cst-ord-badge-pickup'],
    'FOR_DELIVERY'     => ['Out for Delivery',  'cst-ord-badge-delivery'],
    'COMPLETED'        => ['Completed',         'cst-ord-badge-completed'],
    'CANCELLED'        => ['Cancelled',         'cst-ord-badge-cancelled'],
    'REJECTED'         => ['Rejected',          'cst-ord-badge-rejected'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders — RGA Frames</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/customer_header.php'; ?>

<div class="cst-ord-page">
    <div class="cst-ord-page-inner">

        <h1 class="cst-ord-title">My Orders</h1>
        <p  class="cst-ord-subtitle">Track and view all your orders.</p>

        <div class="cst-ord-wrap">

            <!-- ── Tabs ── -->
            <div class="cst-ord-tabs-wrap">
                <div class="cst-ord-tabs">
                    <?php foreach ($tabs as $tab): ?>
                    <a href="customer_orders.php?status=<?= $tab['key'] ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                       class="cst-ord-tab <?= $activeTab === $tab['key'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($tab['label']) ?>
                        <?php if ($tab['count'] > 0): ?>
                        <span class="cst-ord-tab-count <?= $activeTab === $tab['key'] ? 'active' : '' ?>">
                            <?= $tab['count'] ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ── Search ── -->
            <div class="cst-ord-search-row">
                <form method="GET" action="customer_orders.php" class="cst-ord-search-form">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($activeTab) ?>">
                    <div class="cst-ord-search-wrap">
                        <i class="fas fa-search cst-ord-search-icon"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                               class="cst-ord-search-input"
                               placeholder="Search by Order ID or Reference No...">
                        <?php if ($search): ?>
                        <a href="customer_orders.php?status=<?= $activeTab ?>" class="cst-ord-search-clear">
                            <i class="fas fa-times"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
                <button type="button" class="cst-ord-filter-btn">
                    <i class="fas fa-sliders-h"></i> Filters
                </button>
            </div>

            <!-- ── Divider ── -->
            <div class="cst-ord-divider"></div>

            <!-- ── Orders ── -->
            <?php if (empty($orders)): ?>
            <div class="cst-ord-empty">
                <i class="fas fa-box-open cst-ord-empty-icon"></i>
                <p class="cst-ord-empty-text">No orders found.</p>
                <?php if ($search): ?>
                <a href="customer_orders.php?status=<?= $activeTab ?>" class="cst-ord-empty-clear">Clear search</a>
                <?php endif; ?>
            </div>

            <?php else: ?>
            <?php foreach ($orders as $o):
                $st = $o['order_status'];
                [$bl, $bc] = $statusBadge[$st] ?? [$st, 'cst-ord-badge-pending'];
                $isGcash = $o['payment_method'] === 'GCASH';
                $bal     = max(0, (float)$o['total_price'] - (float)$o['amount_paid']);
            ?>
            <div class="cst-ord-card">
                <div class="cst-ord-card-main">

                    <!-- ref + status badge -->
                    <div class="cst-ord-card-top">
                        <span class="cst-ord-card-ref-bold"><?= htmlspecialchars($o['order_reference_no']) ?></span>
                        <span class="cst-ord-badge <?= $bc ?>"><?= $bl ?></span>
                    </div>

                    <!-- tags -->
                    <div class="cst-ord-card-tags">
                        <!-- Delivery -->
                        <span class="cst-ord-tag cst-ord-tag-pickup">
                            <i class="fas fa-<?= $o['delivery_option'] === 'DELIVERY' ? 'truck' : 'store' ?>"></i>
                            <?= $o['delivery_option'] === 'DELIVERY' ? 'Delivery' : 'Pickup' ?>
                        </span>
                        <!-- Payment method -->
                        <?php if ($o['payment_method'] === 'CASH'): ?>
                        <span class="cst-ord-tag cst-ord-tag-cash">
                            <i class="fas fa-dollar-sign"></i> Cash
                        </span>
                        <?php else: ?>
                        <span class="cst-ord-tag cst-ord-tag-gcash">
                            <i class="fas fa-mobile-alt"></i> GCash
                        </span>
                        <?php endif; ?>
                        <!-- Service type -->
                        <?php if (!empty($o['has_frame'])): ?>
                        <span class="cst-ord-tag cst-ord-tag-service">
                            <i class="fas fa-border-all"></i>
                            <?= !empty($o['has_print']) ? 'Frame &amp; Print' : 'Frame only' ?>
                        </span>
                        <?php elseif (!empty($o['has_print'])): ?>
                        <span class="cst-ord-tag cst-ord-tag-service">
                            <i class="fas fa-print"></i> Print only
                        </span>
                        <?php endif; ?>
                        <!-- Category -->
                        <?php if (!empty($o['is_custom'])): ?>
                        <span class="cst-ord-tag cst-ord-tag-custom">
                            <i class="fas fa-paint-brush"></i> Custom
                        </span>
                        <?php elseif (!empty($o['has_frame'])): ?>
                        <span class="cst-ord-tag cst-ord-tag-readymade">
                            <i class="fas fa-tag"></i> Ready-made
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- item label -->
                    <?php if (!empty($o['item_label'])): ?>
                    <p class="cst-ord-card-item-label"><?= htmlspecialchars($o['item_label']) ?></p>
                    <?php endif; ?>

                    <!-- date -->
                    <p class="cst-ord-card-date">
                        <span class="cst-ord-card-date-dot"></span>
                        <?= date('M d, Y', strtotime($o['created_at'])) ?> | <?= date('g:i A', strtotime($o['created_at'])) ?>
                    </p>

                    <!-- balance alert -->
                    <?php if ($isGcash && $bal > 0 && $st === 'PENDING'): ?>
                    <span class="cst-ord-card-alert-tag">
                        <i class="fas fa-exclamation-circle"></i>
                        Balance Due: ₱<?= number_format($bal, 2) ?>
                    </span>
                    <?php endif; ?>

                </div><!-- /.cst-ord-card-main -->

                <div class="cst-ord-card-action">
                    <a href="customer_order_details.php?id=<?= $o['order_id'] ?>" class="cst-ord-view-btn">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </div>
            <div class="cst-ord-divider"></div>
            <?php endforeach; ?>
            <?php endif; ?>

        </div><!-- /.cst-ord-wrap -->
    </div><!-- /.cst-ord-page-inner -->
</div><!-- /.cst-ord-page -->


<?php include __DIR__ . '/../includes/idx_footer.php'; ?>
</body>
</html>