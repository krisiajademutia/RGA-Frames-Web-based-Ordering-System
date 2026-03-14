/**
 * customer_checkout.js
 * Location: assets/js/customer_checkout.js
 *
 * Depends on `subtotal` (number) being declared by PHP before this script loads:
 *   <script>const subtotal = <?= $cartTotal ?>;</script>
 *   <script src="../assets/js/customer_checkout.js"></script>
 */

function onDeliveryChange(radio) {
    document.getElementById('lbl-pickup').classList.remove('selected');
    document.getElementById('lbl-delivery').classList.remove('selected');
    radio.closest('label').classList.add('selected');

    const isDelivery = radio.value === 'DELIVERY';
    document.getElementById('address_wrapper').style.display = isDelivery ? 'block' : 'none';

    const fee   = isDelivery ? 150 : 0;
    const total = subtotal + fee;
    document.getElementById('summary-total').textContent =
        '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
            Swal.fire({
                icon: 'success',
                title: 'Order Placed!',
                text: data.message,
                confirmButtonColor: '#0f3d33'
            }).then(() => { window.location.href = 'customer_orders.php'; });
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