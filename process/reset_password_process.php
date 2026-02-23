<?php
session_start();
include_once __DIR__ . '/../config/db_connect.php';

// Security Check
if (!isset($_SESSION['reset_verified']) || !isset($_SESSION['reset_email'])) {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['reset_email'];
$error = '';

// 1. Determine User Type and ID based on the email in session
$user_id = null;
$user_type = '';

// Check Admin Table
$stmt = $conn->prepare("SELECT admin_id FROM tbl_admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $user_id = $row['admin_id'];
    $user_type = 'ADMIN';
}
$stmt->close();

// Check Customer Table (if not admin)
if (!$user_id) {
    $stmt = $conn->prepare("SELECT customer_id FROM tbl_customer WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $user_id = $row['customer_id'];
        $user_type = 'CUSTOMER';
    }
    $stmt->close();
}

// 2. Process Password Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!$user_id) {
        $error = 'User not found in database.';
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update the correct table
        if ($user_type === 'ADMIN') {
            $sql = "UPDATE tbl_admin SET password = ? WHERE admin_id = ?";
        } else {
            $sql = "UPDATE tbl_customer SET password = ? WHERE customer_id = ?";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Password reset successfully! Login now.';
            
            // Clear session data
            unset($_SESSION['reset_verified']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_user_id']); 
            
            header("Location: ../login.php");
            exit();
        } else {
            $error = 'Failed to update password. Database error.';
        }
        $stmt->close();
    }
}

// If error, redirect back to show it
if (!empty($error)) {
    $_SESSION['error'] = $error;
    header("Location: ../reset_password.php");
    exit();
}
?>