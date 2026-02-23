<?php
session_start();
include __DIR__ . '/../config/db_connect.php';

if (isset($_POST['login_btn'])) {
    $errors = [];
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. Validation
    if (empty($username)) {
        $errors['username'] = "Please enter your username or email.";
    }
    if (empty($password)) {
        $errors['password'] = "Please enter your password.";
    }

    if (empty($errors)) {
        
        // --- STEP 1: Check ADMIN Table First ---
        // (Better to check admin first to ensure staff get priority access)
        $stmt = $conn->prepare("SELECT admin_id, first_name, password FROM tbl_admin WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                // SUCCESS: Logged in as ADMIN
                $_SESSION['user_id']    = $admin['admin_id'];
                $_SESSION['first_name'] = $admin['first_name'];
                $_SESSION['role']       = 'ADMIN';

                // Update Last Login for Admin tracking
                $admin_id = $admin['admin_id'];
                $conn->query("UPDATE tbl_admin SET last_login = NOW() WHERE admin_id = $admin_id");

                header("Location: ../admin/admin_dashboard.php");
                exit();
            } else {
                $errors['password'] = "Incorrect password.";
            }
        } else {
            // --- STEP 2: Check CUSTOMER Table (if no admin match found) ---
            $stmt->close(); // Close the previous statement
            
            $stmt = $conn->prepare("SELECT customer_id, first_name, password FROM tbl_customer WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // SUCCESS: Logged in as CUSTOMER
                    $_SESSION['user_id']    = $user['customer_id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['role']       = 'CUSTOMER';

                    header("Location: ../customer/customer_dashboard.php");
                    exit();
                } else {
                    $errors['password'] = "Incorrect password.";
                }
            } else {
                // If we get here, the user doesn't exist in either table
                $errors['username'] = "No account found with that username or email.";
            }
        }
        $stmt->close();
    }

    // --- LOGIN FAILED ---
    // Save errors and redirect back to login page
    $_SESSION['errors'] = $errors;
    $_SESSION['old_username'] = $username;
    header("Location: ../login.php");
    exit();

} else {
    // If someone tries to access this file directly without clicking login
    header("Location: ../login.php");
    exit();
}
?>