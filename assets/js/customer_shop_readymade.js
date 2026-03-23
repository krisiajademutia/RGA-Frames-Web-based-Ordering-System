// assets/js/customer_shop_readymade.js

let basePrice = 0, pMatPrice = 0, sMatPrice = 0, mountPrice = 0;
let productWidth = 0, productHeight = 0;
const productModal = document.getElementById('productDetailsModal');

productModal.addEventListener('show.bs.modal', function (event) {
    const button  = event.relatedTarget;
    const product = JSON.parse(button.getAttribute('data-product'));

    basePrice     = parseFloat(product.product_price) || 0;
    productWidth  = parseFloat(product.width)         || 0;
    productHeight = parseFloat(product.height)        || 0;

    document.getElementById('hiddenProductWidth').value  = productWidth;
    document.getElementById('hiddenProductHeight').value = productHeight;
    document.getElementById('modalProductId').value      = product.r_product_id;
    document.getElementById('modalProductName').innerText = product.product_name;
    document.getElementById('modalProductImg').src        = "/rga_frames/uploads/" + (product.image_name || '');
    document.getElementById('specSize').innerText         = `${productWidth}x${productHeight}"`;
    document.getElementById('specDesign').innerText       = product.design_name || '—';
    document.getElementById('specColor').innerText        = product.color_name  || '—';
    document.getElementById('specStock').innerText        = `${product.stock} available`;
    document.getElementById('modalQtyInput').max          = product.stock;
    document.getElementById('modalQtyInput').value        = 1;

    // Reset tile selections to first active
    document.querySelectorAll('#addToCartForm .cust-rdymd-tile-group').forEach(group =>
        group.querySelectorAll('.cust-rdymd-option-tile').forEach((t, i) =>
            t.classList.toggle('active', i === 0)
        )
    );

    // Reset swatch selections to first active
    document.querySelectorAll('#addToCartForm .cust-rdymd-swatch-group').forEach(group =>
        group.querySelectorAll('.cust-rdymd-swatch-item').forEach((s, i) =>
            s.classList.toggle('active', i === 0)
        )
    );

    document.getElementById('oos-banner')?.remove();
    document.getElementById('cust-rdymd-upload-section').style.display          = 'none';
    document.getElementById('cust-rdymd-paper-type-section').style.display      = 'none';
    document.getElementById('cust-rdymd-secondary-mat-section').style.display   = 'none';
    document.getElementById('cust-rdymd-image-preview-container').style.display = 'none';
    document.getElementById('cust-rdymd-image-input').value = '';

    document.getElementById('selectedService').value         = 'FRAME_ONLY';
    document.getElementById('selectedPaperId').value         = '';
    document.getElementById('hiddenCurrentMultiplier').value = 0;
    document.getElementById('selectedMatId').value           = '';
    document.getElementById('selectedSecondaryMatId').value  = '';

    // Set default mount
    const defaultMount = document.querySelector('#addToCartForm [data-mount-id]');
    if (defaultMount) {
        document.getElementById('selectedMountId').value = defaultMount.getAttribute('data-mount-id');
        mountPrice = parseFloat(defaultMount.getAttribute('data-price')) || 0;
    }

    // Reset mat prices
    pMatPrice = 0;
    sMatPrice = 0;

    // Out of stock logic
    const isOutOfStock = parseInt(product.stock) <= 0;
    if (isOutOfStock) {
        const div = document.createElement('div');
        div.id = 'oos-banner';
        div.style.cssText = 'background:#fee2e2;color:#991b1b;border:1.5px solid #fca5a5;border-radius:10px;padding:0.6rem 1rem;font-size:0.875rem;font-weight:700;display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;';
        div.innerHTML = '<i class="fas fa-ban"></i> This item is currently out of stock and cannot be ordered.';
        document.querySelector('.cust-rdymd-btn-actions-row').before(div);
    }
    document.querySelectorAll('.cust-rdymd-btn-add-cart, .cust-rdymd-btn-buy-now').forEach(b => {
        b.disabled      = isOutOfStock;
        b.style.cssText = isOutOfStock ? 'opacity:0.45; cursor:not-allowed;' : '';
        b.title         = isOutOfStock ? 'This item is out of stock' : '';
    });

    // Canvas availability check (below 12x18 rule)
    document.querySelectorAll('#cust-rdymd-paper-type-section .cust-rdymd-option-tile').forEach(tile => {
        const pName = tile.querySelector('strong').innerText.toLowerCase();
        if (pName.includes('canvas')) {
            const isBelowRule = (productWidth < 12 || productHeight < 18) &&
                                (productWidth < 18 || productHeight < 12);
            tile.style.display = isBelowRule ? 'none' : 'block';
        }
    });

    updateTotalPriceDisplay();
});

function selectService(el, service) {
    el.closest('.cust-rdymd-tile-group')
      .querySelectorAll('.cust-rdymd-option-tile')
      .forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('selectedService').value = service;
    const showOptions = service === 'FRAME&PRINT';
    document.getElementById('cust-rdymd-upload-section').style.display     = showOptions ? 'block' : 'none';
    document.getElementById('cust-rdymd-paper-type-section').style.display = showOptions ? 'block' : 'none';
    updateTotalPriceDisplay();
}

function selectPaper(el) {
    el.closest('.cust-rdymd-tile-group')
      .querySelectorAll('.cust-rdymd-option-tile')
      .forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('selectedPaperId').value          = el.getAttribute('data-paper-id');
    document.getElementById('hiddenCurrentMultiplier').value  = el.getAttribute('data-multiplier') || 0;
    updateTotalPriceDisplay();
}

