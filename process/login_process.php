<?php
session_start();
include __DIR__ . '/../config/db_connect.php';

if (isset($_POST['login_btn'])) {
    $errors = [];
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. Check for empty fields
    if (empty($username)) {
        $errors['username'] = "Please enter your username.";
    }
    if (empty($password)) {
        $errors['password'] = "Please enter your password.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id, first_name, password, role FROM tbl_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Success
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['role'] = $user['role'];

                // FIXED REDIRECT: go up from process/ then into admin/ or customer/
                $redirect = (strtoupper($user['role']) === 'ADMIN') 
                    ? "../admin/admin_dashboard.php" 
                    : "../customer/customer_dashboard.php";

                header("Location: " . $redirect);
                exit();
            } else {
                $errors['password'] = "Incorrect password. Please try again or reset it.";
            }
        } else {
            $errors['username'] = "No account found with this email address.";
        }
        $stmt->close();
    }

    // Return to login with errors
    $_SESSION['errors'] = $errors;
    $_SESSION['old_username'] = $username;
    header("Location: ../login.php");
    exit();
} else {
    header("Location: ../login.php");
    exit();
}