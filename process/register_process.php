<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/RegistrationValidator.php';
require_once __DIR__ . '/../classes/RegistrationService.php';

$errors = [];
$old = $_POST;

// 1. Validate basic inputs
$validator = new RegistrationValidator($conn);
$errors = $validator->validate($_POST);

$emailToCheck = $_POST['email'] ?? '';
$usernameToCheck = $_POST['username'] ?? '';

// 2. Check for duplicates (only if email and username passed basic validation)
if (empty($errors['email']) && empty($errors['username'])) {
    if (!empty($emailToCheck) && !empty($usernameToCheck)) {
        $dupErrors = $validator->checkDuplicates($emailToCheck, $usernameToCheck);
        $errors = array_merge($errors, $dupErrors); 
    }
}

// 3. Redirect back if any validation fails
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old_input'] = $old;

    header("Location: ../register.php");
    exit();
}

// 4. Register the user
$service = new RegistrationService($conn);
$result = $service->register($_POST);

// 5. Handle registration failure (e.g., database error)
if (!$result['success']) {
    $_SESSION['errors'] = ['general' => $result['message']];
    $_SESSION['old_input'] = $old;

    header("Location: ../register.php");
    exit();
}

// 6. Redirect to dashboard on success
header("Location: ../customer/customer_dashboard.php");
exit();