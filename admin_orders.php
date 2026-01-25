<?php
// admin_orders.php
session_start();
include 'db_connect.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// -----------------------------------------------------
// 2. FETCH COUNTS (The Logic to get the totals)
// -----------------------------------------------------
$counts = [
    'Pending' => 0,
    'Preparing' => 0,
    'Ready for Pickup' => 0,
    'To be Delivered' => 0,
    'Completed' => 0,
    'Cancelled' => 0,
    'Rejected' => 0
];

$count_sql = "SELECT status, COUNT(*) as total FROM orders GROUP BY status";
$count_result = $conn->query($count_sql);

if ($count_result) {
    while ($row = $count_result->fetch_assoc()) {
        $counts[$row['status']] = $row['total'];
    }
}

// -----------------------------------------------------
// 3. TAB LOGIC
// -----------------------------------------------------
$active_tab = isset($_GET['status']) ? $_GET['status'] : 'new_orders';
$db_status_query = "Pending"; // Default

switch ($active_tab) {
    case 'new_orders': 
        $where_clause = "o.status = 'Pending'"; 
        break;
    case 'preparing': 
        $where_clause = "o.status = 'Preparing'"; 
        break;
    case 'ready_pickup': 
        $where_clause = "o.status = 'Ready for Pickup'"; 
        break;
    case 'delivery': 
        $where_clause = "o.status = 'To be Delivered'"; 
        break;
    case 'sold': 
        $where_clause = "o.status = 'Completed'"; 
        break;
    case 'rejected': 
        $where_clause = "o.status = 'Rejected'"; 
        break;
    case 'cancelled': 
        $where_clause = "o.status = 'Cancelled'"; 
        break;
    default:
        $where_clause = "o.status = 'Pending'";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders - RGA Frames</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- NEW COLOR PALETTE --- */
        :root {
            /* YOUR PALETTE */
            --color-green: #A7C957;
            --color-gold: #B89655;
            --color-brown: #795338;
            
            /* Standard UI Colors */
            --bg-light: #f8f9fa;
            --text-dark: #333;
            --text-grey: #666;
            --color-danger: #dc3545; /* Keep red for reject actions for safety */
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            color: var(--text-dark);
            padding-top: 100px;
        }
        
        /* --- CONTENT --- */
        .container { max-width: 1200px; margin: 0 auto; padding-bottom: 50px; }
        /* Use Brown for strong headings */
        .page-title { font-size: 24px; font-weight: bold; margin-bottom: 5px; color: var(--color-brown); }
        .page-subtitle { font-size: 14px; color: var(--text-grey); margin-bottom: 25px; }

        .orders-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); overflow: hidden; border-top: 3px solid var(--color-gold); }

        /* --- TABS --- */
        .tabs-header { display: flex; border-bottom: 1px solid #eee; padding: 0 20px; overflow-x: auto; }
        .tab-link {
            padding: 20px 20px;
            text-decoration: none;
            color: var(--text-grey);
            font-weight: 600;
            font-size: 13px;
            position: relative;
            white-space: nowrap;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }
        /* Hover and Active states use the Gold color */
        .tab-link:hover { color: var(--color-gold); }
        .tab-link.active { color: var(--color-gold); }
        .tab-link.active::after {
            content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 3px; 
            background-color: var(--color-gold); /* Gold indicator */
        }

        /* --- COUNT BADGES --- */
        .tab-count {
            background-color: #eee;
            color: #555;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 11px;
            margin-left: 8px;
            font-weight: bold;
        }
        .tab-link.active .tab-count {
            background-color: #f0e5d0; /* Light gold tint */
            color: var(--color-brown); /* Brown text */
        }

        /* --- SEARCH --- */
        .search-container { padding: 20px; border-bottom: 1px solid #eee; }
        .search-box { position: relative; width: 100%; }
        .search-box input {
            width: 100%; padding: 12px 20px 12px 45px;
            border: 1px solid #e0e0e0; border-radius: 8px; outline: none; background-color: #f9f9f9;
        }
        .search-box input:focus { border-color: var(--color-gold); background: white; }
        .search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #aaa; }

        /* --- BADGES & BUTTONS --- */
        .badge-payment { font-size: 11px; padding: 4px 10px; border-radius: 4px; font-weight: bold; color: white; }
        /* Use Palette Green for Paid */
        .bg-paid { background-color: var(--color-green); }
        /* Use Palette Gold for Partial */
        .bg-partial { background-color: var(--color-gold); }

        .btn-action { padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; color: white; margin-left: 5px; font-weight: 600; transition: opacity 0.2s; }
        .btn-action:hover { opacity: 0.9; }
        
        /* Accept uses Palette Green */
        .btn-accept, .btn-green { background-color: var(--color-green); }
        
        /* Reject keeps red for safety, but softer */
        .btn-reject { background-color: var(--color-danger); }
        
        /* Done/Finish uses Palette Gold */
        .btn-done, .btn-finish, .btn-blue { background-color: var(--color-gold); }

        .finance-info { font-size: 12px; color: #555; margin-top: 5px; }
        .balance-text { color: var(--color-danger); font-weight: bold; }
        
        .empty-state { padding: 60px 20px; text-align: center; color: #ccc; }
        .empty-icon { font-size: 60px; margin-bottom: 20px; color: #e0e0e0; }

        /* NEW STATUS BADGES */
        .status-badge { font-size: 11px; padding: 4px 10px; border-radius: 20px; display: inline-block; margin-top: 5px; font-weight: 600;}
        .st-cancelled { background: #f3f4f6; color: #666; }
        /* Rejected uses Brown palette */
        .st-rejected { background: #efebe9; color: var(--color-brown); border: 1px solid var(--color-brown); }

    </style>
</head>
<body>

    <?php include 'admin_header.php'; ?>

    <div class="container">
        <h1 class="page-title">Orders Dashboard</h1>
        <p class="page-subtitle">Manage and track all customer orders</p>

        <div class="orders-card">
            
            <div class="tabs-header">
                <a href="?status=new_orders" class="tab-link <?php echo ($active_tab == 'new_orders') ? 'active' : ''; ?>">
                    New Orders <span class="tab-count"><?php echo $counts['Pending']; ?></span>
                </a>
                <a href="?status=preparing" class="tab-link <?php echo ($active_tab == 'preparing') ? 'active' : ''; ?>">
                    Preparing <span class="tab-count"><?php echo $counts['Preparing']; ?></span>
                </a>
                <a href="?status=ready_pickup" class="tab-link <?php echo ($active_tab == 'ready_pickup') ? 'active' : ''; ?>">
                    Pickup <span class="tab-count"><?php echo $counts['Ready for Pickup']; ?></span>
                </a>
                <a href="?status=delivery" class="tab-link <?php echo ($active_tab == 'delivery') ? 'active' : ''; ?>">
                    To Deliver <span class="tab-count"><?php echo $counts['To be Delivered']; ?></span>
                </a>
                <a href="?status=sold" class="tab-link <?php echo ($active_tab == 'sold') ? 'active' : ''; ?>">
                    Sold <span class="tab-count"><?php echo $counts['Completed']; ?></span>
                </a>
                <a href="?status=rejected" class="tab-link <?php echo ($active_tab == 'rejected') ? 'active' : ''; ?>">
                    Rejected <span class="tab-count"><?php echo $counts['Rejected']; ?></span>
                </a>
                <a href="?status=cancelled" class="tab-link <?php echo ($active_tab == 'cancelled') ? 'active' : ''; ?>">
                    Cancelled <span class="tab-count"><?php echo $counts['Cancelled']; ?></span>
                </a>
            </div>

            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" placeholder="Search orders...">
                </div>
            </div>

            <div class="orders-content">
                <?php
                $sql = "SELECT o.*, u.first_name, u.last_name, u.phone_number 
                        FROM orders o
                        JOIN users u ON o.user_id = u.user_id
                        WHERE $where_clause
                        ORDER BY o.created_at DESC";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        
                        $grand_total = $row['grand_total'];
                        $downpayment = $row['downpayment_amount'];
                        $balance = $grand_total - $downpayment;
                        $payment_badge = ($balance <= 0) ? '<span class="badge-payment bg-paid">Fully Paid</span>' : '<span class="badge-payment bg-partial">Partial Payment</span>';
                        ?>
                        
                        <div style="border-bottom: 1px solid #eee; padding: 25px 20px; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s;">
                            
                            <div>
                                <strong style="font-size:18px; color: var(--color-brown);">Order #<?php echo $row['order_id']; ?></strong> 
                                <span style="color: #777; font-size: 14px;"> | <?php echo $row['first_name'] . ' ' . $row['last_name']; ?></span>
                                <br>
                                <small style="color:#666; font-size: 13px; display:block; margin-top:4px;">
                                    <i class="fas fa-truck" style="color:var(--color-gold);"></i> <b><?php echo $row['delivery_option']; ?></b> &nbsp;•&nbsp; 
                                    <i class="fas fa-credit-card" style="color:var(--color-gold);"></i> <b><?php echo $row['payment_method']; ?></b>
                                </small>
                                
                                <div style="margin-top: 10px; font-size: 13px;">
                                    
                                    <?php if($active_tab == 'rejected'): ?>
                                        <span class="status-badge st-rejected"><i class="fas fa-ban"></i> Rejected by Admin</span>
                                        <br><br>
                                    <?php elseif($active_tab == 'cancelled'): ?>
                                        <span class="status-badge st-cancelled"><i class="fas fa-user-slash"></i> Cancelled by Customer</span>
                                        <br><br>
                                    <?php endif; ?>
                                    
                                    <?php if($row['payment_method'] == 'GCash' && !empty($row['payment_proof_image'])): ?>
                                        <a href="<?php echo $row['payment_proof_image']; ?>" target="_blank" style="color: var(--color-gold); margin-right:15px; text-decoration:none; font-weight:500;">
                                            <i class="fas fa-file-image"></i> View Customer Proof
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if(empty($row['initial_receipt_image'])): ?>
                                        <span style="color: var(--color-danger);"><i class="fas fa-exclamation-circle"></i> No Receipt Sent</span>
                                    <?php else: ?>
                                        <span style="color: var(--color-green);"><i class="fas fa-check-circle"></i> Receipt Sent</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div style="min-width: 150px; border-left:2px solid #f0f0f0; padding-left:25px;">
                                <div class="finance-info" style="font-size: 14px;">Total: ₱<?php echo number_format($grand_total, 2); ?></div>
                                <div class="finance-info">Down: -₱<?php echo number_format($downpayment, 2); ?></div>
                                <div class="finance-info balance-text" style="font-size: 15px; margin-top: 4px;">Bal: ₱<?php echo number_format($balance, 2); ?></div>
                                <div style="margin-top:8px;"><?php echo $payment_badge; ?></div>
                            </div>

                      <div style="text-align: right; padding-left:25px; min-width: 240px;">
                                
                                <a href="admin_order_details.php?id=<?php echo $row['order_id']; ?>" style="text-decoration: none; font-size: 13px; margin-right: 15px; color: var(--text-grey); font-weight:500;">View Details</a>

                                <?php if ($active_tab == 'new_orders'): ?>
                                    <form method="POST" action="update_order.php" enctype="multipart/form-data" style="margin-top: 15px; background: #fafafa; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <div style="margin-bottom: 10px;">
                                            <label style="font-size: 11px; display: block; text-align: left; color: var(--color-brown); font-weight:600; margin-bottom: 5px;">Upload Downpayment Receipt:</label>
                                            <input type="file" name="receipt_img" required style="font-size: 12px; width: 100%; border: 1px solid #ddd; padding: 5px; background: white; border-radius: 4px;">
                                        </div>
                                        <div style="display: flex; gap: 10px;">
                                            <button type="submit" class="btn-action btn-accept" style="flex: 1;"><i class="fas fa-check"></i> Accept</button>
                                    </form>
                                    <form method="POST" action="update_order.php" style="flex: 1;">
                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button class="btn-action btn-reject" style="width: 100%; height: 100%;"><i class="fas fa-times"></i> Reject</button>
                                    </form>
                                        </div>

                                <?php elseif ($active_tab == 'preparing'): ?>
                                    <form method="POST" action="update_order.php" style="margin-top: 10px;">
                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                        <input type="hidden" name="action" value="mark_done">
                                        <input type="hidden" name="next_status" value="<?php echo ($row['delivery_option'] == 'Delivery') ? 'To be Delivered' : 'Ready for Pickup'; ?>">
                                        <button type="submit" class="btn-action btn-done" style="width: 100%; padding: 12px;"><i class="fas fa-hammer"></i> Mark as Done</button>
                                    </form>

                                <?php elseif ($active_tab == 'ready_pickup' || $active_tab == 'delivery'): ?>
                                    <form method="POST" action="update_order.php" enctype="multipart/form-data" style="margin-top: 15px; background: #fafafa; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                        <input type="hidden" name="action" value="mark_sold">
                                        <div style="margin-bottom: 10px;">
                                            <label style="font-size: 11px; display: block; text-align: left; color: var(--color-brown); font-weight:600; margin-bottom: 5px;">Upload Final Receipt:</label>
                                            <input type="file" name="receipt_img" required style="font-size: 12px; width: 100%; border: 1px solid #ddd; padding: 5px; background: white; border-radius: 4px;">
                                        </div>
                                        <button type="submit" class="btn-action btn-finish" style="width: 100%;"><i class="fas fa-hand-holding-dollar"></i> Complete Transaction</button>
                                    </form>

                                <?php elseif ($active_tab == 'cancelled' || $active_tab == 'rejected'): ?>
                                    <div style="margin-top: 10px; font-size: 12px; color: #999; font-style: italic;">
                                        Archived Order
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="empty-state"><i class="fas fa-box-open empty-icon" style="color: var(--color-gold); opacity: 0.5;"></i><p class="empty-text">No orders found in this section.</p></div>';
                }
                ?>
            </div>
            </div>
    </div>

</body>
</html>