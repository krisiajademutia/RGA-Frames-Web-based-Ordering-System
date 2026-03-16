let selectedTotal = 0;
let selectedIds = [];

/**
 * Toggles item selection for the order summary and checkout
 */
function toggleSelection(card) {
    const id = card.getAttribute('data-id');
    const price = parseFloat(card.getAttribute('data-price'));
    const summaryLine = document.getElementById(`summary-item-${id}`);
    const hiddenInput = document.getElementById('selected-items-input');
    const checkoutBtn = document.getElementById('checkout-btn');
    const emptyMsg = document.getElementById('empty-summary-msg');

    if (card.classList.contains('selected')) {
        // Deselect
        card.classList.remove('selected');
        if (summaryLine) summaryLine.style.display = 'none';
        selectedTotal -= price;
        selectedIds = selectedIds.filter(item => item !== id);
    } else {
        // Select
        card.classList.add('selected');
        // Set to grid to match your layout requirements
        if (summaryLine) summaryLine.style.display = 'grid'; 
        selectedTotal += price;
        selectedIds.push(id);
    }

    // Update Total Display
    const totalDisplay = document.getElementById('running-total');
    if(totalDisplay) {
        const displayValue = Math.max(0, selectedTotal);
        totalDisplay.innerText = '₱' + displayValue.toLocaleString(undefined, {
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2
        });
    }

    // Update Hidden Input for PHP POST
    if(hiddenInput) hiddenInput.value = JSON.stringify(selectedIds);
    
    // UI State: Empty message shows only when no cards are selected
    if(checkoutBtn) checkoutBtn.disabled = selectedIds.length === 0;
    if(emptyMsg) emptyMsg.style.display = selectedIds.length === 0 ? 'block' : 'none';
}

function updateQty(itemId, delta) {
    // Prevents quantity from being reduced below 1
    const qtyInput = event.target.parentElement.querySelector('.cart-qty-input');
    if (delta === -1 && parseInt(qtyInput.value) <= 1) return;
    
    window.location.href = `../process/shopping_cart_process.php?action=update_qty&id=${itemId}&delta=${delta}`;
}

function removeItem(itemId) {
    if(confirm('Remove this item from your cart?')) {
        window.location.href = `../process/shopping_cart_process.php?action=delete&id=${itemId}`;
    }
}