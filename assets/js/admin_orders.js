// ── Live Search ──
const searchInput = document.getElementById('admn-ordr-search-input');
const orderCards  = document.querySelectorAll('.admn-ordr-card');

if (searchInput) {
    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        orderCards.forEach(card => {
            const data = card.getAttribute('data-search') || '';
            card.style.display = data.includes(q) ? '' : 'none';
        });
    });
}

// ── Today Button ──
document.getElementById('admn-ordr-today-btn')?.addEventListener('click', function () {
    const params = new URLSearchParams(window.location.search);
    params.set('date', 'today');
    params.delete('filterDate');
    window.location.href = '?' + params.toString();
});

// ── Apply Filter Modal ──
document.getElementById('admn-ordr-apply-filter')?.addEventListener('click', function () {
    const form     = document.getElementById('admn-ordr-filter-form');
    if (!form) return;
    const formData = new FormData(form);
    const params   = new URLSearchParams(window.location.search);

    const selectedStatus = formData.get('status');
    if (selectedStatus) params.set('status', selectedStatus);

    const filterDate = formData.get('filterDate');
    if (filterDate) {
        params.set('filterDate', filterDate);
        params.delete('date');
    } else {
        params.delete('filterDate');
    }

    window.location.href = '?' + params.toString();
});