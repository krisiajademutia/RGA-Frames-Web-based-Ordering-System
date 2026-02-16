<?php
session_start();
include __DIR__ . '/../config/db_connect.php';

if (isset($_POST['login_btn'])) {
    $errors = [];
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. Check for empty fields
    if (empty($email)) {
        $errors['email'] = "Please enter your registered Gmail address.";
    }
    if (empty($password)) {
        $errors['password'] = "Please enter your password.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id, first_name, password, role FROM tbl_users WHERE email = ?");
        $stmt->bind_param("s", $email);
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
            $errors['email'] = "No account found with this email address.";
        }
        $stmt->close();
    }

    // Return to login with errors
    $_SESSION['errors'] = $errors;
    $_SESSION['old_email'] = $email;
    header("Location: ../login.php");
    exit();
} else {
    header("Location: ../login.php");
    exit();
}