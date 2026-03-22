function getIconForTitle(title) {
        const t = (title || '').toLowerCase();
        if (t.includes('pickup'))
            return { cls: 'notif-icon-pickup', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="#7c6fc4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>' };
        if (t.includes('completed'))
            return { cls: 'notif-icon-completed', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>' };
        if (t.includes('delivery') || t.includes('out for'))
            return { cls: 'notif-icon-delivery', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v4h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>' };
        if (t.includes('payment'))
            return { cls: 'notif-icon-payment', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' };
        if (t.includes('promo') || t.includes('alert'))
            return { cls: 'notif-icon-promo', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>' };
        if (t.includes('order') || t.includes('new'))
            return { cls: 'notif-icon-order', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>' };
        return { cls: 'notif-icon-default', svg: '<svg viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' };
    }

    document.addEventListener('DOMContentLoaded', function() {
        const listContainer = document.getElementById('notifications-list');

        fetch('../process/fetch_admin_notifications.php')
            .then(response => response.json())
            .then(data => {
                listContainer.innerHTML = '';

                if (!data.success || data.notifications.length === 0) {
                    listContainer.innerHTML = '<div class="notif-empty">No notifications yet. You are all caught up!</div>';
                    return;
                }

                data.notifications.forEach(notif => {
                    const isUnread = notif.is_read == 0;
                    const icon = getIconForTitle(notif.title);

                    const card = document.createElement('div');
                    card.className = 'notif-card ' + (isUnread ? 'unread' : 'read');
                    card.dataset.notifId = notif.notification_id;
                    card.dataset.orderId = notif.order_id || 0;

                    card.innerHTML = `
                        <div class="notif-icon-wrap ${icon.cls}">${icon.svg}</div>
                        <div class="notif-body">
                            <div class="notif-title">${notif.title}</div>
                            <div class="notif-message">${notif.message}</div>
                            <div class="notif-time">${notif.created_at ? new Date(notif.created_at).toLocaleString() : 'Recently'}</div>
                        </div>
                    `;

                    listContainer.appendChild(card);
                });

                listContainer.addEventListener('click', function(e) {
                    let item = e.target.closest('.notif-card');
                    if (!item) return;

                    const notifId = item.dataset.notifId;
                    const orderId = parseInt(item.dataset.orderId) || 0;

                    if (notifId > 0) {
                        fetch(`../process/mark_single_notification_read.php?notif_id=${notifId}`)
                            .catch(err => console.warn("Could not mark as read", err));
                    }

                    if (orderId > 0) {
                        // For admin page → always go to admin order details
                        window.location.href = `admin_order_details.php?id=${orderId}`;
                    }
                });
            })
            .catch(err => {
                console.error('Error fetching notifications:', err);
                listContainer.innerHTML = '<div class="notif-error">Failed to load notifications.</div>';
            });

        setTimeout(() => {
            fetch('../process/mark_admin_notif_read.php');
        }, 1000);

        document.getElementById('btn-mark-all-read').addEventListener('click', function() {
            fetch('../process/mark_admin_notif_read.php')
                .then(() => {
                    document.querySelectorAll('.notif-card.unread').forEach(card => {
                        card.classList.remove('unread');
                        card.classList.add('read');
                    });
                })
                .catch(err => console.warn('Could not mark all as read', err));
        });
    });