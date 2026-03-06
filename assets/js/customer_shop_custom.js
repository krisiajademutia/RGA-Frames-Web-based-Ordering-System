// assets/js/customer_shop_custom.js

const state = {
    serviceType:      'FRAME_ONLY',
    frameTypeId:      null,  frameTypePrice:   0,
    frameDesignId:    null,  frameDesignPrice: 0,
    frameColorId:     null,
    frameSizeId:      'OTHER', frameWidth: 0, frameHeight: 0, frameSizePrice: 0,
    primaryMatboard:  0,     primaryMatPrice:  0,
    secondaryMatboard:0,     secondaryMatPrice:0,
    mountTypeId:      null,  mountPrice:       0,
    paperTypeId:      null,  paperPrice:       0,
    paperLogic:       'FIXED',
    imageUploaded:    false,
    quantity:         1,
};

// ── Helpers ──────────────────────────────────────────────
function qs(sel)    { return document.querySelector(sel); }
function qsa(sel)   { return document.querySelectorAll(sel); }
function setMuted(el, text)  { el.textContent = text; el.className = 'csc-summary-muted'; }
function setVal(el, text)    { el.textContent = text; el.className = 'csc-summary-val'; }

// ── Radio-style selectors ────────────────────────────────
function initSelector(cardSelector, onSelect) {
    qsa(cardSelector).forEach(card => {
        card.addEventListener('click', () => {
            qsa(cardSelector).forEach(c => c.classList.remove('selected', 'active'));
            card.classList.add('selected', 'active');
            const input = card.querySelector('input');
            if (input) { input.checked = true; }
            onSelect(card);
        });
    });
}

// ── Service Type ─────────────────────────────────────────
initSelector('.csc-service-option[data-value="FRAME_ONLY"], .csc-service-option[data-value="FRAME_PRINT"]', card => {
    state.serviceType = card.dataset.value;
    const printSection  = qs('#csc-print-section');
    const imageRow      = qs('#sum-image-row');
    const paperRow      = qs('#sum-paper-row');
    const isPrint       = state.serviceType === 'FRAME_PRINT';
    printSection.style.display  = isPrint ? '' : 'none';
    imageRow.style.display      = isPrint ? '' : 'none';
    paperRow.style.display      = isPrint ? '' : 'none';
    setVal(qs('#sum-service'), isPrint ? 'Frame & Print' : 'Frame only');
    updateTotal();
});

// ── Frame Type ───────────────────────────────────────────
initSelector('.csc-type-option', card => {
    state.frameTypeId    = card.dataset.value;
    state.frameTypePrice = parseFloat(card.dataset.price) || 0;
    setVal(qs('#sum-frame-type'), card.querySelector('.csc-type-name').textContent);
    updateTotal();
});

// ── Frame Design ─────────────────────────────────────────
initSelector('.csc-design-card', card => {
    state.frameDesignId    = card.dataset.value;
    state.frameDesignPrice = parseFloat(card.dataset.price) || 0;
    setVal(qs('#sum-design'), card.querySelector('.csc-design-name').textContent);
    updateTotal();
});

// ── Frame Color ──────────────────────────────────────────
initSelector('.csc-color-card', card => {
    state.frameColorId = card.dataset.value;
    setVal(qs('#sum-color'), card.querySelector('.csc-color-name').textContent);
    updateTotal();
});

