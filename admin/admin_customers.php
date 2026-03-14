<?php
// admin/admin_customers.php
session_start();
include __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

// Search & filter
$search = trim($_GET['search'] ?? '');
$filter = $_GET['type'] ?? 'ALL';

$sql = "
    SELECT
        c.customer_id, c.first_name, c.last_name, c.username,
        c.email, c.phone_number, c.customer_type, c.created_at,
        COUNT(o.order_id) AS total_orders
    FROM tbl_customer c
    LEFT JOIN tbl_orders o ON c.customer_id = o.customer_id
    WHERE 1=1
";
$params = [];
$types  = '';

if (!empty($search)) {
    $sql    .= " AND (c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone_number LIKE ?)";
    $like    = "%$search%";
    $params  = array_merge($params, [$like, $like, $like, $like]);
    $types  .= 'ssss';
}
if ($filter !== 'ALL') {
    $sql    .= " AND c.customer_type = ?";
    $params[] = $filter;
    $types   .= 's';
}

$sql .= " GROUP BY c.customer_id ORDER BY c.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Counts
$totalStmt = $conn->query("SELECT customer_type, COUNT(*) as cnt FROM tbl_customer GROUP BY customer_type");
$counts    = ['REGULAR' => 0, 'PHOTOGRAPHER' => 0];
while ($row = $totalStmt->fetch_assoc()) {
    $counts[$row['customer_type']] = (int)$row['cnt'];
}
$counts['ALL'] = array_sum($counts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers — RGA Frames Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include __DIR__ . '/../includes/admin_header.php'; ?>

<div class="admn-cust-wrapper">

    <!-- Page Header -->
    <div class="admn-cust-page-header">
        <h1 class="admn-cust-page-title">Customers</h1>
        <p class="admn-cust-page-subtitle">Manage customer accounts and photographer status</p>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="admn-ordr-summary-card admn-ordr-summary-new">
                <div class="card-body">
                    <p class="admn-ordr-summary-label mb-1">Total Customers</p>
                    <div class="admn-ordr-summary-number"><?= $counts['ALL'] ?></div>
                    <p class="admn-ordr-summary-sub">All accounts</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="admn-ordr-summary-card admn-ordr-summary-completed">
                <div class="card-body">
                    <p class="admn-ordr-summary-label mb-1">Regular</p>
                    <div class="admn-ordr-summary-number"><?= $counts['REGULAR'] ?></div>
                    <p class="admn-ordr-summary-sub">Standard customers</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="admn-ordr-summary-card admn-ordr-summary-progress">
                <div class="card-body">
                    <p class="admn-ordr-summary-label mb-1">Photographers</p>
                    <div class="admn-ordr-summary-number"><?= $counts['PHOTOGRAPHER'] ?></div>
                    <p class="admn-ordr-summary-sub">Discounted accounts</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="admn-ordr-tabs-wrap">
        <ul class="nav admn-ordr-tabs">
            <?php foreach ([
                'ALL'          => 'All Customers',
                'REGULAR'      => 'Regular',
                'PHOTOGRAPHER' => 'Photographers',
            ] as $key => $label): ?>
            <li class="nav-item">
                <a href="admin_customers.php?type=<?= $key ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                   class="nav-link admn-ordr-tab <?= $filter === $key ? 'active' : '' ?>">
                    <?= $label ?>
                    <span class="admn-ordr-tab-count"><?= $counts[$key] ?? 0 ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Table Card -->
    <div class="admn-cust-card">

        <!-- Search bar -->
        <div class="admn-ordr-search-bar px-3 pt-3">
            <form method="GET" action="admin_customers.php" class="admn-ordr-search">
                <input type="hidden" name="type" value="<?= htmlspecialchars($filter) ?>">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                           class="form-control"
                           placeholder="Search by name, email or phone...">
                    <?php if ($search): ?>
                    <a href="admin_customers.php?type=<?= $filter ?>"
                       class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </form>
            <span class="admn-cust-count ms-2">
                <?= count($customers) ?> result<?= count($customers) !== 1 ? 's' : '' ?>
            </span>
        </div>

        <!-- Table -->
        <div class="table-responsive mt-2">
            <table class="admn-cust-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Orders</th>
                        <th>Joined</th>
                        <th>Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="7">
                        <div class="admn-cust-empty">
                            <i class="fas fa-users"></i>
                            <p>No customers found.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($customers as $c):
                    $isPhotog = $c['customer_type'] === 'PHOTOGRAPHER';
                    $initial  = strtoupper(substr($c['first_name'], 0, 1));
                ?>
                <tr>
                    <td class="admn-cust-id">#<?= $c['customer_id'] ?></td>
                    <td>
                        <div class="admn-cust-name-wrap">
                            <div class="admn-cust-avatar"><?= $initial ?></div>
                            <div>
                                <div class="admn-cust-name"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></div>
                                <div class="admn-cust-username">@<?= htmlspecialchars($c['username']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="admn-cust-email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($c['email']) ?></div>
                        <div class="admn-cust-phone"><i class="fas fa-phone"></i> <?= htmlspecialchars($c['phone_number'] ?? '—') ?></div>
                    </td>
                    <td>
                        <span class="admn-cust-orders-badge"><?= $c['total_orders'] ?></span>
                    </td>
                    <td class="admn-cust-date">
                        <?= date('M d, Y', strtotime($c['created_at'])) ?>
                    </td>
                    <td>
                        <span class="admn-cust-type-badge <?= $isPhotog ? 'photographer' : 'regular' ?>">
                            <i class="fas fa-<?= $isPhotog ? 'camera' : 'user' ?>"></i>
                            <?= $isPhotog ? 'Photographer' : 'Regular' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($isPhotog): ?>
                        <button class="admn-cust-btn-revoke"
                                onclick="changeType(<?= $c['customer_id'] ?>, 'REGULAR', '<?= htmlspecialchars($c['first_name']) ?>')">
                            <i class="fas fa-user-minus"></i> Revoke
                        </button>
                        <?php else: ?>
                        <button class="admn-cust-btn-promote"
                                onclick="changeType(<?= $c['customer_id'] ?>, 'PHOTOGRAPHER', '<?= htmlspecialchars($c['first_name']) ?>')">
                            <i class="fas fa-camera"></i> Set Photographer
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div><!-- /.admn-cust-card -->
</div><!-- /.admn-cust-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function changeType(customerId, newType, name) {
    const isPromote = newType === 'PHOTOGRAPHER';
    const { isConfirmed } = await Swal.fire({
        title: isPromote ? `Set ${name} as Photographer?` : `Revoke Photographer status?`,
        text:  isPromote
            ? `${name} will receive photographer discounts on all future orders.`
            : `${name} will be reverted to a regular customer.`,
        icon:  'question',
        showCancelButton:   true,
        confirmButtonColor: isPromote ? '#0F473A' : '#ef4444',
        cancelButtonColor:  '#6b7280',
        confirmButtonText:  isPromote ? 'Yes, set as Photographer' : 'Yes, revoke',
        cancelButtonText:   'Cancel',
    });

    if (!isConfirmed) return;

    try {
        const fd = new FormData();
        fd.append('customer_id',   customerId);
        fd.append('customer_type', newType);

        const res  = await fetch('../process/update_customer_type.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text:  data.message,
                confirmButtonColor: '#0F473A',
                timer: 1800,
                showConfirmButton: false,
            }).then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Network error. Please try again.', 'error');
    }
}
</script>
</body>
</html>