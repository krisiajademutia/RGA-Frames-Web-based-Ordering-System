<?php
// admin_order_details.php
session_start();
include 'db_connect.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Validate Order ID
if (!isset($_GET['id'])) { die("Error: Order ID is missing."); }
$order_id = intval($_GET['id']);

// 3. Fetch Order & User Info
$sql_order = "SELECT o.*, u.first_name, u.last_name, u.phone_number, u.address 
              FROM orders o
              LEFT JOIN users u ON o.user_id = u.user_id
              WHERE o.order_id = $order_id";
$result_order = $conn->query($sql_order);
if ($result_order->num_rows == 0) { die("Order not found."); }
$order = $result_order->fetch_assoc();

// 4. Fetch Order Items
$sql_items = "
    SELECT oi.*, p.frame_name, p.frame_design, p.base_image_url,
        pv.size AS variant_size, pv.color AS variant_color,
        cs.size_label AS custom_size_val,
        co.name AS custom_color_val, cm.name AS custom_matboard_val
    FROM order_items oi
    LEFT JOIN product_variants pv ON oi.product_variant_id = pv.variant_id
    LEFT JOIN products p ON pv.product_id = p.product_id
    LEFT JOIN custom_sizes cs ON oi.custom_size_id = cs.size_id
    LEFT JOIN custom_options co ON oi.custom_color_id = co.option_id
    LEFT JOIN custom_options cm ON oi.custom_matboard_id = cm.option_id
    WHERE oi.order_id = $order_id
