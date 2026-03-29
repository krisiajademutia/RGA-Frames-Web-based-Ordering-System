<?php
// process/register_process.php
session_start();
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../includes/email_functions.php';
require_once __DIR__ . '/../classes/UserRepository.php';
require_once __DIR__ . '/../classes/RegistrationValidator.php';

$errors = [];
$old = $_POST;

$userRepository = new UserRepository($conn);
$validator = new RegistrationValidator($userRepository);
$errors = $validator->validate($_POST);

$emailToCheck = $_POST['email'] ?? '';

// If there are validation errors, send them back
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old_input'] = $old;
    header("Location: ../register.php");
    exit();
}

// 🚀 1. GENERATE THE OTP IN MEMORY (No Database Needed!)
$otp = sprintf("%06d", mt_rand(0, 999999));
$expires_at = time() + (15 * 60); // Code expires in exactly 15 minutes

// 2. Shoot the email
$emailSent = sendOTP($emailToCheck, $otp, 'Welcome! Please Verify Your Account');

if (!$emailSent) {
    $_SESSION['errors'] = ['email' => 'Failed to send verification email. Try again later.'];
    $_SESSION['old_input'] = $old;
    header("Location: ../register.php");
    exit();
}

// 🚀 3. HOLD EVERYTHING IN THE "LOCKER" 
$_SESSION['pending_user'] = [
    'data'       => $_POST,         // Holds their Name, Password, Phone, etc.
    'email'      => $emailToCheck,
    'otp'        => $otp,           // The secret code
    'expires_at' => $expires_at     // The expiration timer
];

// Clear out old form data since they passed validation
unset($_SESSION['old_input']);
unset($_SESSION['errors']);

// Send them to the OTP screen!
header("Location: ../verify_account.php"); 
exit();