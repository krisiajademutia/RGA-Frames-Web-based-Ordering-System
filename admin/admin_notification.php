<?php
// admin/admin_notifications.php
session_start();

// Security check: Only Admins allowed here
if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'ADMIN') {
    header('Location: login.php'); 
    exit();
}
include __DIR__ . '/../includes/admin_header.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notifications - RGA Frames</title>
    <!-- All CSS links are already in admin_header.php, so we don't repeat them -->
</head>
<body>

    <div class="container my-5" style="max-width: 900px;">
        <h2 class="mb-4">Admin Notifications</h2>
        
        <div id="notifications-list" class="list-group shadow-sm rounded">
            <div class="text-center py-5 text-muted">Loading your notifications...</div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const listContainer = document.getElementById('notifications-list');

        fetch('../process/fetch_admin_notifications.php')
            .then(response => response.json())
            .then(data => {
                listContainer.innerHTML = '';

                if (!data.success || data.notifications.length === 0) {
                    listContainer.innerHTML = '<div style="padding: 30px; text-align: center; color: #888;">No notifications yet. You are all caught up!</div>';
                    return;
                }

                data.notifications.forEach(notif => {
                    let bg = notif.is_read == 0 ? '#f0f8ff' : '#ffffff'; 
                    let fw = notif.is_read == 0 ? 'bold' : 'normal';

                    listContainer.innerHTML += `
                        <div class="notification-item" 
                            data-notif-id="${notif.notification_id}"
                            data-order-id="${notif.order_id || 0}"
                            style="padding: 20px; border-bottom: 1px solid #eee; background-color: ${bg}; cursor: pointer;"
                            onmouseover="this.style.backgroundColor='${notif.is_read == 0 ? '#e3f2fd' : '#f5f5f5'}'"
                            onmouseout="this.style.backgroundColor='${bg}'">
                            <div style="font-weight: ${fw}; font-size: 16px; margin-bottom: 8px; color: #333;">${notif.title}</div>
                            <div style="color: #555; font-size: 14px; line-height: 1.5;">${notif.message}</div>
                            <div style="color: #aaa; font-size: 12px; margin-top: 10px;">
                                ${notif.created_at ? new Date(notif.created_at).toLocaleString() : 'Recently'}
                            </div>
                        </div>
                    `;
                });

                listContainer.addEventListener('click', function(e) {
                    let item = e.target.closest('.notification-item');
                    if (!item) return;

                    const notifId  = item.dataset.notifId;
                    const orderId  = parseInt(item.dataset.orderId) || 0;

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
                listContainer.innerHTML = '<div style="padding: 30px; text-align: center; color: red;">Failed to load notifications.</div>';
            });

        setTimeout(() => {
            fetch('../process/mark_admin_notif_read.php');
        }, 1000); 
    });
    </script>

    <!-- If you need Bootstrap JS and it's not already in the header -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>