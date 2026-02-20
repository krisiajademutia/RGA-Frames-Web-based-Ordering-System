<?php
session_start();
include __DIR__ . '/../config/db_connect.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) { 
    header("Location: ../login.php"); 
    exit(); 
}

$current_user_id = $_SESSION['user_id'];

// 2. Fetch Notifications
$sql = "SELECT * FROM tbl_notifications WHERE customer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

// 🔴 MOVE CLOSE HERE: Close it as soon as we have the $result set
$stmt->close(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notification History - RGA Frames</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        body { background-color: #fffdf7 !important; }
        .notif-card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .notif-item { padding: 1.25rem; border-bottom: 1px solid #f0f0f0; transition: background 0.2s ease; }
        .notif-item:last-child { border-bottom: none; }
        .notif-item.unread { background-color: #fdf8ef; border-left: 4px solid #c19a5f; }
        .notif-icon { font-size: 1.25rem; color: #4a2c18; margin-top: 3px; }
    </style>
</head>
<body style="padding-top: 100px;"> 
    
    <?php include '../includes/customer_header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <h2 class="mb-4" style="color: #4a2c18; font-weight: 700;">
                    <i class="fas fa-bell me-2"></i>Notifications
                </h2>

                <div class="card notif-card">
                    <div class="card-body p-0">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <div class="notif-item d-flex gap-3 <?php echo ($row['is_read'] == 0) ? 'unread' : ''; ?>">
                                    <div class="notif-icon">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1" style="color: #1a0f09;">
                                            <?php echo htmlspecialchars($row['message']); ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1"></i>
                                            <?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="p-5 text-center">
                                <i class="fas fa-bell-slash fa-3x mb-3 text-muted"></i>
                                <p class="text-muted">No notifications found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>