<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM tbl_notifications WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notification History</title>
    <style>
        .container { max-width: 600px; margin: 100px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .notif-item { padding: 15px; border-bottom: 1px solid #eee; display: flex; gap: 15px; }
        .notif-item.unread { background: #fffcf5; }
    </style>
</head>
<body style="background-color: #FFFBF0; margin: 0;">
    <?php include 'customer_header.php'; ?>
    <div class="container">
        <h2>Notification History</h2>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="notif-item <?php echo $row['is_read']==0 ? 'unread' : ''; ?>">
                    <i class="fas fa-info-circle" style="color:#795338;"></i>
                    <div>
                        <p style="margin:0;"><?php echo $row['message']; ?></p>
                        <small style="color:#999;"><?php echo $row['created_at']; ?></small>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; color:#999;">No history found.</p>
        <?php endif; ?>
    </div>
</body>
</html>