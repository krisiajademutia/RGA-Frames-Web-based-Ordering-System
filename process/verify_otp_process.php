<?php
session_start();
include_once __DIR__ . '/../config/db_connect.php';
date_default_timezone_set('Asia/Manila');

// 1. Check if we have session data
if (!isset($_SESSION['reset_email'])) {
    header("Location: ../forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];
$error = '';

// 2. Identify the User ID (Because tbl_otp uses IDs, not emails)
$user_id = null;
$user_type = '';

// Check Admin
$stmt = $conn->prepare("SELECT admin_id FROM tbl_admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $user_id = $row['admin_id'];
    $user_type = 'ADMIN';
}
$stmt->close();

// Check Customer
if (!$user_id) {
    $stmt = $conn->prepare("SELECT customer_id FROM tbl_customer WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $user_id = $row['customer_id'];
        $user_type = 'CUSTOMER';
    }
    $stmt->close();
}

// 3. Verify OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $entered_otp = trim($_POST['otp'] ?? '');

    if (strlen($entered_otp) !== 6) {
        $error = 'Please enter the full 6-digit code.';
    } elseif (!$user_id) {
        $error = 'User account not found.';
    } else {
        // Prepare query based on user type (Matching your DB columns)
        if ($user_type === 'ADMIN') {
            $sql = "SELECT * FROM tbl_otp WHERE admin_id = ? AND otp_code = ? AND is_used = 0 AND expired_at > NOW()";
        } else {
            $sql = "SELECT * FROM tbl_otp WHERE customer_id = ? AND otp_code = ? AND is_used = 0 AND expired_at > NOW()";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $entered_otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Success: Mark as used
            if ($user_type === 'ADMIN') {
                $upd = $conn->prepare("UPDATE tbl_otp SET is_used = 1 WHERE admin_id = ? AND otp_code = ?");
            } else {
                $upd = $conn->prepare("UPDATE tbl_otp SET is_used = 1 WHERE customer_id = ? AND otp_code = ?");
            }
            $upd->bind_param("is", $user_id, $entered_otp);
            $upd->execute();
            
            $_SESSION['reset_verified'] = true;
            header("Location: ../reset_password.php");
            exit();
        } else {
            $error = 'Invalid or expired OTP.';
        }
    }
}

// 4. Handle Errors
if (!empty($error)) {
    $_SESSION['error'] = $error;
    header("Location: ../verify_reset_otp.php");
    exit();
}
?>