function selectMat(el, id = null, isNone = false) {
    el.closest('.cust-rdymd-swatch-group')
      .querySelectorAll('.cust-rdymd-swatch-item')
      .forEach(s => s.classList.remove('active'));
    el.classList.add('active');
    pMatPrice = 0; // Primary mat alone is free — fee only on double-matting
    document.getElementById('selectedMatId').value = isNone ? '' : el.getAttribute('data-mat-id');

    const secSec = document.getElementById('cust-rdymd-secondary-mat-section');
    secSec.style.display = isNone ? 'none' : 'block';

    if (isNone) {
        sMatPrice = 0;
        document.getElementById('selectedSecondaryMatId').value = '';
        secSec.querySelectorAll('.cust-rdymd-swatch-item').forEach(s => s.classList.remove('active'));
        secSec.querySelector('.cust-rdymd-swatch-item:first-child').classList.add('active');
    }
    updateTotalPriceDisplay();
}

function selectSecondaryMat(el, id = null) {
    el.closest('.cust-rdymd-swatch-group')
      .querySelectorAll('.cust-rdymd-swatch-item')
      .forEach(s => s.classList.remove('active'));
    el.classList.add('active');
    sMatPrice = el ? parseFloat(el.getAttribute('data-price')) || 0 : 0;
    document.getElementById('selectedSecondaryMatId').value = el ? el.getAttribute('data-mat-id') : '';
    updateTotalPriceDisplay();
}

function selectMount(el) {
    el.closest('.cust-rdymd-tile-group')
      .querySelectorAll('.cust-rdymd-option-tile')
      .forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    mountPrice = el ? parseFloat(el.getAttribute('data-price')) || 0 : 0;
    document.getElementById('selectedMountId').value = el ? el.getAttribute('data-mount-id') : '';
    updateTotalPriceDisplay();
}

function updateTotalPriceDisplay() {
    const qty        = parseInt(document.getElementById('modalQtyInput').value) || 1;
    const service    = document.getElementById('selectedService').value;
    const multiplier = parseFloat(document.getElementById('hiddenCurrentMultiplier').value) || 0;
    let printPricePerItem = 0;

    if (service === 'FRAME&PRINT' && multiplier > 0) {
        printPricePerItem = (productWidth * productHeight) * multiplier;
    }

    const frameTotalPerItem = basePrice + pMatPrice + sMatPrice + mountPrice;
    const totalPrice        = (frameTotalPerItem + printPricePerItem) * qty;

    document.getElementById('modalProductPrice').innerText = '₱ ' + totalPrice.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function adjustQty(val) {
    const input  = document.getElementById('modalQtyInput');
    const max    = parseInt(input.getAttribute('max')) || 99;
    const newVal = parseInt(input.value) + val;
    if (newVal >= 1 && newVal <= max) {
        input.value = newVal;
        updateTotalPriceDisplay();
    }
}

function previewUserImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('cust-rdymd-image-preview').src             = e.target.result;
            document.getElementById('cust-rdymd-image-preview-container').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function showToast(message, type = 'success') {
    const wrap  = document.getElementById('cust-rdymd-toast-wrap');
    const toast = document.createElement('div');
    toast.className   = 'cust-rdymd-toast ' + type;
    toast.textContent = message;
    wrap.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 350);
    }, 3200);
}

function handleReadyMadeSubmit(actionType) {
    const service = document.getElementById('selectedService').value;
    if (service === 'FRAME&PRINT') {
        const imgInput = document.getElementById('cust-rdymd-image-input');
        if (!imgInput.files || imgInput.files.length === 0) {
            showToast('⚠️ Please upload an image.', 'error');
            return;
        }
    }

    const btn     = actionType === 'buy_now'
        ? document.querySelector('.cust-rdymd-btn-buy-now')
        : document.querySelector('.cust-rdymd-btn-add-cart');
    const oldText = btn.textContent;
    btn.disabled  = true;
    btn.textContent = actionType === 'buy_now' ? 'Please wait…' : 'Adding…';

    const formData = new FormData(document.getElementById('addToCartForm'));
    formData.append('action',               actionType);
    formData.append('r_product_id',          document.getElementById('modalProductId').value);
    formData.append('quantity',              document.getElementById('modalQtyInput').value);
    formData.append('service_type',          document.getElementById('selectedService').value);
    formData.append('primary_matboard_id',   document.getElementById('selectedMatId').value);
    formData.append('secondary_matboard_id', document.getElementById('selectedSecondaryMatId').value);
    formData.append('mount_type_id',         document.getElementById('selectedMountId').value);
    formData.append('paper_type_id',         document.getElementById('selectedPaperId').value);

    fetch('../process/ready_made_process.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (actionType === 'buy_now') {
                window.location.href = data.redirect;
            } else {
                showToast('✓ ' + (data.message || 'Added to cart!'), 'success');
                bootstrap.Modal.getInstance(productModal).hide();
            }
        } else {
            showToast(data.message || 'Error occurred.', 'error');
        }
    })
    .catch(() => showToast('Network error. Please try again.', 'error'))
    .finally(() => {
        btn.disabled    = false;
        btn.textContent = oldText;
    });
}

productModal.addEventListener('hidden.bs.modal', function () {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = 'auto';
});