// assets/js/customer_order_details.js

/* ── Modal helpers ── */
function openProofViewer() {
    document.getElementById('cst-proof-modal').style.display = 'flex';
}

function openUploadModal() {
    document.getElementById('cst-upload-modal').style.display = 'flex';
}

function closeUploadModal() {
    document.getElementById('cst-upload-modal').style.display = 'none';
}

function openDocViewer(src, label) {
    document.getElementById('cst-doc-modal-img').src           = src;
    document.getElementById('cst-doc-modal-label').textContent = label;
    document.getElementById('cst-doc-modal').style.display     = 'flex';
}

/* ── File preview ── */
function previewFile(input) {
    if (!input.files?.[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('cst-upload-preview-img').src        = e.target.result;
        document.getElementById('cst-upload-preview').style.display  = 'block';
        document.getElementById('cst-upload-dropzone').style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}

/* ── Drag & drop ── */
document.addEventListener('DOMContentLoaded', () => {

    // Backdrop click — close modals
    ['cst-proof-modal', 'cst-doc-modal', 'cst-upload-modal'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', e => {
            if (e.target.id === id) e.target.style.display = 'none';
        });
    });

    // Dropzone drag & drop
    const dz = document.getElementById('cst-upload-dropzone');
    if (dz) {
        dz.addEventListener('dragover', e => {
            e.preventDefault();
            dz.classList.add('dragging');
        });
        dz.addEventListener('dragleave', () => {
            dz.classList.remove('dragging');
        });
        dz.addEventListener('drop', e => {
            e.preventDefault();
            dz.classList.remove('dragging');
            const file = e.dataTransfer.files[0];
            if (file) {
                const dt  = new DataTransfer();
                dt.items.add(file);
                const inp = document.getElementById('cst-upload-file');
                inp.files = dt.files;
                previewFile(inp);
            }
        });
    }
});

/* ── Submit GCash receipt ── */
async function submitReceipt() {
    const amount = parseFloat(document.getElementById('cst-upload-amount').value);
    const file   = document.getElementById('cst-upload-file').files[0];

    if (!amount || amount <= 0) {
        Swal.fire('Missing Amount', 'Please enter the amount on your receipt.', 'warning');
        return;
    }
    if (!file) {
        Swal.fire('No File', 'Please select your receipt image.', 'warning');
        return;
    }

    const fd = new FormData();
    fd.append('order_id',        ORDER_ID);
    fd.append('payment_id',      PAYMENT_ID);
    fd.append('uploaded_amount', amount);
    fd.append('receipt',         file);

    const btn     = document.querySelector('.cst-upload-submit-btn');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

    try {
        const res  = await fetch('../process/upload_receipt.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Receipt Uploaded!',
                text: 'We will verify your receipt shortly.',
                confirmButtonColor: '#0f3d33'
            }).then(() => location.reload());
        } else {
            Swal.fire('Error', data.message || 'Something went wrong.', 'error');
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-upload"></i> Submit Receipt';
        }
    } catch (e) {
        Swal.fire('Error', 'Network error. Please try again.', 'error');
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-upload"></i> Submit Receipt';
    }
}

/* ── Cancel order ── */
function confirmCancel() {
    Swal.fire({
        title: 'Cancel this order?',
        text: 'This action cannot be undone. Your order will be marked as cancelled.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor:  '#6b7280',
        confirmButtonText:  'Yes, cancel it',
        cancelButtonText:   'No, keep it'
    }).then(async result => {
        if (!result.isConfirmed) return;

        const btn     = document.querySelector('.cst-ord-dtls-cancel-btn');
        btn.disabled  = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';

        const fd = new FormData();
        fd.append('order_id', ORDER_ID);

        try {
            const res  = await fetch('../process/cancel_order.php', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Order Cancelled',
                    text: data.message,
                    confirmButtonColor: '#0f3d33'
                }).then(() => {
                    window.location.href = 'customer_orders.php?status=CANCELLED';
                });
            } else {
                Swal.fire('Cannot Cancel', data.message, 'error');
                btn.disabled  = false;
                btn.innerHTML = '<i class="fas fa-times-circle"></i> Cancel Order';
            }
        } catch (e) {
            Swal.fire('Error', 'Network error. Please try again.', 'error');
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-times-circle"></i> Cancel Order';
        }
    });
}