// assets/js/customer_shop_custom.js

const state = {
    serviceType:       null,
    frameTypeId:       null,  frameTypeName:    '',  frameTypePrice:   0,
    frameDesignId:     null,  frameDesignName:  '',  frameDesignPrice: 0,
    frameColorId:      null,  frameColorName:   '',
    frameSizeId:       null,  frameWidth:       0,   frameHeight:      0,  frameSizePrice: 0,
    primaryMatboard:   0,     primaryMatName:   '',  primaryMatPrice:  0,
    secondaryMatboard: 0,     secondaryMatName: '',  secondaryMatPrice:0,
    mountTypeId:       null,  mountName:        '',  mountPrice:       0,
    paperTypeId:       null,  paperName:        '',  paperPrice:       0,  paperLogic: 'FIXED',
    imageUploaded:     false, imageName:        '',
    quantity:          1,
};

// ── Helpers ──────────────────────────────────────────────
function qs(sel)  { return document.querySelector(sel); }
function qsa(sel) { return document.querySelectorAll(sel); }

function showRow(rowId, valId, text) {
    const row = qs('#' + rowId);
    const val = qs('#' + valId);
    if (!row || !val) return;
    val.textContent = text;
    row.style.display = '';
}

function hideRow(rowId) {
    const row = qs('#' + rowId);
    if (row) row.style.display = 'none';
}

// ── Radio-style selectors ────────────────────────────────
function initSelector(cardSelector, onSelect) {
    qsa(cardSelector).forEach(card => {
        card.addEventListener('click', () => {
            qsa(cardSelector).forEach(c => c.classList.remove('selected', 'active'));
            card.classList.add('selected', 'active');
            const input = card.querySelector('input');
            if (input) input.checked = true;
            onSelect(card);
        });
    });
}

// ── Service Type ─────────────────────────────────────────
initSelector('.csc-service-option[data-value="FRAME_ONLY"], .csc-service-option[data-value="FRAME_PRINT"]', card => {
    state.serviceType = card.dataset.value;
    const isPrint = state.serviceType === 'FRAME_PRINT';

    qs('#csc-print-section').style.display = isPrint ? '' : 'none';

    if (!isPrint) {
        hideRow('sum-image-row');
        hideRow('sum-paper-row');
        state.paperTypeId   = null;
        state.paperPrice    = 0;
        state.imageUploaded = false;
        state.imageName     = '';
    } else {
        if (state.imageUploaded) showRow('sum-image-row', 'sum-image', state.imageName);
        if (state.paperTypeId)   showRow('sum-paper-row', 'sum-paper', state.paperName);
    }

    showRow('sum-service-row', 'sum-service', isPrint ? 'Frame & Print' : 'Frame only');
    updateTotal();
});

// ── Frame Type ───────────────────────────────────────────
initSelector('.csc-type-option', card => {
    state.frameTypeId    = card.dataset.value;
    state.frameTypePrice = parseFloat(card.dataset.price) || 0;
    state.frameTypeName  = card.querySelector('.csc-type-name').textContent.trim();
    showRow('sum-frame-type-row', 'sum-frame-type', state.frameTypeName);
    updateTotal();
});

// ── Frame Design ─────────────────────────────────────────
initSelector('.csc-design-card', card => {
    state.frameDesignId    = card.dataset.value;
    state.frameDesignPrice = parseFloat(card.dataset.price) || 0;
    state.frameDesignName  = card.querySelector('.csc-design-name').textContent.trim();
    showRow('sum-design-row', 'sum-design', state.frameDesignName);
    updateTotal();
});

// ── Frame Color ──────────────────────────────────────────
initSelector('.csc-color-card', card => {
    state.frameColorId   = card.dataset.value;
    state.frameColorName = card.querySelector('.csc-color-name').textContent.trim();
    showRow('sum-color-row', 'sum-color', state.frameColorName);
    updateTotal();
});

