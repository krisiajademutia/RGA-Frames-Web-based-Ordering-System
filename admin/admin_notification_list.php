<?php
// admin_notification_list.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include __DIR__ . '/../includes/admin_header.php'; // This is safe now because the header doesn't include THIS file anymore.
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notification History</title>
    <style>
        /* Use a container similar to the customer history page */
        .container { max-width: 800px; margin: 120px auto 40px; padding: 30px; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h2 { font-family: 'Inter', sans-serif; color: #1e293b; }
    </style>
</head>
<body style="background-color: #f8fafc; margin: 0;">
    <div class="container">
        <h2>Notification History</h2>
        
        <?php
        // GROUP MEMBERS WORKSPACE:
        // They will write the SAME database logic here to show the FULL history.
        $has_notifications = false; 
        
        if (!$has_notifications): ?>
            <p style="text-align:center; color:#999; padding: 40px;">No history found.</p>
        <?php else: ?>
            <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>