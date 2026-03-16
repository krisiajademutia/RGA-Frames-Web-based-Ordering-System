<?php 
session_start();
include __DIR__ . '/../config/db_connect.php';

// Mock data for structure - Replace this with your actual SQL query or Session Cart logic
$cart_items = $_SESSION['cart'] ?? []; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | Custom Framing</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --forest-dark:     #0F473A; 
            --forest-muted:    #4F9280; 
            --cream-dark:      #CDC3A0; 
            --cream-light:     #ECE7D4; 
            --font-primary:    'Inter', sans-serif;
            --font-secondary:  'Open Sans', sans-serif;
            --text-black:      #000000;
            --cart-bg:         #ffffff; 
            --cart-white:      #ffffff;
            --cart-border:     #d1d5db;
            --cart-text-muted: #6B7280;
        }

        body {
            font-family: var(--font-primary);
            background-color: var(--cart-bg);
            color: var(--text-black);
            margin: 0;
            /* Padding for the fixed header height */
            padding-top: 80px; 
        }

        .cart-main-wrapper {
            width: 100%;
            /* 250px margin above the content to clear the header area */
            padding: 130px 50px 40px 50px; 
            margin: 0;
        }

        .cart-page-header {
            margin-bottom: 40px;
        }

        .cart-page-header h1 {
            font-weight: 800;
            color: var(--forest-dark);
            font-size: 34px;
            margin: 0;
        }

        /* Item Cards */
        .cart-item-card {
            background: var(--cart-white);
            border-radius: 20px;
            padding: 24px 30px;
            margin-bottom: 20px;
            border: 1px solid var(--cart-border);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .cart-item-img {
            width: 180px;
            height: 120px;
            background-color: #E5E7EB;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9CA3AF;
            font-size: 36px;
            flex-shrink: 0;
            overflow: hidden;
        }
        
        .cart-item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-details {
            flex-grow: 1;
        }

        .cart-item-name {
            font-weight: 700;
            font-size: 24px;
            margin: 0 0 5px 0;
            color: var(--forest-dark);
        }

        .cart-item-meta {
            font-family: var(--font-secondary);
            font-size: 14px;
            color: var(--cart-text-muted);
            margin-bottom: 12px;
        }

        /* Qty Controls */
        .cart-qty-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-qty-btn {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1px solid #D1D5DB;
            background: white;
            color: #4B5563;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .cart-qty-btn:hover { background: #f3f4f6; }

        .cart-qty-input {
            width: 45px;
            height: 28px;
            text-align: center;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
        }

        .cart-item-right {
            text-align: right;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
            height: 120px;
        }

        .cart-item-price {
            font-weight: 800;
            font-size: 24px;
            color: var(--forest-dark);
            margin: 0;
        }

        .cart-remove-btn {
            background: none;
            border: none;
            color: #ef4444;
            font-size: 20px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .cart-remove-btn:hover { opacity: 0.7; }

        /* Sidebar Summary */
        .cart-sidebar-column {
            padding-left: 60px !important; 
        }

        .cart-summary-card {
            background: var(--cart-white);
            border-radius: 25px;
            border: 1px solid var(--cart-border);
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            position: sticky;
            /* Adjusted for large top spacing */
            top: 20px; 
        }

        .cart-summary-header {
            background: var(--forest-dark); 
            padding: 16px 25px;
            color: var(--cream-light);
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .cart-summary-body {
            padding: 25px;
            font-family: var(--font-secondary);
        }

        .cart-summary-line {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
            margin-bottom: 22px;
            align-items: start;
        }

        .cart-summary-item-name {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-black);
            line-height: 1.2;
        }

        .cart-summary-item-sub {
            font-size: 12px;
            color: var(--cart-text-muted);
            display: block;
            margin-top: 2px;
        }

        .cart-summary-divider {
            border-top: 1px solid #E5E7EB;
            margin: 20px 0;
        }

        .cart-total-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-family: var(--font-primary);
        }

        .cart-total-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--forest-dark);
        }

        .cart-checkout-btn {
            width: 100%;
            background: var(--forest-dark);
            color: var(--cream-light);
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            font-family: var(--font-primary);
            transition: background-color 0.2s;
        }

        .cart-checkout-btn:hover {
            background-color: var(--forest-muted);
        }

        @media (max-width: 991px) {
            .cart-sidebar-column {
                padding-left: 12px !important;
                margin-top: 40px;
            }
            .cart-main-wrapper {
                padding-top: 150px; /* Reduced for mobile if necessary */
            }
        }
    </style>
</head>
<body>

<?php include '../includes/customer_header.php'; ?>

<div class="cart-main-wrapper">
    <div class="cart-page-header">
        <h1>Shopping Cart</h1>
        <p>Review your custom frames before checking out.</p>
    </div>

    <div class="row g-0">
        <div class="col-lg-8">
            <div class="cart-item-card">
                <div class="cart-item-img">
                    <i class="fa-regular fa-image"></i>
                </div>
                <div class="cart-item-details">
                    <h4 class="cart-item-name">Antique Gold Frame</h4>
                    <p class="cart-item-meta">12x16" | Frame only</p>
                    <div class="cart-qty-controls">
                        <button class="cart-qty-btn">-</button>
                        <input type="text" class="cart-qty-input" value="1">
                        <button class="cart-qty-btn">+</button>
                    </div>
                </div>
                <div class="cart-item-right">
                    <p class="cart-item-price">₱650.00</p>
                    <button class="cart-remove-btn" title="Remove item">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
            
            <div class="cart-item-card">
                <div class="cart-item-img">
                    <i class="fa-regular fa-image"></i>
                </div>
                <div class="cart-item-details">
                    <h4 class="cart-item-name">Vintage Brown Frame</h4>
                    <p class="cart-item-meta">5x7" | Frame only</p>
                    <div class="cart-qty-controls">
                        <button class="cart-qty-btn">-</button>
                        <input type="text" class="cart-qty-input" value="1">
                        <button class="cart-qty-btn">+</button>
                    </div>
                </div>
                <div class="cart-item-right">
                    <p class="cart-item-price">₱249.00</p>
                    <button class="cart-remove-btn"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>
        </div>

        <div class="col-lg-4 cart-sidebar-column">
            <div class="cart-summary-card">
                <div class="cart-summary-header">ORDER SUMMARY</div>
                <div class="cart-summary-body">
                    <div class="cart-summary-line">
                        <div>
                            <span class="cart-summary-item-name">Antique Gold Frame</span>
                            <span class="cart-summary-item-sub">Qty: 1</span>
                        </div>
                        <span class="cart-summary-item-price">₱650.00</span>
                    </div>

                    <div class="cart-summary-line">
                        <div>
                            <span class="cart-summary-item-name">Vintage Brown Frame</span>
                            <span class="cart-summary-item-sub">Qty: 1</span>
                        </div>
                        <span class="cart-summary-item-price">₱249.00</span>
                    </div>

                    <div class="cart-summary-divider"></div>

                    <div class="cart-total-section">
                        <span class="fw-bold">Total</span>
                        <span class="cart-total-value">₱899.00</span>
                    </div>

                    <form action="checkout.php" method="POST">
                        <button type="submit" class="cart-checkout-btn">Proceed to Checkout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>