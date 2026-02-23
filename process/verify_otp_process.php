<?php
// process/verify_otp_process.php

session_start();
date_default_timezone_set('Asia/Manila');

include_once __DIR__ . '/../config/db_connect.php';

// Load classes
require_once __DIR__ . '/../classes/UserFinder.php';
require_once __DIR__ . '/../classes/OtpService.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: ../forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['verify_otp'])) {
    header("Location: ../verify_reset_otp.php");
    exit();
}

$entered_otp = trim($_POST['otp'] ?? '');

if (strlen($entered_otp) !== 6 || !ctype_digit($entered_otp)) {
    $_SESSION['error'] = 'Please enter the full 6-digit code.';
    header("Location: ../verify_reset_otp.php");
    exit();
}

$finder = new UserFinder($conn);
$user = $finder->findByEmail($email);

if (!$user) {
    $_SESSION['error'] = 'User account not found.';
    header("Location: ../verify_reset_otp.php");
    exit();
}

$otpService = new OtpService($conn);

// verifyOtp now handles the ADMIN/CUSTOMER logic internally!
if ($otpService->verifyOtp($user, $entered_otp)) {
    $_SESSION['reset_verified'] = true;
    header("Location: ../reset_password.php");
    exit();
} else {
    $_SESSION['error'] = 'Invalid or expired OTP code.';
    header("Location: ../verify_reset_otp.php");
    exit();
}