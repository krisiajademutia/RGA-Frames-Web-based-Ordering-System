<?php require_once __DIR__ . '/../process/shopping_cart_process.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | Custom Framing</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include '../includes/customer_header.php'; ?>

<div class="cart-main-wrapper">
    <div class="cart-page-header">
        <h1>Shopping Cart</h1>
        <p>Manage your selections before checkout.</p>
    </div>

    <?php if (!empty($cart_items)): ?>
    <!-- NEW: Select-all toolbar bar matching the reference image -->
    <div class="cart-toolbar-bar">
        <div class="cart-toolbar-inner">
            <label class="cart-toolbar-select-all">
                <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll(this)">
                <span>Select all</span>
            </label>
            <span class="cart-toolbar-count" id="toolbar-count">0 items selected</span>
            <button type="button" class="cart-toolbar-clear-btn" onclick="removeAllItems()">
                Clear all selected items
            </button>
        </div>
    </div>
    <?php endif; ?>

    <div class="cart-container">
        <div class="cart-content-grid">
            
            <div class="cart-items-section">
                <?php if (empty($cart_items)): ?>
                    <div class="alert alert-light border-dashed text-center py-5">
                        <p class="mb-3 text-muted">Your cart is currently empty.</p>
                        <a href="customer_dashboard.php" class="btn btn-outline-dark rounded-pill px-4">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item-card" data-id="<?= $item['id']; ?>" data-price="<?= $item['sub_total']; ?>" onclick="toggleSelection(this)">
                            <div class="cart-item-img">
                                <?php if (!empty($item['display_image'])): ?>
                                    <img src="<?= htmlspecialchars($item['display_image']); ?>" alt="Frame Image">
                                <?php else: ?>
                                    <i class="fa-regular fa-image"></i>
                                <?php endif; ?>
                            </div>
                            <div class="cart-item-details">
                                <h4 class="cart-item-name"><?= htmlspecialchars($item['display_name']); ?></h4>
                                <p class="cart-item-meta">
                                    <?= $item['service_type'] === 'FRAME&PRINT' ? 'Frame & Print' : 'Frame Only'; ?>
                                    <?php if ($item['width_inch']): ?>
                                        | <?= (float)$item['width_inch'] . 'x' . (float)$item['height_inch']; ?>"
                                    <?php endif; ?>
                                </p>
                                <div class="cart-qty-controls" onclick="event.stopPropagation()">
                                    <button type="button" class="cart-qty-btn" onclick="updateQty(<?= $item['id']; ?>, -1)">-</button>
                                    <input type="text" class="cart-qty-input" value="<?= $item['quantity']; ?>" readonly>
                                    <button type="button" class="cart-qty-btn" onclick="updateQty(<?= $item['id']; ?>, 1)">+</button>
                                </div>
                            </div>
                            <div class="cart-item-right">
                                <p class="cart-item-price">₱<?= number_format($item['sub_total'], 2); ?></p>
                               <button type="button" class="cart-remove-btn" title="Remove item" onclick="event.stopPropagation(); removeItem(<?= $item['id']; ?>)">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="cart-sidebar-column">
                <div class="cart-summary-card">
                    <div class="cart-summary-header">ORDER SUMMARY</div>
                    <div class="cart-summary-body">
                        <div id="empty-summary-msg" class="text-center py-3 text-muted small">Select items to view summary</div>
                        
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-summary-line" id="summary-item-<?= $item['id']; ?>" style="display: none;">
                                <div>
                                    <span class="cart-summary-item-name"><?= htmlspecialchars($item['display_name']); ?></span>
                                    <span class="cart-summary-item-sub"><?= $item['service_type'] === 'FRAME&PRINT' ? 'Frame & Print' : 'Frame only'; ?></span>
                                </div>
                                <div class="cart-summary-qty">×<?= $item['quantity']; ?></div>
                                <div class="cart-summary-item-price">₱<?= number_format($item['sub_total'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>

                        <?php if(!empty($cart_items)): ?>
                            <div class="cart-summary-divider"></div>
                            <div class="cart-total-section">
                                <span class="total-label">Total</span>
                                <span class="cart-total-value" id="running-total">₱0.00</span>
                            </div>
                            <form action="checkout.php" method="POST">
                                <input type="hidden" name="selected_items" id="selected-items-input">
                                <button type="submit" id="checkout-btn" class="cart-checkout-btn" disabled>Proceed to Checkout</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FIXED: Added style="display:none;" so modal is hidden on page load -->
<div id="deleteModal" class="-cart-modal-overlay" style="display:none;">
    <div class="-cart-modal-card">
        <div class="-cart-modal-icon-circle">
            <i class="fa-solid fa-cart-shopping"></i>
        </div>
        <h3 class="-cart-modal-title" id="modalTitle">Remove Item</h3>
        <p class="-cart-modal-text" id="modalText">Are you sure you want to remove this item from your cart? This action cannot be undone.</p>
        <div class="-cart-modal-actions">
            <button type="button" class="-cart-btn-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
            <a id="confirmDeleteBtn" href="#" class="-cart-btn-modal-confirm">Remove</a>
        </div>
    </div>
</div>

<script src="../assets/js/shopping_cart.js"></script>
</body>
</html>