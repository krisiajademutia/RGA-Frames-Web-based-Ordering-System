<?php
// customer/customer_notifications.php
session_start();

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    header('Location: login.php');
    exit();
}

include __DIR__ . '/../includes/customer_header.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notifications - RGA Frames</title>
</head>
<body>

    <div class="notif-main-wrapper">

        <div class="notif-page-header">
            <div>
                <h1>Notifications</h1>
                <p>Keep updated on your orders.</p>
            </div>
            <button class="btn-mark-read" id="btn-mark-all-read">Mark as Read</button>
        </div>

        <div class="notif-container">
            <div id="notifications-list" class="notif-list">
                <div class="notif-loading">Loading your notifications...</div>
            </div>
        </div>

    </div>
<script src="../assets/js/customer_notif.js"></script>
</body>
</html>