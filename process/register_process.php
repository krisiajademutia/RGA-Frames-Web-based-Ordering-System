<?php
// process/register_process.php
session_start();
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../includes/email_functions.php';
require_once __DIR__ . '/../classes/RegistrationValidator.php';

$errors = [];
$old = $_POST;

$validator = new RegistrationValidator($conn);
$errors = $validator->validate($_POST);

$emailToCheck = $_POST['email'] ?? '';
$usernameToCheck = $_POST['username'] ?? '';

// Check for duplicates in the database BEFORE we proceed
if (empty($errors['email']) && empty($errors['username'])) {
    
    // 🚨 1. CROSS-TABLE EMAIL CHECK (Checks Admin AND Customer tables)
    $stmtEmail = $conn->prepare("
        SELECT email FROM tbl_admin WHERE email = ? 
        UNION 
        SELECT email FROM tbl_customer WHERE email = ?
    ");
    $stmtEmail->bind_param("ss", $emailToCheck, $emailToCheck);
    $stmtEmail->execute();
    $stmtEmail->store_result();
    
    if ($stmtEmail->num_rows > 0) {
        $errors['email'] = "This email is already registered in our system. Please use a different email.";
    }
    $stmtEmail->close();

    // 🚨 2. CROSS-TABLE USERNAME CHECK (Checks Admin AND Customer tables)
    $stmtUser = $conn->prepare("
        SELECT username FROM tbl_admin WHERE username = ? 
        UNION 
        SELECT username FROM tbl_customer WHERE username = ?
    ");
    $stmtUser->bind_param("ss", $usernameToCheck, $usernameToCheck);
    $stmtUser->execute();
    $stmtUser->store_result();
    
    if ($stmtUser->num_rows > 0) {
        $errors['username'] = "This username is already taken. Please choose another one.";
    }
    $stmtUser->close();
}

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