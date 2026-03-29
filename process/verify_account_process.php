<?php
// process/verify_account_process.php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../verify_account.php");
    exit();
}

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/UserRepository.php';
require_once __DIR__ . '/../classes/RegistrationService.php';

// Security check
if (!isset($_SESSION['pending_user'])) {
    die("<h1>Session Expired</h1><p>Your registration session expired. Please go back and register again.</p>");
}

$pending = $_SESSION['pending_user'];

// 1. Collect the 6 digits
$otp = ($_POST['otp_1'] ?? '') .
       ($_POST['otp_2'] ?? '') .
       ($_POST['otp_3'] ?? '') .
       ($_POST['otp_4'] ?? '') .
       ($_POST['otp_5'] ?? '') .
       ($_POST['otp_6'] ?? '');

$otp = trim($otp);

if (empty($otp) || strlen($otp) !== 6) {
    $_SESSION['errors'] = ['otp' => 'Please enter the complete 6-digit code.'];
    header("Location: ../verify_account.php");
    exit();
}

// 2. CHECK EXPIRATION TIMER (Strict 15 minutes!)
if (time() > $pending['expires_at']) {
    unset($_SESSION['pending_user']); // Destroy the data
    $_SESSION['errors'] = ['email' => 'Code expired! You took longer than 15 minutes. Please register again.'];
    header("Location: ../register.php"); // Send them all the way back
    exit();
}

// 3. CHECK IF THE CODE MATCHES
if ($otp !== $pending['otp']) {
    $_SESSION['errors'] = ['otp' => 'Invalid verification code.'];
    header("Location: ../verify_account.php");
    exit();
}

// 🚀 4. SUCCESS! FINALLY SAVE THEM TO THE DATABASE! 🚀
$userRepository = new UserRepository($conn);
$service = new RegistrationService($userRepository);

// We pass the original $_POST data we held in the session!
$result = $service->register($pending['data']);

if (!$result['success']) {
    $_SESSION['errors'] = ['otp' => 'Database error. Unable to create account.'];
    header("Location: ../verify_account.php");
    exit();
}

// --- 5. Finalize the Login ---
$_SESSION['user_id']    = $result['user_id'];
$_SESSION['first_name'] = $pending['data']['first_name'];
$_SESSION['role']       = 'CUSTOMER';

// Empty the temporary locker!
unset($_SESSION['pending_user']);
unset($_SESSION['errors']);

// Redirect to Dashboard
header("Location: ../customer/customer_dashboard.php");
exit();