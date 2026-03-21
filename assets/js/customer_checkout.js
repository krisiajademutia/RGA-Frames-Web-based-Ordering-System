/**
 * customer_checkout.js
 * Location: assets/js/customer_checkout.js
 *
 * PHP injects these globals before this script loads:
 *   const discountedSubtotal = <?= $discountedTotal ?>;   // subtotal after discount, before delivery
 *   const deliveryUnlocked   = true|false;                // whether 30+ frames threshold is met
 */

const DELIVERY_FEE = 150;

function recalcTotal(withDelivery) {
    const total = discountedSubtotal + (withDelivery ? DELIVERY_FEE : 0);
    document.getElementById('summary-total').textContent =
        '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const feeRow = document.getElementById('delivery-fee-row');
    if (feeRow) feeRow.style.display = withDelivery ? 'flex' : 'none';
}

function onDeliveryChange(radio) {
    document.getElementById('lbl-pickup').classList.remove('selected');
    document.getElementById('lbl-delivery').classList.remove('selected');
    radio.closest('label').classList.add('selected');

    const isDelivery = radio.value === 'DELIVERY';
    document.getElementById('address_wrapper').style.display = isDelivery ? 'block' : 'none';
    recalcTotal(isDelivery);
}

function onPaymentChange(radio) {
    document.getElementById('lbl-cash').classList.remove('selected');
    document.getElementById('lbl-gcash').classList.remove('selected');
    radio.closest('label').classList.add('selected');
    document.getElementById('gcash_wrapper').style.display = radio.value === 'GCASH' ? 'block' : 'none';
}

function onReceiptSelected(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('receipt-dropzone').style.display = 'none';
        document.getElementById('receipt-preview-img').src = e.target.result;
        document.getElementById('receipt-preview').style.display = 'block';
        document.getElementById('receipt-change-btn').style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
}

function openReceiptLightbox() {
    document.getElementById('chk-lightbox-img').src =
        document.getElementById('receipt-preview-img').src;
    document.getElementById('chk-lightbox').classList.add('open');
}

function closeReceiptLightbox() {
    document.getElementById('chk-lightbox').classList.remove('open');
}

// Close lightbox on backdrop click
document.getElementById('chk-lightbox')?.addEventListener('click', e => {
    if (e.target.id === 'chk-lightbox') closeReceiptLightbox();
});

// Form submission
document.getElementById('checkout-form').addEventListener('submit', async e => {
    e.preventDefault();

    const delivery = document.querySelector('input[name="delivery_option"]:checked').value;
    const payment  = document.querySelector('input[name="payment_method"]:checked').value;

    if (delivery === 'DELIVERY' && !deliveryUnlocked) {
        Swal.fire('Not Available', 'Delivery requires a minimum of 30 frames.', 'warning');
        return;
    }
    if (delivery === 'DELIVERY' && !document.getElementById('delivery_address').value.trim()) {
        Swal.fire('Missing Address', 'Please enter your delivery address.', 'warning');
        return;
    }
    if (payment === 'GCASH' && !document.getElementById('receipt_file').files.length) {
        Swal.fire('Missing Receipt', 'Please upload your GCash receipt.', 'warning');
        return;
    }

    const btn     = document.getElementById('btn-place-order');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    try {
        const res  = await fetch('../process/checkout_process.php', {
            method: 'POST',
            body:   new FormData(e.target)
        });
        const data = await res.json();

        if (data.success) {
          // Check which payment method they actually used
            const selectedPayment = document.querySelector('input[name="payment_method"]:checked').value;

            if (selectedPayment === 'GCASH') {
                // ─── GCASH SUCCESS MESSAGE ───
                Swal.fire({
                    title: '<span style="font-size: 24px;">Order Placed! 🎉</span>',
                    html: '<style>.swal2-icon { transform: scale(0.7); margin: 0 auto 5px auto !important; }</style>' + 
                          '<p style="font-size: 15px; color: #555; margin-bottom: 15px;">Your order and payment receipt have been received.</p>' +
                          '<div style="font-size: 13.5px; background: #fff3cd; padding: 10px 15px; border-radius: 5px; border-left: 4px solid #d9534f; text-align: left; line-height: 1.4;">' +
                          '<span style="color: #d9534f; font-weight: bold;">Note:</span> Your balance updates automatically once our staff verifies the payment.</div>',
                    icon: 'success',
                    iconColor: '#0f3d33',
                    confirmButtonText: 'Got it',
                    confirmButtonColor: '#0f3d33',
                    allowOutsideClick: false,
                    width: '450px',
                    padding: '1.25em 1.5em 1.5em 1.5em'
                }).then(() => { window.location.href = 'customer_orders.php'; });
                
            } else {
                // ─── CASH SUCCESS MESSAGE ───
                Swal.fire({
                    title: '<span style="font-size: 24px;">Order Placed! 🎉</span>',
                    html: '<style>.swal2-icon { transform: scale(0.7); margin: 0 auto 5px auto !important; }</style>' + 
                          '<p style="font-size: 15px; color: #555; margin-bottom: 15px;">Your order has been successfully submitted.</p>' +
                          '<div style="font-size: 13.5px; background: #e2eaec; padding: 10px 15px; border-radius: 5px; border-left: 4px solid #0f3d33; text-align: left; line-height: 1.4;">' +
                          '<span style="color: #0f3d33; font-weight: bold;">Note:</span> Please prepare the exact cash amount for your payment upon claiming or delivery.</div>',
                    icon: 'success',
                    iconColor: '#0f3d33',
                    confirmButtonText: 'Got it',
                    confirmButtonColor: '#0f3d33',
                    allowOutsideClick: false,
                    width: '450px',
                    padding: '1.25em 1.5em 1.5em 1.5em'
                }).then(() => { window.location.href = 'customer_orders.php'; });
            }
        } else {
            Swal.fire('Error', data.message, 'error');
            btn.disabled  = false;
            btn.innerHTML = 'Place Order';
        }
    } catch (err) {
        Swal.fire('Error', 'Network error. Please try again.', 'error');
        btn.disabled  = false;
        btn.innerHTML = 'Place Order';
    }
});