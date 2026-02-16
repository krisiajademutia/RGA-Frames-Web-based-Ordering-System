<?php
session_start();
include_once __DIR__ . '/../config/db_connect.php';
$error = '';
if (!isset($_SESSION['reset_email'])) {
    header("Location: ../forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $entered_otp = trim($_POST['otp'] ?? '');

    if (empty($entered_otp) || strlen($entered_otp) !== 6) {
        $error = 'Please enter the full 6-digit code.';
    } else {
        $stmt = $conn->prepare("
            SELECT * FROM tbl_otp 
            WHERE email = ? 
              AND otp_code = ? 
              AND purpose = 'reset_password' 
              AND is_used = 0 
              AND expires_at > NOW()
        ");
        $stmt->bind_param("ss", $email, $entered_otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $conn->query("UPDATE tbl_otp SET is_used = 1 WHERE email = '$email' AND otp_code = '$entered_otp'");
            $_SESSION['reset_verified'] = true;
            header("Location: ../reset_password.php");
            exit();
        } else {
            $error = 'Invalid or expired code.';
        }
        $stmt->close();
    }
}
?>