// ── Frame Size Pills ─────────────────────────────────────
qsa('.csc-size-pill').forEach(pill => {
    pill.addEventListener('click', () => {
        qsa('.csc-size-pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        state.frameSizeId    = pill.dataset.value;
        state.frameSizePrice = parseFloat(pill.dataset.price) || 0;
        const w = pill.dataset.width;
        const h = pill.dataset.height;
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
['#csc-width', '#csc-height'].forEach(sel => {
    qs(sel)?.addEventListener('input', () => {
        state.frameWidth  = parseFloat(qs('#csc-width').value)  || 0;
        state.frameHeight = parseFloat(qs('#csc-height').value) || 0;
        qsa('.csc-size-pill').forEach(p => p.classList.remove('active'));
        const otherPill = qs('.csc-size-pill[data-value="OTHER"]');
        if (otherPill) otherPill.classList.add('active');
        state.frameSizeId    = 'OTHER';
        state.frameSizePrice = 0;
        updateSizeLabel();
        updateTotal();
    });
});

function updateSizeLabel() {
    const w = state.frameWidth, h = state.frameHeight;
    if (w && h) {
        showRow('sum-size-row', 'sum-size', w + '" × ' + h + '"');
    } else {
        hideRow('sum-size-row');
    }
}

// ── Primary Matboard ─────────────────────────────────────
initSelector('#csc-primary-matboard .csc-matboard-card', card => {
    state.primaryMatboard = card.dataset.value;
    state.primaryMatPrice = parseFloat(card.dataset.price) || 0;
    state.primaryMatName  = card.querySelector('span').textContent.trim();
    updateMatboardSummary();
    updateTotal();
});

// ── Secondary Matboard ───────────────────────────────────
initSelector('#csc-secondary-matboard .csc-matboard-card', card => {
    state.secondaryMatboard = card.dataset.value;
    state.secondaryMatPrice = parseFloat(card.dataset.price) || 0;
    state.secondaryMatName  = card.querySelector('span').textContent.trim();
    updateMatboardSummary();
    updateTotal();
});

function updateMatboardSummary() {
    const pri = state.primaryMatboard;
    const sec = state.secondaryMatboard;

    if (pri == 0 && sec == 0) {
        hideRow('sum-matboard-row');
    } else if (pri != 0 && sec != 0) {
        showRow('sum-matboard-row', 'sum-matboard', state.primaryMatName + ' + ' + state.secondaryMatName);
    } else if (pri != 0) {
        showRow('sum-matboard-row', 'sum-matboard', state.primaryMatName);
    } else {
        showRow('sum-matboard-row', 'sum-matboard', state.secondaryMatName);
    }
}

// ── Mount Type ───────────────────────────────────────────
initSelector('.csc-section:last-of-type .csc-service-option', card => {
    state.mountTypeId = card.dataset.value;
    state.mountPrice  = parseFloat(card.dataset.price) || 0;
    state.mountName   = card.querySelector('.csc-service-label').textContent.trim();
    showRow('sum-mount-row', 'sum-mount', state.mountName);
    updateTotal();
});

// ── Paper Type ───────────────────────────────────────────
qs('#csc-paper-type')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    state.paperTypeId = this.value || null;
    state.paperPrice  = parseFloat(opt.dataset.price) || 0;
    state.paperLogic  = opt.dataset.logic || 'FIXED';
    state.paperName   = opt.textContent.trim();
    if (this.value) {
        showRow('sum-paper-row', 'sum-paper', state.paperName);
    } else {
        hideRow('sum-paper-row');
    }
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
        const name = this.files[0].name;
        state.imageName = name.length > 22 ? name.substring(0, 22) + '...' : name;
        showRow('sum-image-row', 'sum-image', state.imageName);
    }
});

// ── Quantity ─────────────────────────────────────────────
qs('#csc-qty-minus')?.addEventListener('click', () => {
    const input = qs('#csc-qty');
    if (parseInt(input.value) > 1) {
        input.value--;
        state.quantity = parseInt(input.value);
        updateTotal();
    }
});
qs('#csc-qty-plus')?.addEventListener('click', () => {
    const input = qs('#csc-qty');
    input.value++;
    state.quantity = parseInt(input.value);
    updateTotal();
});
qs('#csc-qty')?.addEventListener('input', function() {
    state.quantity = parseInt(this.value) || 1;
    updateTotal();
});

// ── Total Calculation ────────────────────────────────────
function updateTotal() {
    let base = 0;
    base += state.frameTypePrice;
    base += state.frameDesignPrice;

    if (state.frameSizeId && state.frameSizeId !== 'OTHER') {
        base += state.frameSizePrice;
    } else if (state.frameWidth && state.frameHeight) {
        const totalInch = (state.frameWidth * 2) + (state.frameHeight * 2);
        base += totalInch * 10;
    }

    base += state.primaryMatPrice;
    base += state.secondaryMatPrice;
    base += state.mountPrice;

    if (state.serviceType === 'FRAME_PRINT') {
        base += state.paperPrice;
    }

    const total = base * state.quantity;
    qs('#csc-total').textContent = '₱' + total.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// ── Form Submission ──────────────────────────────────────
async function submitForm(action) {
    const formData = new FormData();
    formData.append('action',             action);
    formData.append('service_type',       state.serviceType === 'FRAME_PRINT' ? 'FRAME&PRINT' : 'FRAME_ONLY');
    formData.append('frame_type_id',      state.frameTypeId    ?? '');
    formData.append('frame_design_id',    state.frameDesignId  ?? '');
    formData.append('frame_color_id',     state.frameColorId   ?? '');
    formData.append('frame_size_id',      state.frameSizeId    ?? 'OTHER');
    formData.append('custom_width',       state.frameWidth     ?? 0);
    formData.append('custom_height',      state.frameHeight    ?? 0);
    formData.append('primary_matboard',   state.primaryMatboard   ?? 0);
    formData.append('secondary_matboard', state.secondaryMatboard ?? 0);
    formData.append('mount_type_id',      state.mountTypeId    ?? '');
    formData.append('paper_type_id',      state.paperTypeId    ?? '');
    formData.append('quantity',           state.quantity);
    formData.append('payment_method',     'CASH');
    formData.append('delivery_option',    'PICKUP');
    formData.append('delivery_address',   '');

    const imageInput = qs('#csc-image-input');
    if (imageInput && imageInput.files[0]) {
        formData.append('customer_image', imageInput.files[0]);
    }

    const cartBtn = qs('#csc-add-to-cart');
    const buyBtn  = qs('#csc-buy-now');
    cartBtn.disabled = buyBtn.disabled = true;
    cartBtn.textContent = buyBtn.textContent = 'Processing...';

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
                window.location.href = 'customer_my_orders.php?new_order=' + result.order_id;
            }
        } else {
            showToast(result.message || 'Something went wrong.', 'error');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        cartBtn.disabled = buyBtn.disabled = false;
        cartBtn.innerHTML = '<i class="fas fa-cart-shopping"></i> Add to Cart';
        buyBtn.textContent = 'Buy Now';
    }
}

// ── Toast ────────────────────────────────────────────────
function showToast(message, type = 'success') {
    const existing = qs('#csc-toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.id = 'csc-toast';
    toast.style.cssText = [
        'position:fixed', 'bottom:2rem', 'right:2rem', 'z-index:9999',
        'background:' + (type === 'success' ? '#0F473A' : '#ef4444'),
        'color:#fff', 'padding:0.85rem 1.5rem', 'border-radius:10px',
        'font-size:0.9rem', 'font-weight:600',
        'box-shadow:0 4px 16px rgba(0,0,0,0.15)',
        'animation:fadeInUp 0.3s ease'
    ].join(';');
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

// ── Button Handlers ──────────────────────────────────────
qs('#csc-add-to-cart')?.addEventListener('click', () => submitForm('add_to_cart'));
qs('#csc-buy-now')?.addEventListener('click',     () => submitForm('buy_now'));

// ── Lightbox ─────────────────────────────────────────────
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.csc-design-zoom-btn');
    if (btn) {
        e.preventDefault();
        e.stopPropagation();
        qs('#csc-lightbox-img').src   = btn.dataset.img;
        qs('#csc-lightbox-name').textContent = btn.dataset.name;
        qs('#csc-lightbox').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
});

function closeLightbox() {
    qs('#csc-lightbox').style.display = 'none';
    document.body.style.overflow = '';
}

qs('#csc-lightbox-close')?.addEventListener('click', closeLightbox);
qs('#csc-lightbox-backdrop')?.addEventListener('click', closeLightbox);

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
});