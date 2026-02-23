<?php
session_start();
include __DIR__ . '/../config/db_connect.php';

if (isset($_POST['register_btn'])) {
    $errors = [];
    $input_data = $_POST;

    // Get Inputs
    $first_name   = trim($_POST['first_name'] ?? '');
    $last_name    = trim($_POST['last_name'] ?? '');
    $username     = trim($_POST['username'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $password     = $_POST['password'] ?? '';

    // --- Validation Logic ---
    if (empty($first_name)) $errors['first_name'] = "First name is required.";
    if (empty($last_name))  $errors['last_name'] = "Last name is required.";

    // Username Checks
    if (empty($username)) {
        $errors['username'] = "Username cannot be empty.";
    } elseif (strlen($username) < 5) {
        $errors['username'] = "Username must be at least 5 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = "Username can only contain letters, numbers, and underscores.";
    }

    // Email Checks
    if (empty($email)) {
        $errors['email'] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } elseif (!str_ends_with(strtolower($email), '@gmail.com')) {
        $errors['email'] = "We only accept Gmail addresses (e.g., user@gmail.com).";
    }

    // Phone Checks
    if (!empty($phone_number)) {
        $cleaned_phone = preg_replace('/\D/', '', $phone_number); // Remove non-numbers
        if (!str_starts_with($cleaned_phone, '09')) {
            $errors['phone_number'] = "Phone number must start with '09'.";
        } elseif (strlen($cleaned_phone) !== 11) {
            $errors['phone_number'] = "Phone number must be exactly 11 digits.";
        } else {
            $phone_number = $cleaned_phone; 
        }
    }

    // Password Checks
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters.";
    }

    // --- Database Duplicate Checks (tbl_customer) ---
    if (empty($errors)) {
        // Check Email
        $stmt = $conn->prepare("SELECT email FROM tbl_customer WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) $errors['email'] = "This email is already registered.";
        $stmt->close();
        
        // Check Username
        $stmt = $conn->prepare("SELECT username FROM tbl_customer WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) $errors['username'] = "This username is taken.";
        $stmt->close();
    }

    // If Errors, Redirect Back
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $input_data;
        header("Location: ../register.php");
        exit();
    }

    // --- Insert into Database ---
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $phone_value = !empty($phone_number) ? $phone_number : null;

    $stmt = $conn->prepare("INSERT INTO tbl_customer (first_name, last_name, username, email, phone_number, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $first_name, $last_name, $username, $email, $phone_value, $hashed_password);

    if ($stmt->execute()) {
        // Success: Log them in automatically
        $_SESSION['success'] = 'Registration successful!';
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['first_name'] = $first_name;
        $_SESSION['role'] = 'CUSTOMER';

        header("Location: ../customer/customer_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = 'Database error: ' . $stmt->error;
        header("Location: ../register.php");
        exit();
    }
} else {
    header("Location: ../register.php");
    exit();
}
?>