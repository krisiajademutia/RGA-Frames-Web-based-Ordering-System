<?php
session_start();
include_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/UserFinder.php';
require_once __DIR__ . '/../classes/AuthService.php';

if (!isset($_POST['login_btn'])) {
    header("Location: ../login.php");
    exit();
}

$errors = [];
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username)) {
    $errors['username'] = "Please enter your username or email.";
}
if (empty($password)) {
    $errors['password'] = "Please enter your password.";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old_username'] = $username;
    header("Location: ../login.php");
    exit();
}

$finder = new UserFinder($conn);
$user = $finder->findByUsernameOrEmail($username);

$auth = new AuthService($conn);

if ($auth->attemptLogin($user, $password)) {
    // ── SOLID FIX: Redirect logic is now handled by the Controller ──
    if ($_SESSION['role'] === 'ADMIN') {
        header("Location: ../admin/admin_dashboard.php");
    } else {
        header("Location: ../customer/customer_dashboard.php"); 
    }
    exit();
} else {
    // Failure
    if ($user) {
        $errors['password'] = "Incorrect password.";
    } else {
        $errors['username'] = "No account found with that username or email.";
    }

    $_SESSION['errors'] = $errors;
    $_SESSION['old_username'] = $username;
    header("Location: ../login.php");
    exit();
}