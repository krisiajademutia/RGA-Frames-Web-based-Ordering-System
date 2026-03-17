let selectedTotal = 0;
let selectedIds = [];

function updateToolbarCount() {
    const countEl = document.getElementById('toolbar-count');
    if (countEl) {
        const n = selectedIds.length;
        countEl.innerText = n === 0 ? '0 items selected' : `${n} item${n > 1 ? 's' : ''} selected`;
    }
    const allCards = document.querySelectorAll('.cart-item-card');
    const selectAllCb = document.getElementById('select-all-checkbox');
    if (selectAllCb && allCards.length > 0) {
        selectAllCb.checked = selectedIds.length === allCards.length;
        selectAllCb.indeterminate = selectedIds.length > 0 && selectedIds.length < allCards.length;
    }
}

function toggleSelection(card) {
    const id = card.getAttribute('data-id');
    const price = parseFloat(card.getAttribute('data-price'));
    const summaryLine = document.getElementById(`summary-item-${id}`);
    const hiddenInput = document.getElementById('selected-items-input');
    const checkoutBtn = document.getElementById('checkout-btn');
    const emptyMsg = document.getElementById('empty-summary-msg');

    if (card.classList.contains('selected')) {
        card.classList.remove('selected');
        if (summaryLine) summaryLine.style.display = 'none';
        selectedTotal -= price;
        selectedIds = selectedIds.filter(item => item !== id);
    } else {
        card.classList.add('selected');
        if (summaryLine) summaryLine.style.display = 'grid';
        selectedTotal += price;
        selectedIds.push(id);
    }

    const totalDisplay = document.getElementById('running-total');
    if (totalDisplay) {
        const displayValue = Math.max(0, selectedTotal);
        totalDisplay.innerText = '₱' + displayValue.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    if (hiddenInput) hiddenInput.value = JSON.stringify(selectedIds);
    if (checkoutBtn) checkoutBtn.disabled = selectedIds.length === 0;
    if (emptyMsg) emptyMsg.style.display = selectedIds.length === 0 ? 'block' : 'none';

    updateToolbarCount();
}

function toggleSelectAll(checkbox) {
    const allCards = document.querySelectorAll('.cart-item-card');
    allCards.forEach(card => {
        const isSelected = card.classList.contains('selected');
        if (checkbox.checked && !isSelected) {
            toggleSelection(card);
        } else if (!checkbox.checked && isSelected) {
            toggleSelection(card);
        }
    });
}

function updateQty(itemId, delta) {
    const qtyInput = event.target.parentElement.querySelector('.cart-qty-input');
    if (delta === -1 && parseInt(qtyInput.value) <= 1) return;
    window.location.href = `../process/shopping_cart_process.php?action=update_qty&id=${itemId}&delta=${delta}`;
}

// Single Item Deletion
function removeItem(itemId) {
    const modal = document.getElementById('deleteModal');
    const confirmBtn = document.getElementById('confirmDeleteBtn');

    document.getElementById('modalTitle').innerText = "Remove Item";
    document.getElementById('modalText').innerText = "Are you sure you want to remove this item? It cannot be recovered.";
    confirmBtn.innerText = "Remove";
    confirmBtn.href = `../process/shopping_cart_process.php?action=delete&id=${itemId}`;

    modal.style.display = 'flex';
}

// Delete SELECTED Items only — passes selectedIds to the process file
function removeAllItems() {
    if (selectedIds.length === 0) return;

    const modal = document.getElementById('deleteModal');
    const confirmBtn = document.getElementById('confirmDeleteBtn');

    const n = selectedIds.length;
    document.getElementById('modalTitle').innerText = "Remove Selected Items";
    document.getElementById('modalText').innerText = `Are you sure you want to remove ${n} selected item${n > 1 ? 's' : ''}? This action cannot be undone.`;
    confirmBtn.innerText = `Remove ${n} Item${n > 1 ? 's' : ''}`;

    // Pass the selected IDs as a JSON query param
    const idsParam = encodeURIComponent(JSON.stringify(selectedIds));
    confirmBtn.href = `../process/shopping_cart_process.php?action=delete_selected&ids=${idsParam}`;

    modal.style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target == modal) {
        closeDeleteModal();
    }
}