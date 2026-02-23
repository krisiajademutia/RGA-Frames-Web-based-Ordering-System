<?php
// process/reset_password_process.php

session_start();
include_once __DIR__ . '/../config/db_connect.php';

// Load classes
require_once __DIR__ . '/../classes/UserFinder.php';
require_once __DIR__ . '/../classes/PasswordResetService.php';

// Security Check
if (!isset($_SESSION['reset_verified']) || !isset($_SESSION['reset_email'])) {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['reset_password'])) {
    header("Location: ../reset_password.php");
    exit();
}

$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($new_password) || strlen($new_password) < 8) {
    $_SESSION['error'] = 'Password must be at least 8 characters.';
    header("Location: ../reset_password.php");
    exit();
}

if ($new_password !== $confirm_password) {
    $_SESSION['error'] = 'Passwords do not match.';
    header("Location: ../reset_password.php");
    exit();
}

$finder = new UserFinder($conn);
$user = $finder->findByEmail($email);

if (!$user) {
    $_SESSION['error'] = 'User not found in database.';
    header("Location: ../reset_password.php");
    exit();
}

$resetService = new PasswordResetService($conn);
$success = $resetService->resetPassword($user, $new_password, $confirm_password);

// ... after $success = $resetService->resetPassword(...)

if ($success) {
    // 1. CLEAR SESSIONS for security
    unset($_SESSION['reset_verified']);
    unset($_SESSION['reset_email']);
    
    // 2. Set success message and go to login
    $_SESSION['success'] = 'Password updated! Please login with your new password.';
    header("Location: ../login.php");
    exit();
} else {
    $_SESSION['error'] = 'Failed to update password. Please try again.';
    header("Location: ../reset_password.php");
    exit();
}