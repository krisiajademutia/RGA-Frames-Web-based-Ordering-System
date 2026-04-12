let selectedTotal = 0;
let selectedIds   = [];

/* ─── Sync toolbar count + checkbox state ─── */
function updateToolbarCount() {
    const countEl = document.getElementById('toolbar-count');
    if (countEl) {
        const n = selectedIds.length;
        countEl.innerText = n === 0 ? '0 items selected' : `${n} item${n > 1 ? 's' : ''} selected`;
    }
    const allCards    = document.querySelectorAll('.cart-item-card');
    const selectAllCb = document.getElementById('select-all-checkbox');
    if (selectAllCb && allCards.length > 0) {
        selectAllCb.checked       = selectedIds.length === allCards.length;
        selectAllCb.indeterminate = selectedIds.length > 0 && selectedIds.length < allCards.length;
    }
}

/* ─── Sync the summary sidebar and checkout button ─── */
function syncSidebar() {
    const totalDisplay = document.getElementById('running-total');
    const hiddenInput  = document.getElementById('selected-items-input');
    const checkoutBtn  = document.getElementById('checkout-btn');
    const emptyMsg     = document.getElementById('empty-summary-msg');

    if (totalDisplay) {
        const displayValue = Math.max(0, selectedTotal);
        totalDisplay.innerText = '₱' + displayValue.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    if (hiddenInput)  hiddenInput.value    = JSON.stringify(selectedIds);
    if (checkoutBtn)  checkoutBtn.disabled = selectedIds.length === 0;
    if (emptyMsg)     emptyMsg.style.display = selectedIds.length === 0 ? 'block' : 'none';
}

/* ─── Select / deselect a single card ─── */
function toggleSelection(element) {
    // Always resolve to the actual .cart-item-card regardless of which
    // child element was clicked — prevents missed clicks on nested text/icons
    const card = element.closest('.cart-item-card');
    if (!card) return;

    const id          = card.getAttribute('data-id');
    const price       = parseFloat(card.getAttribute('data-price'));
    const summaryLine = document.getElementById(`summary-item-${id}`);

    if (card.classList.contains('selected')) {
        card.classList.remove('selected');
        if (summaryLine) summaryLine.style.display = 'none';
        selectedTotal -= price;
        selectedIds    = selectedIds.filter(item => item !== id);
    } else {
        card.classList.add('selected');
        if (summaryLine) summaryLine.style.display = 'block';
        selectedTotal += price;
        selectedIds.push(id);
    }

    syncSidebar();
    updateToolbarCount();
}

/* ─── Select all / deselect all ─── */
function toggleSelectAll(checkbox) {
    const allCards = document.querySelectorAll('.cart-item-card');

    selectedTotal = 0;
    selectedIds   = [];

    allCards.forEach(card => {
        const id          = card.getAttribute('data-id');
        const price       = parseFloat(card.getAttribute('data-price'));
        const summaryLine = document.getElementById(`summary-item-${id}`);

        if (checkbox.checked) {
            card.classList.add('selected');
            if (summaryLine) summaryLine.style.display = 'block';
            selectedTotal += price;
            selectedIds.push(id);
        } else {
            card.classList.remove('selected');
            if (summaryLine) summaryLine.style.display = 'none';
        }
    });

    syncSidebar();
    updateToolbarCount();
}

/* ─── Quantity update ─── */
function updateQty(type, itemId, delta, btn) {
    const qtyInput = btn.parentElement.querySelector('.cart-qty-input');
    if (delta === -1 && parseInt(qtyInput.value) <= 1) return;
    window.location.href = `../process/shopping_cart_process.php?action=update_qty&type=${type}&id=${itemId}&delta=${delta}`;
}

/* ─── Single item removal modal ─── */
function removeItem(type, itemId) {
    const modal      = document.getElementById('deleteModal');
    const confirmBtn = document.getElementById('confirmDeleteBtn');

    document.getElementById('modalTitle').innerText = "Remove Item";
    document.getElementById('modalText').innerText  = "Are you sure you want to remove this item? It cannot be recovered.";
    confirmBtn.innerText = "Remove";

    if (type === 'print') {
        confirmBtn.href = `../process/shopping_cart_process.php?action=delete_print&id=${itemId}`;
    } else {
        confirmBtn.href = `../process/shopping_cart_process.php?action=delete&id=${itemId}`;
    }

    modal.style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

/* ─── Clear selected items — dedicated modal ─── */
function removeAllItems() {
    if (selectedIds.length === 0) {
        const modal      = document.getElementById('clearSelectedModal');
        const confirmBtn = document.getElementById('confirmClearBtn');
        document.getElementById('clearModalTitle').innerText = 'No Items Selected';
        document.getElementById('clearModalText').innerText  = 'Please select at least one item to remove.';
        confirmBtn.style.display = 'none';
        modal.style.display = 'flex';
        return;
    }

    const modal      = document.getElementById('clearSelectedModal');
    const confirmBtn = document.getElementById('confirmClearBtn');
    confirmBtn.style.display = '';
    const n = selectedIds.length;

    document.getElementById('clearModalTitle').innerText = `Remove ${n} Selected Item${n > 1 ? 's' : ''}`;
    document.getElementById('clearModalText').innerText  =
        `Are you sure you want to remove ${n} selected item${n > 1 ? 's' : ''}? This action cannot be undone.`;
    confirmBtn.innerText = `Remove ${n} Item${n > 1 ? 's' : ''}`;

    const idsParam  = encodeURIComponent(JSON.stringify(selectedIds));
    confirmBtn.href = `../process/shopping_cart_process.php?action=delete_selected&ids=${idsParam}`;

    modal.style.display = 'flex';
}

function closeClearModal() {
    document.getElementById('clearSelectedModal').style.display = 'none';
}

/* ─── Close modals when clicking outside ─── */
window.onclick = function (event) {
    const deleteModal = document.getElementById('deleteModal');
    const clearModal  = document.getElementById('clearSelectedModal');
    if (event.target === deleteModal) closeDeleteModal();
    if (event.target === clearModal)  closeClearModal();
};

/* ─── Inline expand / collapse details ─── */
function toggleDetails(itemId, btn) {
    const panel  = document.getElementById(`cart-details-${itemId}`);
    const isOpen = panel.classList.contains('cart-item-expanded--open');

    if (isOpen) {
        panel.classList.remove('cart-item-expanded--open');
        btn.innerHTML = '<i class="fa-solid fa-chevron-down cart-details-chevron"></i> View details';
    } else {
        panel.classList.add('cart-item-expanded--open');
        btn.innerHTML = '<i class="fa-solid fa-chevron-up cart-details-chevron cart-details-chevron--open"></i> Hide details';
    }
}

/* ─── Prevent double-submit on checkout ─── */
document.addEventListener('DOMContentLoaded', function () {
    const checkoutForm = document.querySelector('form[action*="save_selected"]');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            const btn = document.getElementById('checkout-btn');
            // Guard: if no items selected, block submission entirely
            if (selectedIds.length === 0) {
                e.preventDefault();
                return;
            }
            // Write latest selectedIds into hidden input before form data is collected
            const hiddenInput = document.getElementById('selected-items-input');
            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(selectedIds);
            }
            // Disable button after a tick to prevent double-submit without blocking submission
            if (btn) {
                setTimeout(() => { btn.disabled = true; }, 100);
            }
        });
    }
});