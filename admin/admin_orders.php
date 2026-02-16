<?php
// admin_orders.php
session_start();
include __DIR__ . '/../config/db_connect.php';

// Security Check
if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role']) !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

// Map numeric delivery_status to readable names (based on your table comment)
$status_names = [
    0 => 'Pending',
    1 => 'Processing',
    2 => 'Ready for Pickup',
    3 => 'To be Delivered',
    4 => 'Completed',
    5 => 'Rejected',
    6 => 'Cancelled'
];

// Fetch counts using delivery_status
$counts = array_fill_keys(array_values($status_names), 0);

$count_sql = "SELECT delivery_status, COUNT(*) as total FROM tbl_orders GROUP BY delivery_status";
$count_result = $conn->query($count_sql);

if ($count_result) {
    while ($row = $count_result->fetch_assoc()) {
        $status_code = $row['delivery_status'];
        if (isset($status_names[$status_code])) {
            $counts[$status_names[$status_code]] = $row['total'];
        }
    }
}

// Tab logic – map tab names back to numeric codes
$active_tab = isset($_GET['status']) ? $_GET['status'] : 'new_orders';

$status_map = [
    'new_orders'     => 0,
    'processing'     => 1,
    'ready_pickup'   => 2,
    'delivery'       => 3,
    'sold'           => 4,
    'rejected'       => 5,
    'cancelled'      => 6
];

$selected_status = $status_map[$active_tab] ?? 0; // Default to Pending (0)

