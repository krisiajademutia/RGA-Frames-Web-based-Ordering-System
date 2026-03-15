<?php
// process/profile_process.php
session_start();
include __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'CUSTOMER') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$customer_id = (int)$_SESSION['user_id'];
$action      = $_POST['action'] ?? '';

// ── Update personal info ─────────────────────────────────
if ($action === 'update_info') {
    $first_name   = trim($_POST['first_name']   ?? '');
    $last_name    = trim($_POST['last_name']    ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    if (empty($first_name) || empty($last_name)) {
        echo json_encode(['success' => false, 'message' => 'First name and last name are required.']);
        exit();
    }

    $stmt = $conn->prepare("
        UPDATE tbl_customer
        SET first_name = ?, last_name = ?, phone_number = ?
        WHERE customer_id = ?
    ");
    $stmt->bind_param("sssi", $first_name, $last_name, $phone_number, $customer_id);

    if ($stmt->execute()) {
        // Update session name
        $_SESSION['first_name'] = $first_name;
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile. Please try again.']);
    }
    exit();
}

// ── Change password ──────────────────────────────────────
if ($action === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password']     ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'All password fields are required.']);
        exit();
    }
    if (strlen($new_password) < 8) {
        echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters.']);
        exit();
    }
    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'New password and confirmation do not match.']);
        exit();
    }

    // Fetch current hashed password
    $stmt = $conn->prepare("SELECT password FROM tbl_customer WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row || !password_verify($current_password, $row['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
        exit();
    }

    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt2  = $conn->prepare("UPDATE tbl_customer SET password = ? WHERE customer_id = ?");
    $stmt2->bind_param("si", $hashed, $customer_id);

    if ($stmt2->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password. Please try again.']);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
exit();