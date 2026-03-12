<?php
// admin/admin_orders.php
session_start();
include __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Order/OrderService.php';

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

$service = new OrderService($conn);

$active_tab = strtoupper($_GET['status'] ?? 'PENDING');
$valid_tabs = ['PENDING','PROCESSING','READY_FOR_PICKUP','FOR_DELIVERY','COMPLETED','REJECTED','CANCELLED'];
if (!in_array($active_tab, $valid_tabs)) $active_tab = 'PENDING';

$filters = [
    'date'       => $_GET['date']       ?? null,
    'filterDate' => $_GET['filterDate'] ?? null,
];

$summary = $service->getDashboardSummary();

// Get counts per tab for the badges
$tab_counts = [];
foreach ($valid_tabs as $tab) {
    $tab_counts[$tab] = count($service->getOrdersForStatus($tab));
}

$orders = $service->getOrdersForStatus($active_tab, $filters);

$tab_labels = [
    'PENDING'          => 'New Orders',
    'PROCESSING'       => 'Processing',
    'READY_FOR_PICKUP' => 'Ready for Pick-up',
    'FOR_DELIVERY'     => 'For Delivery',
    'COMPLETED'        => 'Sold',
    'REJECTED'         => 'Rejected',
    'CANCELLED'        => 'Cancelled',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - RGA Frames Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php include __DIR__ . '/../includes/admin_header.php'; ?>

<div class="container-fluid px-4 admn-ordr-page">

    <h2 class="admn-ordr-title mb-0">Orders Dashboard</h2>
    <p class="admn-ordr-subtitle mb-4">Manage and track all customer frame orders</p>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="admn-ordr-summary-card admn-ordr-summary-new">
                <div class="card-body">
                    <p class="admn-ordr-summary-label mb-1">New Orders</p>
                    <div class="admn-ordr-summary-number"><?= $summary['new_orders'] ?? 0 ?></div>
                    <p class="admn-ordr-summary-sub">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="admn-ordr-summary-card admn-ordr-summary-completed">
                <div class="card-body">
                    <p class="admn-ordr-summary-label mb-1">Completed Today</p>
                    <div class="admn-ordr-summary-number"><?= $summary['completed_today'] ?? 0 ?></div>
                    <p class="admn-ordr-summary-sub">Orders completed</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="admn-ordr-summary-card admn-ordr-summary-progress">
                <div class="card-body">
                    <p class="admn-ordr-summary-label mb-1">In Progress</p>
                    <div class="admn-ordr-summary-number"><?= $summary['in_progress'] ?? 0 ?></div>
                    <p class="admn-ordr-summary-sub">Processing / Delivery</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="admn-ordr-summary-card admn-ordr-summary-issues">
                <div class="card-body">
                    <p class="admn-ordr-summary-label mb-1">Issues</p>
                    <div class="admn-ordr-summary-number"><?= $summary['issues'] ?? 0 ?></div>
                    <p class="admn-ordr-summary-sub">Rejected / Cancelled</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs + Orders List -->
    <div class="admn-ordr-tabs-wrap">
        <ul class="nav admn-ordr-tabs" id="orderTabs">
            <?php foreach ($tab_labels as $status => $label): 
                $count = $tab_counts[$status] ?? 0;
            ?>
            <li class="nav-item">
                <a class="nav-link admn-ordr-tab <?= $active_tab === $status ? 'active' : '' ?>"
                   href="?status=<?= $status ?>">
                    <?= $label ?>
                    <?php if ($count > 0): ?>
                        <span class="admn-ordr-tab-count"><?= $count ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="admn-ordr-list-wrap">

        <!-- Search & Filter Bar -->
        <div class="admn-ordr-search-bar">
            <div class="input-group admn-ordr-search">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="admn-ordr-search-input" class="form-control"
                       placeholder="Search by Order ID, Ref No., Customer Name or Phone...">
            </div>

            <?php if (!empty($filters['filterDate'])): ?>
                <span class="admn-ordr-filter-badge">
                    <i class="fas fa-calendar-day"></i>
                    <?= date('M d, Y', strtotime($filters['filterDate'])) ?>
                    <a href="?status=<?= $active_tab ?>">×</a>
                </span>
            <?php endif; ?>

            <button class="admn-ordr-filter-btn" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fas fa-sliders-h"></i> Filters
            </button>
        </div>

        <!-- Orders List -->
        <?php if (empty($orders)): ?>
            <div class="admn-ordr-empty">
                <i class="fas fa-inbox"></i>
                <h5 class="fw-bold text-muted">No orders found in this category.</h5>
                <p class="small">Orders placed by customers will appear here.</p>
            </div>
        <?php else: ?>
            <div id="admn-ordr-list">
            <?php foreach ($orders as $order):
                $st  = strtolower($order['order_status']);
                $stLabel = match($order['order_status']) {
                    'PENDING'          => 'Pending',
                    'PROCESSING'       => 'Processing',
                    'READY_FOR_PICKUP' => 'Ready for Pick-up',
                    'FOR_DELIVERY'     => 'For Delivery',
                    'COMPLETED'        => 'Completed',
                    'REJECTED'         => 'Rejected',
                    'CANCELLED'        => 'Cancelled',
                    default            => $order['order_status'],
                };
            ?>
            <div class="admn-ordr-card" data-search="<?= strtolower(
                $order['order_id'] . ' ' .
                ($order['order_reference_no'] ?? '') . ' ' .
                ($order['first_name'] ?? '') . ' ' .
                ($order['last_name'] ?? '') . ' ' .
                ($order['phone_number'] ?? '')
            ) ?>">
                <div class="admn-ordr-card-left">
                    <!-- Order ID + Ref + Status -->
                    <div class="admn-ordr-id-row">
                        <span class="admn-ordr-id">Order #<?= $order['order_id'] ?></span>
                        <span class="admn-ordr-ref-pill"><?= htmlspecialchars($order['order_reference_no'] ?? '—') ?></span>
                        <span class="admn-ordr-status-pill admn-ordr-status-<?= $st ?>"><?= $stLabel ?></span>
                    </div>

                    <!-- Customer Name -->
                    <div class="admn-ordr-customer-name">
                        <i class="fas fa-user"></i>
                        <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?>
                    </div>

                    <!-- Phone + Email -->
                    <div class="admn-ordr-contact-row">
                        <span><i class="fas fa-phone"></i><?= htmlspecialchars($order['phone_number'] ?? '—') ?></span>
                        <span><i class="fas fa-envelope"></i><?= htmlspecialchars($order['email'] ?? '—') ?></span>
                    </div>

                    <!-- Date -->
                    <div class="admn-ordr-date-row">
                        <i class="fas fa-clock"></i>
                        Placed <?= date('M d, Y | g:i A', strtotime($order['created_at'])) ?>
                    </div>
                </div>

                <div class="admn-ordr-card-right">
                    <a href="admin_order_details.php?id=<?= $order['order_id'] ?>"
                       class="admn-ordr-view-btn">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Filter Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="admn-ordr-filter-form">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Specific Date</label>
                        <input type="date" class="form-control" id="filterDate" name="filterDate"
                               value="<?= htmlspecialchars($filters['filterDate'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <?php foreach ($tab_labels as $val => $lbl): ?>
                                <option value="<?= $val ?>" <?= $active_tab === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark" id="admn-ordr-apply-filter">Apply Filters</button>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/admin_orders.js"></script>
</body>
</html>