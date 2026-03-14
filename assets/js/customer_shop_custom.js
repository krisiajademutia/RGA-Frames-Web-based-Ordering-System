// assets/js/customer_shop_custom.js

const state = {
    serviceType:       null,
    frameTypeId:       null,  frameTypeName:    '',  frameTypePrice:   0,
    frameDesignId:     null,  frameDesignName:  '',  frameDesignPrice: 0,
    frameColorId:      null,  frameColorName:   '',
    frameSizeId:       null,  frameWidth:       0,   frameHeight:      0,
    primaryMatboard:   0,     primaryMatName:   '',  primaryMatPrice:  0,
    secondaryMatboard: 0,     secondaryMatName: '',  secondaryMatPrice:0,
    mountTypeId:       null,  mountName:        '',  mountPrice:       0,
    paperTypeId:       null,  paperName:        '',  paperMultiplier:  0,
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

function setPrice(elId, amount) {
    const el = document.getElementById(elId);
    if (!el) return;
    el.textContent = amount > 0
        ? '₱' + amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        : '';
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
        state.paperMultiplier = 0;
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
    setPrice('sum-frame-type-price', state.frameTypePrice);
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
const customSizeWrap = qs('#csc-custom-size-wrap');

qsa('.csc-size-pill').forEach(pill => {
    pill.addEventListener('click', () => {
        qsa('.csc-size-pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        state.frameSizeId = pill.dataset.value;

        if (pill.dataset.value === 'OTHER') {
            // Show custom fields, clear preset values
            if (customSizeWrap) customSizeWrap.style.display = 'flex';
            qs('#csc-width').value  = '';
            qs('#csc-height').value = '';
            state.frameWidth  = 0;
            state.frameHeight = 0;
        } else {
            // Hide custom fields, use preset dimensions
            if (customSizeWrap) customSizeWrap.style.display = 'none';
            const w = pill.dataset.width;
            const h = pill.dataset.height;
            if (w && h) {
                state.frameWidth  = parseFloat(w);
                state.frameHeight = parseFloat(h);
            }
        }
        updateSizeLabel();
        updateTotal();
    });
});

// ── Custom Width / Height (WITH KUYA'S SIZE LIMITS) ──────
['#csc-width', '#csc-height'].forEach(sel => {
    qs(sel)?.addEventListener('input', () => {
        let w = parseFloat(qs('#csc-width').value)  || 0;
        let h = parseFloat(qs('#csc-height').value) || 0;
        
        let maxSide = Math.max(w, h);
        let minSide = Math.min(w, h);

        // RULE 1: Global Maximum (48x96)
        if (maxSide > 96 || minSide > 48) {
            alert("Maximum frame size allowed is 48x96 inches.");
            qs('#csc-width').value = '';
            qs('#csc-height').value = '';
            w = 0; h = 0;
        }

        // RULE 2: Dynamic ₱75 Design Limit (Max 12x18)
        if (state.frameDesignPrice > 0 && state.frameDesignPrice <= 75 && (maxSide > 18 || minSide > 12)) {
            alert("Kuya's Rule: Designs in this price range (₱75) are only available for sizes up to 12x18 inches.");
            qs('#csc-width').value = '';
            qs('#csc-height').value = '';
            w = 0; h = 0;
        }

        state.frameWidth  = w;
        state.frameHeight = h;

        qsa('.csc-size-pill').forEach(p => p.classList.remove('active'));
        const otherPill = qs('.csc-size-pill[data-value="OTHER"]');
        if (otherPill) otherPill.classList.add('active');
        state.frameSizeId = 'OTHER';
        updateSizeLabel();
        updateTotal();
    });
});

function updateSizeLabel() {
    const w = state.frameWidth, h = state.frameHeight;
    if (w && h) {
        showRow('sum-size-row', 'sum-size', w + '" × ' + h + '"');
        setPrice('sum-size-price', 0); // Hide this, price is now baked into the Design calculation
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
    const pri = parseInt(state.primaryMatboard)  || 0;
    const sec = parseInt(state.secondaryMatboard) || 0;

    if (pri === 0 && sec === 0) {
        hideRow('sum-matboard-row');
    } else if (pri !== 0 && sec !== 0) {
        const matTotal = state.primaryMatPrice + state.secondaryMatPrice;
        showRow('sum-matboard-row', 'sum-matboard', state.primaryMatName + ' + ' + state.secondaryMatName);
        setPrice('sum-matboard-price', matTotal);
    } else {
        const name = pri !== 0 ? state.primaryMatName : state.secondaryMatName;
        showRow('sum-matboard-row', 'sum-matboard', name);
        setPrice('sum-matboard-price', 0);
    }
}

// ── Mount Type ───────────────────────────────────────────
initSelector('.csc-section:last-of-type .csc-service-option', card => {
    state.mountTypeId = card.dataset.value;
    state.mountPrice  = parseFloat(card.dataset.price) || 0;
    state.mountName   = card.querySelector('.csc-service-label').textContent.trim();
    showRow('sum-mount-row', 'sum-mount', state.mountName);
    setPrice('sum-mount-price', state.mountPrice);
    updateTotal();
});

// ── Paper Type ───────────────────────────────────────────
qs('#csc-paper-type')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    state.paperTypeId = this.value || null;
    state.paperMultiplier = parseFloat(opt.dataset.multiplier) || 0;
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
        // Validate it is an image
        if (!this.files[0].type.startsWith('image/')) {
            alert('Please upload a valid image file (JPG, PNG).');
            this.value = '';
            state.imageUploaded = false;
            return;
        }

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
    } else {
        state.imageUploaded = false;
        state.imageName = '';
        hideRow('sum-image-row');
        qs('#csc-image-preview').style.display = 'none';
        qs('#csc-upload-placeholder').style.display = 'flex';
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
    if (state.quantity < 1) {
        this.value = 1;
        state.quantity = 1;
    }
    updateTotal();
});

// ── THE BRAIN: Total Calculation (Kuya's Logic) ──────────
function updateTotal() {
    let frameBase = 0;
    let printBase = 0;
    let extras    = 0;

    const w = state.frameWidth || 0;
    const h = state.frameHeight || 0;

    // --- 1. FRAME MATH ---
    frameBase += state.frameTypePrice;
    
    let calculatedDesignPrice = 0;
    if (w > 0 && h > 0 && state.frameDesignPrice > 0) {
        // Kuya's Formula: ((W + H) / 6) * Frame Design Price
        calculatedDesignPrice = ((w + h) / 6) * state.frameDesignPrice;
        frameBase += calculatedDesignPrice;
        
        // Show Base Price in the label, but Calculated Price in the amount column!
        const designLabel = `${state.frameDesignName} (Base: ₱${state.frameDesignPrice.toFixed(2)})`;
        showRow('sum-design-row', 'sum-design', designLabel);
        setPrice('sum-design-price', calculatedDesignPrice); 
    } else {
        // If they haven't typed a size yet, just show the normal name and 0 price
        showRow('sum-design-row', 'sum-design', state.frameDesignName);
        setPrice('sum-design-price', 0);
    }

    // --- 2. EXTRAS MATH (Matboards & Mounts) ---
    const priId = parseInt(state.primaryMatboard)  || 0;
    const secId = parseInt(state.secondaryMatboard) || 0;
    if (priId > 0 || secId > 0) {
        extras += state.primaryMatPrice;
        extras += state.secondaryMatPrice;
    }
    extras += state.mountPrice;

    // --- 3. PRINT MATH ---
    if (state.serviceType === 'FRAME_PRINT' && w > 0 && h > 0 && state.paperTypeId) {
        let isFixed = false;
        
        // Step A: Check Menu (Fixed Prices)
        if (typeof CSC_DATA !== 'undefined' && CSC_DATA.fixedPrintPrices) {
            const matchedPackage = CSC_DATA.fixedPrintPrices.find(
                f => f.paper_id == state.paperTypeId && f.width == w && f.height == h
            );
            if (matchedPackage) {
                printBase = matchedPackage.price;
                isFixed = true;
                // Tell them it's a fixed package price
                showRow('sum-paper-row', 'sum-paper', `${state.paperName} (Fixed Package)`);
            }
        }

        // Step B: Custom Math (W x H x Multiplier)
        if (!isFixed) {
            printBase = (w * h) * state.paperMultiplier;
            // Tell them the multiplier rate!
            showRow('sum-paper-row', 'sum-paper', `${state.paperName} (₱${state.paperMultiplier.toFixed(2)}/sq.in)`);
        }
        
        setPrice('sum-paper-price', printBase); 
    } else if (state.serviceType === 'FRAME_PRINT' && state.paperTypeId) {
        showRow('sum-paper-row', 'sum-paper', state.paperName);
        setPrice('sum-paper-price', 0);
    } else {
        setPrice('sum-paper-price', 0);
    }

    // --- 4. GRAND TOTAL ---
    const unitTotal  = frameBase + extras + printBase;
    let grandTotal = unitTotal * state.quantity;

    // Apply Kuya's 20% Discount for Photographers & Loyal Customers!
    if (typeof HAS_DISCOUNT !== 'undefined' && HAS_DISCOUNT) {
        const discountAmount = grandTotal * 0.20;
        grandTotal = grandTotal - discountAmount;
        
        // Show a cool label so they know they got 20% off
        const serviceLabel = state.serviceType === 'FRAME_PRINT' ? 'Frame & Print' : 'Frame only';
        showRow('sum-service-row', 'sum-service', `${serviceLabel} (⭐ 20% LOYALTY DISCOUNT!)`);
    }

    qs('#csc-total').textContent = '₱' + grandTotal.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// ── Form Submission ──────────────────────────────────────
// Add to Cart
async function submitAddToCart() {
    if (state.serviceType === 'FRAME_PRINT' && !state.imageUploaded) {
        showToast('Please upload an image for Frame & Print!', 'error'); return;
    }
    const fd = new FormData();
    fd.append('action',           'add_to_cart');
    fd.append('service_type',     state.serviceType === 'FRAME_PRINT' ? 'FRAME&PRINT' : 'FRAME_ONLY');
    fd.append('frame_type_id',    state.frameTypeId    ?? '');
    fd.append('frame_design_id',  state.frameDesignId  ?? '');
    fd.append('frame_color_id',   state.frameColorId   ?? '');
    fd.append('frame_size_id',    state.frameSizeId    ?? 'OTHER');
    fd.append('custom_width',     state.frameWidth     ?? 0);
    fd.append('custom_height',    state.frameHeight    ?? 0);
    fd.append('primary_matboard',   state.primaryMatboard   ?? 0);
    fd.append('secondary_matboard', state.secondaryMatboard ?? 0);
    fd.append('mount_type_id',    state.mountTypeId    ?? '');
    fd.append('paper_type_id',    state.paperTypeId    ?? '');
    fd.append('quantity',         state.quantity);
    const img = qs('#csc-image-input');
    if (img && img.files[0]) fd.append('customer_image', img.files[0]);

    const cartBtn = qs('#csc-add-to-cart');
    const buyBtn  = qs('#csc-buy-now');
    if (cartBtn) { cartBtn.disabled = true; cartBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...'; }
    if (buyBtn)  buyBtn.disabled = true;
    try {
        const res    = await fetch('../process/custom_frame_process.php', { method: 'POST', body: fd });
        const result = await res.json();
        if (result.success) showToast('Frame added to your cart!', 'success');
        else showToast(result.message || 'Something went wrong.', 'error');
    } catch (e) { showToast('Network error. Please try again.', 'error'); }
    finally {
        if (cartBtn) { cartBtn.disabled = false; cartBtn.innerHTML = '<i class="fas fa-cart-shopping"></i> Add to Cart'; }
        if (buyBtn)  buyBtn.disabled = false;
    }
}

// Buy Now - store in session then go to checkout
async function submitBuyNow() {
    if (state.serviceType === 'FRAME_PRINT' && !state.imageUploaded) {
        showToast('Please upload an image for Frame & Print!', 'error'); return;
    }
    if (!state.frameTypeId || !state.frameDesignId || !state.frameColorId) {
        showToast('Please complete your frame selection first.', 'error'); return;
    }
    if (!state.frameWidth || !state.frameHeight) {
        showToast('Please select or enter a frame size.', 'error'); return;
    }
    const fd = new FormData();
    fd.append('action',           'buy_now');
    fd.append('service_type',     state.serviceType === 'FRAME_PRINT' ? 'FRAME&PRINT' : 'FRAME_ONLY');
    fd.append('frame_type_id',    state.frameTypeId    ?? '');
    fd.append('frame_design_id',  state.frameDesignId  ?? '');
    fd.append('frame_color_id',   state.frameColorId   ?? '');
    fd.append('frame_size_id',    state.frameSizeId    ?? 'OTHER');
    fd.append('custom_width',     state.frameWidth     ?? 0);
    fd.append('custom_height',    state.frameHeight    ?? 0);
    fd.append('primary_matboard',   state.primaryMatboard   ?? 0);
    fd.append('secondary_matboard', state.secondaryMatboard ?? 0);
    fd.append('mount_type_id',    state.mountTypeId    ?? '');
    fd.append('paper_type_id',    state.paperTypeId    ?? '');
    fd.append('quantity',         state.quantity);
    const img = qs('#csc-image-input');
    if (img && img.files[0]) fd.append('customer_image', img.files[0]);

    const buyBtn = qs('#csc-buy-now');
    const cartBtn = qs('#csc-add-to-cart');
    if (buyBtn)  { buyBtn.disabled = true; buyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...'; }
    if (cartBtn) cartBtn.disabled = true;
    try {
        const res    = await fetch('../process/custom_frame_process.php', { method: 'POST', body: fd });
        const result = await res.json();
        if (result.success) {
            window.location.href = 'customer_checkout.php';
        } else {
            showToast(result.message || 'Something went wrong.', 'error');
            if (buyBtn)  { buyBtn.disabled = false; buyBtn.textContent = 'Buy Now'; }
            if (cartBtn) cartBtn.disabled = false;
        }
    } catch (e) {
        showToast('Network error. Please try again.', 'error');
        if (buyBtn)  { buyBtn.disabled = false; buyBtn.textContent = 'Buy Now'; }
        if (cartBtn) cartBtn.disabled = false;
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
qs('#csc-add-to-cart')?.addEventListener('click', (e) => {
    e.preventDefault();
    submitAddToCart();
});

qs('#csc-buy-now')?.addEventListener('click', (e) => {
    e.preventDefault();
    submitBuyNow();
});


qs('#csc-buy-now')?.addEventListener('click', (e) => {
    e.preventDefault(); // This stops the page from reloading!
    submitForm('buy_now');
});

// ── Lightbox ─────────────────────────────────────────────
let lbImages = [];
let lbIndex  = 0;

function openLightbox(images, name, startIndex) {
    lbImages = images;
    lbIndex  = startIndex || 0;
    qs('#csc-lightbox-name').textContent = name;
    renderLightboxImage();
    qs('#csc-lightbox').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function renderLightboxImage() {
    qs('#csc-lightbox-img').src = lbImages[lbIndex];
    const total = lbImages.length;
    qs('#csc-lightbox-counter').textContent = total > 1 ? (lbIndex + 1) + ' / ' + total : '';
    qs('#csc-lightbox-prev')?.classList.toggle('hidden', lbIndex === 0);
    qs('#csc-lightbox-next')?.classList.toggle('hidden', lbIndex === total - 1);
}

function closeLightbox() {
    qs('#csc-lightbox').style.display = 'none';
    document.body.style.overflow = '';
    lbImages = [];
}

document.addEventListener('click', function(e) {
    const wrap = e.target.closest('.csc-design-img-wrap');
    if (wrap) {
        e.preventDefault();
        e.stopPropagation();
        const images = JSON.parse(wrap.dataset.images || '[]');
        const name   = wrap.dataset.name || '';
        if (images.length) openLightbox(images, name, 0);
    }
});

qs('#csc-lightbox-prev')?.addEventListener('click', function(e) {
    e.stopPropagation();
    if (lbIndex > 0) { lbIndex--; renderLightboxImage(); }
});

qs('#csc-lightbox-next')?.addEventListener('click', function(e) {
    e.stopPropagation();
    if (lbIndex < lbImages.length - 1) { lbIndex++; renderLightboxImage(); }
});

qs('#csc-lightbox-close')?.addEventListener('click', closeLightbox);
qs('#csc-lightbox-backdrop')?.addEventListener('click', closeLightbox);

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft'  && lbImages.length) { if (lbIndex > 0) { lbIndex--; renderLightboxImage(); } }
    if (e.key === 'ArrowRight' && lbImages.length) { if (lbIndex < lbImages.length - 1) { lbIndex++; renderLightboxImage(); } }
});