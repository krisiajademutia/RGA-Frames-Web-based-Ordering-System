<?php
session_start();
include __DIR__ . '/../config/db_connect.php';

if (isset($_POST['register_btn'])) {
    $errors = [];
    $input_data = $_POST;

    $first_name   = trim($_POST['first_name'] ?? '');
    $last_name    = trim($_POST['last_name'] ?? '');
    $username     = trim($_POST['username'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $password     = $_POST['password'] ?? '';

    // --- First & Last Name ---
    if (empty($first_name)) $errors['first_name'] = "First name is required.";
    if (empty($last_name))  $errors['last_name'] = "Last name is required.";

    // --- Username Validation ---
    if (empty($username)) {
        $errors['username'] = "Username cannot be empty.";
    } elseif (strlen($username) < 5) {
        $errors['username'] = "Username is too short. It must be at least 5 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = "Username can only contain letters, numbers, and underscores.";
    }

    // --- Gmail Validation ---
    if (empty($email)) {
        $errors['email'] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "The email address entered is not a valid format.";
    } elseif (!str_ends_with(strtolower($email), '@gmail.com')) {
        $errors['email'] = "Invalid domain. We only accept Gmail addresses (e.g., user@gmail.com).";
    }

    // --- Phone Number Validation (The specific "Invalid Format" part) ---
    if (!empty($phone_number)) {
        $cleaned_phone = preg_replace('/\D/', '', $phone_number);
        if (!str_starts_with($cleaned_phone, '09')) {
            $errors['phone_number'] = "Invalid phone number format. It must start with '09'.";
        } elseif (strlen($cleaned_phone) !== 11) {
            $errors['phone_number'] = "Invalid phone number length. It must be exactly 11 digits.";
        } else {
            $phone_number = $cleaned_phone;
        }
    }

    // --- Password Validation ---
    if (empty($password)) {
        $errors['password'] = "Please create a password.";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password is too weak. It must be at least 8 characters long.";
    }

    // --- Database Duplicate Checks ---
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT email FROM tbl_users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) $errors['email'] = "This email is already registered to another account.";
        
        $stmt = $conn->prepare("SELECT username FROM tbl_users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) $errors['username'] = "This username is taken. Try adding numbers or choosing a different one.";
        $stmt->close();
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $input_data;
        header("Location: ../customer/customer_dashboard.php");
        exit();
    }

   // Final insertion code here... (same as before)
     $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check email
    $stmt = $conn->prepare("SELECT user_id FROM tbl_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Email already registered!';
        header("Location: ../customer/customer_dashboard.php");
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
        header("Location: ../customer/customer_dashboard.php");
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

        header("Location: ../customer/customer_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = 'Error creating account: ' . $stmt->error;
        header("Location: ../customer/customer_dashboard.php");
        exit();
    }

    $stmt->close();
} else {
    header("Location: ../customer/customer_dashboard.php");
    exit();
}
$conn->close();
?>