// ── Frame Size Pills ─────────────────────────────────────
qsa('.csc-size-pill').forEach(pill => {
    pill.addEventListener('click', () => {
        qsa('.csc-size-pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        const input = pill.querySelector('input');
        if (input) input.checked = true;

        const w = pill.dataset.width;
        const h = pill.dataset.height;
        state.frameSizeId    = pill.dataset.value;
        state.frameSizePrice = parseFloat(pill.dataset.price) || 0;

        if (pill.dataset.value !== 'OTHER' && w && h) {
            qs('#csc-width').value  = w;
            qs('#csc-height').value = h;
            state.frameWidth  = parseFloat(w);
            state.frameHeight = parseFloat(h);
        }
        updateSizeLabel();
        updateTotal();
    });
});

// ── Custom Width / Height ────────────────────────────────
['#csc-width','#csc-height'].forEach(sel => {
    qs(sel)?.addEventListener('input', () => {
        state.frameWidth  = parseFloat(qs('#csc-width').value)  || 0;
        state.frameHeight = parseFloat(qs('#csc-height').value) || 0;
        // Deselect preset pills if user types custom
        qsa('.csc-size-pill').forEach(p => p.classList.remove('active'));
        const otherPill = document.querySelector('.csc-size-pill[data-value="OTHER"]');
        if (otherPill) otherPill.classList.add('active');
        state.frameSizeId    = 'OTHER';
        state.frameSizePrice = 0;
        updateSizeLabel();
        updateTotal();
    });
});

function updateSizeLabel() {
    const w = state.frameWidth, h = state.frameHeight;
    if (w && h) setVal(qs('#sum-size'), `${w}" × ${h}"`);
    else if (state.frameSizeId !== 'OTHER') setVal(qs('#sum-size'), state.frameSizeId);
    else setMuted(qs('#sum-size'), 'not selected');
}

// ── Primary Matboard ─────────────────────────────────────
initSelector('#csc-primary-matboard .csc-matboard-card', card => {
    state.primaryMatboard = card.dataset.value;
    state.primaryMatPrice = parseFloat(card.dataset.price) || 0;
    const label = card.querySelector('span').textContent;
    state.primaryMatboard == 0
        ? setMuted(qs('#sum-matboard'), 'not selected')
        : setVal(qs('#sum-matboard'), label);
    updateTotal();
});

// ── Secondary Matboard ───────────────────────────────────
initSelector('#csc-secondary-matboard .csc-matboard-card', card => {
    state.secondaryMatboard = card.dataset.value;
    state.secondaryMatPrice = parseFloat(card.dataset.price) || 0;
    updateTotal();
});

// ── Mount Type ───────────────────────────────────────────
initSelector('.csc-section:last-of-type .csc-service-option', card => {
    state.mountTypeId = card.dataset.value;
    state.mountPrice  = parseFloat(card.dataset.price) || 0;
    setVal(qs('#sum-mount'), card.querySelector('.csc-service-label').textContent);
    updateTotal();
});

// ── Paper Type ───────────────────────────────────────────
qs('#csc-paper-type')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    state.paperTypeId = this.value;
    state.paperPrice  = parseFloat(opt.dataset.price) || 0;
    state.paperLogic  = opt.dataset.logic || 'FIXED';
    const label = opt.textContent.trim();
    this.value
        ? setVal(qs('#sum-paper'), label)
        : setMuted(qs('#sum-paper'), 'not selected');
    updateTotal();
});

// ── Image Upload ─────────────────────────────────────────
qs('#csc-image-input')?.addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            qs('#csc-image-preview').src = e.target.result;
            qs('#csc-image-preview').style.display = 'block';
            qs('#csc-upload-placeholder').style.display = 'none';
        };
        reader.readAsDataURL(this.files[0]);
        state.imageUploaded = true;
        setVal(qs('#sum-image'), this.files[0].name.substring(0, 20) + '...');
    }
});

// ── Quantity ─────────────────────────────────────────────
qs('#csc-qty-minus')?.addEventListener('click', () => {
    const input = qs('#csc-qty');
    if (parseInt(input.value) > 1) { input.value--; state.quantity = parseInt(input.value); updateTotal(); }
});
qs('#csc-qty-plus')?.addEventListener('click', () => {
    const input = qs('#csc-qty');
    input.value++; state.quantity = parseInt(input.value); updateTotal();
});
qs('#csc-qty')?.addEventListener('input', function() {
    state.quantity = parseInt(this.value) || 1;
    updateTotal();
});

