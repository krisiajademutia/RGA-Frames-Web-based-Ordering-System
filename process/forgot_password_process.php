<?php
session_start();
date_default_timezone_set('Asia/Manila'); 

include_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../config/config.php'; 
include_once __DIR__ . '/../includes/email_functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_otp'])) {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM tbl_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = 'No account found with that email.';
        } else {
            $otp = sprintf("%06d", mt_rand(0, 999999));
            $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $purpose = 'reset_password';

            $delete_stmt = $conn->prepare("DELETE FROM tbl_otp WHERE email = ? AND purpose = ?");
            $delete_stmt->bind_param("ss", $email, $purpose);
            $delete_stmt->execute();
            $delete_stmt->close();

            $stmt = $conn->prepare("INSERT INTO tbl_otp (email, otp_code, purpose, expires_at, is_used) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("ssss", $email, $otp, $purpose, $expires_at);
            
            if ($stmt->execute()) {
                $_SESSION['reset_email'] = $email;
                if (sendOTP($email, $otp, $purpose)) {
                    $_SESSION['success'] = 'OTP sent! Check your email.';
                    header("Location: ../verify_reset_otp.php"); 
                    exit();
                } else {
                    $error = 'Failed to send OTP. Check SMTP settings.';
                }
            } else {
                $error = 'Database error. Please try again.';
            }
        }
        $stmt->close();
    }
}
?>