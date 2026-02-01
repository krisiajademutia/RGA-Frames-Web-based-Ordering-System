<?php
// login_process.php
session_start();
include 'db_connect.php';

if (isset($_POST['login_btn'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please enter both email and password.';
        header("Location: login.php");
        exit();
    }

    $stmt = $conn->prepare("
        SELECT user_id, first_name, last_name, password, role 
        FROM tbl_users 
        WHERE email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id']    = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['role']       = $user['role'];  // This must be 'ADMIN'

            // Success message
            $_SESSION['success'] = 'Login successful! Welcome back, ' . htmlspecialchars($user['first_name']) . '.';

            // Redirect based on role
            if (strtoupper($user['role']) === 'ADMIN') {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                header("Location: customer_dashboard.php");
                exit();
            }
        } else {
            $_SESSION['error'] = 'Incorrect password!';
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = 'Email not found!';
        header("Location: login.php");
        exit();
    }

    $stmt->close();
} else {
    header("Location: login.php");
    exit();
}

$conn->close();
?>