// ── Total Calculation ────────────────────────────────────
function updateTotal() {
    let base = 0;

    // Frame type price
    base += state.frameTypePrice;

    // Frame design price
    base += state.frameDesignPrice;

    // Size price (preset) or calculated (total inches × rate)
    if (state.frameSizeId !== 'OTHER') {
        base += state.frameSizePrice;
    } else if (state.frameWidth && state.frameHeight) {
        // Simple per-inch calculation — adjust rate as needed
        const totalInch = (state.frameWidth * 2) + (state.frameHeight * 2);
        base += totalInch * 10; // ₱10 per inch example
    }

    // Matboard
    base += state.primaryMatPrice;
    base += state.secondaryMatPrice;

    // Mount
    base += state.mountPrice;

    // Print
    if (state.serviceType === 'FRAME_PRINT') {
        base += state.paperPrice;
    }

    const total = base * state.quantity;
    qs('#csc-total').textContent = '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Init
updateTotal();

// ── Form Submission ──────────────────────────────────────
async function submitForm(action) {
    const formData = new FormData();

    // Action
    formData.append('action', action);

    // All state fields
    formData.append('service_type',           state.serviceType === 'FRAME_PRINT' ? 'FRAME&PRINT' : 'FRAME_ONLY');
    formData.append('frame_type_id',          state.frameTypeId    ?? '');
    formData.append('frame_design_id',        state.frameDesignId  ?? '');
    formData.append('frame_color_id',         state.frameColorId   ?? '');
    formData.append('frame_size_id',          state.frameSizeId    ?? 'OTHER');
    formData.append('custom_width',           state.frameWidth     ?? 0);
    formData.append('custom_height',          state.frameHeight    ?? 0);
    formData.append('primary_matboard',       state.primaryMatboard   ?? 0);
    formData.append('secondary_matboard',     state.secondaryMatboard ?? 0);
    formData.append('mount_type_id',          state.mountTypeId    ?? '');
    formData.append('paper_type_id',          state.paperTypeId    ?? '');
    formData.append('quantity',               state.quantity);

    // For buy_now: payment + delivery (use defaults, can extend with a modal)
    formData.append('payment_method',  'CASH');
    formData.append('delivery_option', 'PICKUP');
    formData.append('delivery_address', '');

    // Image file
    const imageInput = document.getElementById('csc-image-input');
    if (imageInput && imageInput.files[0]) {
        formData.append('customer_image', imageInput.files[0]);
    }

    // Disable buttons while submitting
    const cartBtn = document.getElementById('csc-add-to-cart');
    const buyBtn  = document.getElementById('csc-buy-now');
    cartBtn.disabled = true;
    buyBtn.disabled  = true;
    cartBtn.textContent = 'Processing...';
    buyBtn.textContent  = 'Processing...';

    try {
        const response = await fetch('../process/custom_frame_process.php', {
            method: 'POST',
            body:   formData,
        });

        const result = await response.json();

        if (result.success) {
            if (action === 'add_to_cart') {
                showToast('Added to cart successfully!', 'success');
            } else {
                // Redirect to order confirmation or orders page
                window.location.href = `customer_my_orders.php?new_order=${result.order_id}`;
            }
        } else {
            showToast(result.message || 'Something went wrong.', 'error');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        cartBtn.disabled = false;
        buyBtn.disabled  = false;
        cartBtn.innerHTML = '<i class="fas fa-cart-shopping"></i> Add to Cart';
        buyBtn.textContent = 'Buy Now';
    }
}

// ── Toast notification ───────────────────────────────────
function showToast(message, type = 'success') {
    const existing = document.getElementById('csc-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'csc-toast';
    toast.style.cssText = `
        position: fixed; bottom: 2rem; right: 2rem; z-index: 9999;
        background: ${type === 'success' ? '#0F473A' : '#ef4444'};
        color: #fff; padding: 0.85rem 1.5rem; border-radius: 10px;
        font-size: 0.9rem; font-weight: 600;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        animation: fadeInUp 0.3s ease;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

// ── Button click handlers ────────────────────────────────
document.getElementById('csc-add-to-cart')?.addEventListener('click', () => submitForm('add_to_cart'));
document.getElementById('csc-buy-now')?.addEventListener('click',     () => submitForm('buy_now'));