<?php
// register_process.php
session_start();
include 'db_connect.php';

if (isset($_POST['register_btn'])) {
    $first_name    = trim($_POST['first_name'] ?? '');
    $last_name     = trim($_POST['last_name'] ?? '');
    $username      = trim($_POST['username'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone_number  = trim($_POST['phone_number'] ?? '');
    $password      = $_POST['password'] ?? '';

    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please fill all required fields.';
        header("Location: register.php");
        exit();
    }

    if (!str_ends_with(strtolower($email), '@gmail.com')) {
        $_SESSION['error'] = 'Please use a Gmail account (must end with @gmail.com).';
        header("Location: register.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check email
    $stmt = $conn->prepare("SELECT user_id FROM tbl_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Email already registered!';
        header("Location: register.php");
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Check username
    $stmt = $conn->prepare("SELECT user_id FROM tbl_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Username already taken! Please choose another.';
        header("Location: register.php");
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Insert
    $stmt = $conn->prepare("
        INSERT INTO tbl_users 
        (first_name, last_name, username, email, phone_number, password, role) 
        VALUES (?, ?, ?, ?, ?, ?, 'CUSTOMER')
    ");

    $phone_value = !empty($phone_number) ? $phone_number : null;

    $stmt->bind_param("ssssss", $first_name, $last_name, $username, $email, $phone_value, $hashed_password);

    if ($stmt->execute()) {
        // Set session for success message on dashboard
        $_SESSION['success'] = 'Registration successful! Welcome to RGA Frames.';
        $_SESSION['user_id'] = $conn->insert_id; // Auto-get new user ID
        $_SESSION['first_name'] = $first_name;
        $_SESSION['role'] = 'CUSTOMER';

        header("Location: customer_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = 'Error creating account: ' . $stmt->error;
        header("Location: register.php");
        exit();
    }

    $stmt->close();
} else {
    header("Location: register.php");
    exit();
}

$conn->close();
?>