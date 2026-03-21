<?php
// process/read_notification.php
session_start();
include __DIR__ . '/../config/db_connect.php';

// 1. Get the IDs from the URL that we clicked
$notif_id = (int)($_GET['notif_id'] ?? 0);
$order_id = (int)($_GET['order_id'] ?? 0);
$role     = strtoupper($_SESSION['role'] ?? '');

// 2. Mark this specific notification as read
if ($notif_id > 0) {
    // ⚠️ IMPORTANT: Check your database! Is the primary key named 'id' or 'notification_id'? 
    // Change "id = ?" below if your column is named something else!
    $stmt = $conn->prepare("UPDATE tbl_notifications SET is_read = 1 WHERE id = ?");
    
    if ($stmt) {
        $stmt->bind_param("i", $notif_id);
        $stmt->execute();
    }
}

// 3. Redirect the user to the correct order page
if ($order_id > 0) {
    if ($role === 'ADMIN') {
        // ⚠️ Change this to the exact name of your Admin view order page!
        header("Location: ../admin_order_details.php?id=" . $order_id); 
    } else {
        // ⚠️ Change this to the exact name of your Customer view order page!
        header("Location: ../customer_order_details.php?id=" . $order_id); 
    }
} else {
    // If there is no order ID (like a general system alert), just send them back to the page they were on
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
}
exit();
?>