$where_clause = "o.delivery_status = $selected_status";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - RGA Frames</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

     <style>
        :root {
            --color-green: #A7C957;
            --color-gold:  #B89655;
            --color-brown: #795338;
            --bg-light:    #f8f9fa;
            --text-dark:   #333;
            --text-grey:   #666;
            --color-danger: #dc3545;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            padding-top: 80px;
        }

        @media (max-width: 991px) {
            body { padding-top: 140px; }
        }

        @media (max-width: 576px) {
            body { padding-top: 130px; }
        }

        .container { max-width: 1300px; margin: 0 auto; padding: 1rem; }

        .page-title {
            font-size: 2.1rem;
            font-weight: 800;
            color: var(--color-brown);
            margin-bottom: 0.3rem;
        }

        .page-subtitle {
            color: var(--text-grey);
            font-size: 1rem;
        }

        .orders-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
            border-top: 4px solid var(--color-gold);
        }

        .tabs-header {
            display: flex;
            overflow-x: auto;
            border-bottom: 1px solid #eee;
            padding: 0 1rem;
            background: #faf9f6;
        }

        .tab-link {
            padding: 1rem 1.4rem;
            color: var(--text-grey);
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            white-space: nowrap;
            position: relative;
            transition: color 0.2s;
        }

        .tab-link:hover { color: var(--color-gold); }

        .tab-link.active {
            color: var(--color-gold);
            font-weight: 700;
        }

        .tab-link.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--color-gold);
        }

        .tab-count {
            background: #e0e0e0;
            color: #555;
            border-radius: 12px;
            padding: 0.25rem 0.6rem;
            font-size: 0.8rem;
            margin-left: 0.5rem;
        }

        .tab-link.active .tab-count {
            background: var(--color-gold);
            color: white;
        }

        .search-container {
            padding: 1.2rem;
            border-bottom: 1px solid #eee;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 2.8rem;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #f9f9f9;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        .order-item {
            border-bottom: 1px solid #eee;
            padding: 1.5rem 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .order-info {
            flex: 1;
            min-width: 250px;
        }

        .order-finance {
            min-width: 180px;
            text-align: right;
        }

        .order-actions {
            min-width: 220px;
            text-align: right;
        }

        .finance-info {
            font-size: 0.95rem;
            color: #555;
            margin: 0.3rem 0;
        }

        .balance-text {
            color: var(--color-danger);
            font-weight: bold;
            font-size: 1.1rem;
        }

        .status-badge {
            font-size: 0.85rem;
            padding: 0.4rem 0.9rem;
            border-radius: 20px;
            font-weight: 600;
            margin-top: 0.5rem;
            display: inline-block;
        }

        .st-cancelled { background: #f3f4f6; color: #666; }
        .st-rejected  { background: #efebe9; color: var(--color-brown); border: 1px solid var(--color-brown); }

        .btn-action {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            margin: 0.3rem;
            transition: opacity 0.2s;
        }

        .btn-action:hover { opacity: 0.9; }

        .btn-accept { background: var(--color-green); color: white; }
        .btn-reject  { background: var(--color-danger); color: white; }
        .btn-done    { background: var(--color-gold); color: white; }
        .btn-finish  { background: var(--color-gold); color: white; }

        .empty-state {
            padding: 5rem 1rem;
            text-align: center;
            color: #aaa;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--color-gold);
            opacity: 0.4;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../includes/admin_header.php'; ?>

    <div class="container mt-4">
        <!-- Messages -->
        <?php
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    ' . htmlspecialchars($_SESSION['success']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ' . htmlspecialchars($_SESSION['error']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            unset($_SESSION['error']);
        }
        ?>

        <h1 class="page-title">Orders Dashboard</h1>
        <p class="page-subtitle">View and manage all customer orders</p>

        <div class="orders-card">
            <!-- Tabs - updated to match your numeric status codes -->
            <div class="tabs-header">
                <a href="?status=new_orders"    class="tab-link <?php echo $active_tab === 'new_orders' ? 'active' : ''; ?>">
                    New Orders <span class="tab-count"><?php echo $counts['Pending']; ?></span>
                </a>
                <a href="?status=processing"    class="tab-link <?php echo $active_tab === 'processing' ? 'active' : ''; ?>">
                    Processing <span class="tab-count"><?php echo $counts['Processing']; ?></span>
                </a>
                <a href="?status=ready_pickup"  class="tab-link <?php echo $active_tab === 'ready_pickup' ? 'active' : ''; ?>">
                    Pickup <span class="tab-count"><?php echo $counts['Ready for Pickup']; ?></span>
                </a>
                <a href="?status=delivery"      class="tab-link <?php echo $active_tab === 'delivery' ? 'active' : ''; ?>">
                    To Deliver <span class="tab-count"><?php echo $counts['To be Delivered']; ?></span>
                </a>
                <a href="?status=sold"          class="tab-link <?php echo $active_tab === 'sold' ? 'active' : ''; ?>">
                    Sold <span class="tab-count"><?php echo $counts['Completed']; ?></span>
                </a>
                <a href="?status=rejected"      class="tab-link <?php echo $active_tab === 'rejected' ? 'active' : ''; ?>">
                    Rejected <span class="tab-count"><?php echo $counts['Rejected']; ?></span>
                </a>
                <a href="?status=cancelled"     class="tab-link <?php echo $active_tab === 'cancelled' ? 'active' : ''; ?>">
                    Cancelled <span class="tab-count"><?php echo $counts['Cancelled']; ?></span>
                </a>
            </div>

            <!-- Search -->
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" placeholder="Search orders by ID, name or phone...">
                </div>
            </div>

            <!-- Orders List -->
            <div class="orders-content p-3">
                <?php
                // Use delivery_status and tbl_users
                $sql = "SELECT o.*, u.first_name, u.last_name, u.phone_number 
                        FROM tbl_orders o
                        JOIN tbl_users u ON o.user_id = u.user_id
                        WHERE o.delivery_status = $selected_status
                        ORDER BY o.created_at DESC";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $grand_total  = $row['total_price'] ?? 0; // your column is total_price
                        $downpayment  = $row['downpayment_amount'] ?? 0; // assuming you have this column
                        $balance      = $grand_total - $downpayment;
                        $payment_badge = ($balance <= 0) 
                            ? '<span class="badge bg-success">Fully Paid</span>' 
                            : '<span class="badge bg-warning">Partial Payment</span>';

                        // Map numeric delivery_status to name
                        $current_status = $status_names[$row['delivery_status']] ?? 'Unknown';
                        ?>
                        <div class="order-item border-bottom py-4">
                            <div class="order-info flex-grow-1">
                                <strong style="font-size: 1.25rem; color: var(--color-brown);">
                                    Order #<?php echo $row['order_id']; ?>
                                </strong>
                                <span class="ms-2 text-muted">
                                    | <?php echo $row['first_name'] . ' ' . $row['last_name']; ?>
                                </span>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-truck me-1" style="color:var(--color-gold);"></i> 
                                    <?php echo $row['delivery_option'] ?? 'N/A'; ?> 
                                    • 
                                    <i class="fas fa-credit-card me-1" style="color:var(--color-gold);"></i> 
                                    <?php echo $row['payment_method'] ?? 'N/A'; ?>
                                </small>
                                <div class="mt-2">
                                    <span class="status-badge" style="background: #e9ecef; color: #495057;">
                                        <?php echo $current_status; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="order-finance text-end">
                                <div>Total: ₱<?php echo number_format($grand_total, 2); ?></div>
                                <div>Down: ₱<?php echo number_format($downpayment, 2); ?></div>
                                <div class="balance-text fw-bold">Balance: ₱<?php echo number_format($balance, 2); ?></div>
                                <?php echo $payment_badge; ?>
                            </div>

                            <div class="order-actions text-end mt-3 mt-md-0">
                                <a href="admin_order_details.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-outline-secondary me-2">
                                    <i class="fas fa-eye"></i> Details
                                </a>

                                <!-- Add your action buttons here (accept, reject, etc.) based on $row['delivery_status'] -->
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="empty-state text-center py-5">
                            <i class="fas fa-box-open fa-4x mb-3" style="color: var(--color-gold); opacity: 0.5;"></i>
                            <p class="lead text-muted">No orders found in this category.</p>
                          </div>';
                }
                $conn->close();
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>