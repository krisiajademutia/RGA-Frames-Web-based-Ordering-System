<?php
session_start();
date_default_timezone_set('Asia/Manila'); 

include_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../config/config.php'; 
include_once __DIR__ . '/../includes/email_functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_otp'])) {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $user_id = null;
        $user_type = '';

        // 1. Check ADMIN Table
        $stmt = $conn->prepare("SELECT admin_id FROM tbl_admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $user_id = $row['admin_id'];
            $user_type = 'ADMIN';
        }
        $stmt->close();

        // 2. Check CUSTOMER Table (if not found in Admin)
        if (!$user_id) {
            $stmt = $conn->prepare("SELECT customer_id FROM tbl_customer WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $user_id = $row['customer_id'];
                $user_type = 'CUSTOMER';
            }
            $stmt->close();
        }

        if (!$user_id) {
            $error = 'No account found with that email.';
        } else {
            // Generate 6-digit OTP
            $otp = sprintf("%06d", mt_rand(0, 999999));
            $expired_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            // 3. Insert into tbl_otp using the correct ID column
            if ($user_type === 'ADMIN') {
                // Clear old OTPs for this admin
                $conn->query("DELETE FROM tbl_otp WHERE admin_id = $user_id");
                
                $stmt = $conn->prepare("INSERT INTO tbl_otp (admin_id, otp_code, expired_at, is_used) VALUES (?, ?, ?, 0)");
                $stmt->bind_param("iss", $user_id, $otp, $expired_at);
            } else {
                // Clear old OTPs for this customer
                $conn->query("DELETE FROM tbl_otp WHERE customer_id = $user_id");

                $stmt = $conn->prepare("INSERT INTO tbl_otp (customer_id, otp_code, expired_at, is_used) VALUES (?, ?, ?, 0)");
                $stmt->bind_param("iss", $user_id, $otp, $expired_at);
            }
            
            if ($stmt->execute()) {
                // Save info to session so next page knows who we are checking
                $_SESSION['reset_email'] = $email; 
                $_SESSION['reset_user_id'] = $user_id;
                $_SESSION['reset_user_type'] = $user_type;

                // Send Email
                if (sendOTP($email, $otp, 'Password Reset')) {
                    $_SESSION['success'] = 'OTP sent! Check your email.';
                    header("Location: ../verify_reset_otp.php");
                    exit();
                } else {
                    $error = 'Failed to send OTP email.';
                }
            } else {
                $error = 'Database error. Please try again.';
            }
            $stmt->close();
        }
    }
}

if (!empty($error)) {
    $_SESSION['error'] = $error;
    header("Location: ../forgot_password.php");
    exit();
}
?>