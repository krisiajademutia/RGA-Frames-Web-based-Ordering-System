<?php
// process/forgot_password_process.php

session_start();
date_default_timezone_set('Asia/Manila');

include_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../config/config.php';
include_once __DIR__ . '/../includes/email_functions.php';

// Load classes
require_once __DIR__ . '/../classes/UserFinder.php';
require_once __DIR__ . '/../classes/OtpService.php';

// ONLY run logic if it's a POST request with send_otp button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_otp'])) {

    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address.';
        header("Location: ../forgot_password.php");
        exit();
    }

    $finder = new UserFinder($conn);
    $user = $finder->findByEmail($email);

    if (!$user) {
        $_SESSION['error'] = 'No account found with that email.';
        header("Location: ../forgot_password.php");
        exit();
    }

    // Save email in session so the next page knows who we are verifying!
    $_SESSION['reset_email'] = $email;

    $otpService = new OtpService($conn);
    $otp = $otpService->generateAndSaveOtp($user, $email);

    if ($otp === false) {
        $_SESSION['error'] = 'Database error. Please try again.';
        header("Location: ../forgot_password.php");
        exit();
    }

    if (!sendOTP($email, $otp, 'Password Reset')) {
        $_SESSION['error'] = 'Failed to send OTP email.';
        header("Location: ../forgot_password.php");
        exit();
    }

    $_SESSION['success'] = 'OTP sent! Check your email.';
    // Changed this path to match your other relative paths!
    header("Location: ../verify_reset_otp.php");
    exit();
}