";
$result_items = $conn->query($sql_items);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?php echo $order_id; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- BRAND PALETTE --- */
        :root { 
            --color-green: #A7C957;
            --color-gold: #B89655;
            --color-brown: #795338;
            
            --bg-light: #f8f9fa;
            --card-bg: #ffffff; 
            --text-main: #333; 
            --border: #e5e7eb; 
            --color-danger: #dc3545;
        }
        
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); color: var(--text-main); margin: 0; padding-top: 100px; }

        .container { max-width: 1000px; margin: 0 auto; padding: 0 20px 40px; }
        
        /* Back Button */
        .back-btn { display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: #6b7280; font-weight: 600; margin-bottom: 20px; font-size: 0.9rem; transition: color 0.2s; }
        .back-btn:hover { color: var(--color-gold); }

        /* Card Styles */
        .order-header-card, .info-card, .table-card, .proof-section {
            background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 20px; padding: 25px;
            /* Top border accent in Gold */
            border-top: 3px solid var(--color-gold);
        }
        
        .order-header-card { display: flex; justify-content: space-between; align-items: flex-start; }
        
        /* Typography */
        .order-title h1 { margin: 0; font-size: 1.6rem; color: var(--color-brown); font-weight: 700; }
        .order-meta { color: #6b7280; font-size: 0.9rem; margin-top: 5px; }
        
        /* Status Badges */
        .status-badge { padding: 6px 16px; border-radius: 50px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Status Colors mapped to Brand/Logic */
        .status-Pending { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .status-Preparing { background: #fdf8f6; color: var(--color-brown); border: 1px solid #ebd5c1; } /* Light Brown */
        .status-Ready { background: #fefce8; color: #854d0e; border: 1px solid #fef08a; } /* Yellowish */
        .status-To { background: #eff6ff; color: #1e40af; border: 1px solid #dbeafe; } /* Blue (To be Delivered) */
        .status-Completed { background: #f2f7e6; color: #5a6e2e; border: 1px solid #dae8b5; } /* Green Palette */
        .status-Rejected { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .status-Cancelled { background: #f3f4f6; color: #666; text-decoration: line-through; border: 1px solid #e5e7eb; }

        /* Grid & Tables */
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .card-title { font-size: 0.85rem; text-transform: uppercase; color: var(--color-brown); font-weight: 700; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95rem; }
        .info-value { font-weight: 500; color: #111; text-align: right; }

        .table-card { padding: 0; overflow: hidden; border-top: 3px solid var(--color-green); /* Differentiate table slightly */ }
        table { width: 100%; border-collapse: collapse; }
        th { background: #fcfcfc; text-align: left; padding: 15px 25px; font-size: 0.85rem; color: var(--color-brown); font-weight: 600; border-bottom: 1px solid #eee; }
        td { padding: 20px 25px; border-bottom: 1px solid var(--border); font-size: 0.95rem; vertical-align: top; }
        .item-main { font-weight: 600; color: var(--color-brown); display: block; }
        .item-sub { font-size: 0.85rem; color: #6b7280; line-height: 1.5; margin-top: 4px; }
        .item-type-badge { padding: 3px 8px; background: #f3f4f6; color: #4b5563; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }

        /* Proof Images */
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 20px; }
        .proof-item { text-align: center; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; transition: border-color 0.2s; }
        .proof-item:hover { border-color: var(--color-gold); }
        .proof-item img { width: 100%; height: 120px; object-fit: cover; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

    <?php include 'admin_header.php'; ?>

    <div class="container">
        
        <a href="admin_orders.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Order List
        </a>

        <div class="order-header-card">
            <div class="order-title">
                <h1>Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></h1>
                <div class="order-meta">
                    <i class="far fa-clock"></i> Placed on <?php echo date("F j, Y • g:i A", strtotime($order['created_at'])); ?>
                </div>
            </div>
            <div class="order-status">
                <?php 
                    $status_class = explode(' ', $order['status'])[0]; 
                    if($order['status'] == 'To be Delivered') $status_class = 'To';
                ?>
                <span class="status-badge status-<?php echo $status_class; ?>">
                    <?php echo $order['status']; ?>
                </span>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <div class="card-title">Customer Details</div>
                <div class="info-row">
                    <span style="color:#6b7280;">Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                </div>
                <div class="info-row">
                    <span style="color:#6b7280;">Phone</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['phone_number']); ?></span>
                </div>
                <div class="info-row">
                    <span style="color:#6b7280;">Address</span>
                    <span class="info-value" style="max-width: 60%;">
                        <?php echo nl2br(htmlspecialchars($order['delivery_address'] ?? 'N/A')); ?>
                    </span>
                </div>
            </div>

            <div class="info-card">
                <div class="card-title">Payment & Totals</div>
                <div class="info-row">
                    <span style="color:#6b7280;">Method</span>
                    <span class="info-value">
                        <?php echo $order['payment_method']; ?> 
                        <small style="color: var(--color-gold); font-weight:bold;">(<?php echo $order['payment_status']; ?>)</small>
                    </span>
                </div>
                <div class="info-row">
                    <span style="color:#6b7280;">Delivery</span>
                    <span class="info-value"><?php echo $order['delivery_option']; ?></span>
                </div>
                <div style="border-top: 1px dashed #e5e7eb; margin: 15px 0;"></div>
                <div class="info-row" style="font-size: 1.1rem;">
                    <span style="color: #111; font-weight: 600;">Grand Total</span>
                    <span style="color: var(--color-brown); font-weight: 700;">₱<?php echo number_format($order['grand_total'], 2); ?></span>
                </div>
                <div class="info-row">
                    <span style="color:#6b7280;">Downpayment</span>
                    <span class="info-value" style="color: #666;">-₱<?php echo number_format($order['downpayment_amount'], 2); ?></span>
                </div>
                <div class="info-row">
                    <span style="color:#6b7280;">Balance Due</span>
                    <?php $bal = $order['grand_total'] - $order['downpayment_amount']; ?>
                    <span class="info-value" style="color: <?php echo ($bal > 0) ? 'var(--color-danger)' : 'var(--color-green)'; ?>;">
                        ₱<?php echo number_format($bal, 2); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th style="width: 55%;">Item Details</th>
                        <th style="width: 15%;">Type</th>
                        <th style="width: 10%; text-align: center;">Qty</th>
                        <th style="width: 20%; text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
    <?php if ($result_items && $result_items->num_rows > 0): ?>
        <?php while($item = $result_items->fetch_assoc()): ?>
        <tr>
            <td>
                <?php 
                // --- CASE 1: READY-MADE (Both types) ---
                if (strpos($item['service_type'], 'Ready-Made') !== false): ?>
                    <span class="item-main"><?php echo htmlspecialchars($item['frame_name']); ?></span>
                    <div class="item-details-list">
                        <div class="detail-line"><span class="detail-label">Size:</span> <span class="detail-val"><?php echo $item['variant_size']; ?></span></div>
                        <div class="detail-line"><span class="detail-label">Color:</span> <span class="detail-val"><?php echo $item['variant_color']; ?></span></div>
                    </div>

                <?php 
                // --- CASE 2: CUSTOM FRAMES (Both types) ---
                elseif (strpos($item['service_type'], 'Custom') !== false): ?>
                    <span class="item-main"><i class="fas fa-tools"></i> Custom Frame</span>
                    <div class="item-details-list">
                         <div class="detail-line"><span class="detail-label">Size:</span> <span class="detail-val"><?php echo $item['custom_size_val']; ?></span></div>
                         <div class="detail-line"><span class="detail-label">Frame:</span> <span class="detail-val"><?php echo $item['custom_color_val']; ?></span></div>
                         <?php if($item['custom_matboard_val']): ?>
                             <div class="detail-line"><span class="detail-label">Mat:</span> <span class="detail-val"><?php echo $item['custom_matboard_val']; ?></span></div>
                         <?php endif; ?>
                    </div>
                
                <?php 
                // --- CASE 3: PRINT ONLY ---
                elseif ($item['service_type'] === 'Print-Only'): ?>
                     <span class="item-main" style="color:#e67e22;"><i class="fas fa-print"></i> Printing Service</span>
                     <div class="item-details-list">
                         <div class="detail-line">
                             <span class="detail-label">Size:</span> 
                             <span class="detail-val"><?php echo $item['custom_size_val']; // e.g. A4, A3 ?></span>
                         </div>
                         <div class="detail-line">
                             <span class="detail-label">Paper:</span> 
                             <span class="detail-val"><?php echo $item['custom_matboard_val'] ?? 'Standard'; ?></span>
                         </div>
                     </div>
                <?php endif; ?>

                <?php if (!empty($item['print_image'])): ?>
                    <div style="margin-top: 10px; padding: 10px; background: #fffbeb; border: 1px dashed #eab308; border-radius: 6px; display: flex; gap: 10px; align-items: center;">
                        <img src="<?php echo htmlspecialchars($item['print_image']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border:1px solid #ccc;">
                        <div>
                            <div style="font-size: 0.85rem; font-weight: 700; color: #795338;">
                                <?php echo ($item['service_type'] == 'Print-Only') ? 'Image to Print' : 'Image to Mount'; ?>
                            </div>
                            <a href="<?php echo htmlspecialchars($item['print_image']); ?>" download class="text-blue-600 hover:underline" style="font-size: 0.8rem; color: #2563eb;">
                                <i class="fas fa-download"></i> Download High-Res
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </td>

            <td>
                <?php 
                if (strpos($item['service_type'], 'Ready-Made') !== false) {
                    $badgeClass = 'badge-ready';
                    $badgeText = 'Ready-Made';
                } elseif ($item['service_type'] === 'Print-Only') {
                    $badgeClass = 'badge-custom'; // You can make a new 'badge-print' class yellow/orange
                    $badgeText = 'Print Only';
                } else {
                    $badgeClass = 'badge-custom';
                    $badgeText = 'Custom';
                }
                ?>
                <span class="item-type-badge <?php echo $badgeClass; ?>">
                    <?php echo $badgeText; ?>
                </span>
            </td>

            <td style="text-align: center;">x<?php echo $item['frame_quantity']; ?></td>
            <td style="text-align: right;">₱<?php echo number_format($item['item_subtotal'], 2); ?></td>
        </tr>
        <?php endwhile; ?>
    <?php endif; ?>
</tbody>
            </table>
        </div>

        <?php if(!empty($order['payment_proof_image']) || !empty($order['initial_receipt_image']) || !empty($order['final_receipt_image'])): ?>
        <div class="proof-section">
            <div class="card-title">Attached Documents</div>
            <div class="gallery-grid">
                
                <?php if(!empty($order['payment_proof_image'])): ?>
                <div class="proof-item">
                    <a href="<?php echo htmlspecialchars($order['payment_proof_image']); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($order['payment_proof_image']); ?>" alt="Customer Proof">
                    </a>
                    <div style="font-size:12px; margin-top:5px; color:#666;">Customer Proof</div>
                </div>
                <?php endif; ?>

                <?php if(!empty($order['initial_receipt_image'])): ?>
                <div class="proof-item">
                    <a href="<?php echo htmlspecialchars($order['initial_receipt_image']); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($order['initial_receipt_image']); ?>" alt="Shop Receipt">
                    </a>
                    <div style="font-size:12px; margin-top:5px; color:#666;">Downpayment Receipt</div>
                </div>
                <?php endif; ?>

                <?php if(!empty($order['final_receipt_image'])): ?>
                <div class="proof-item">
                    <a href="<?php echo htmlspecialchars($order['final_receipt_image']); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($order['final_receipt_image']); ?>" alt="Final Receipt">
                    </a>
                    <div style="font-size:12px; margin-top:5px; color:#666;">Final Receipt</div>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php endif; ?>

    </div>

</body>
</html>