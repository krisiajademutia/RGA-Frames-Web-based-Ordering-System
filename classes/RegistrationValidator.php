<?php
// classes/RegistrationValidator.php

class RegistrationValidator {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Validates inputs and returns an array of errors
    public function validate(array $data): array {
        $errors = [];

        $first_name   = trim($data['first_name'] ?? '');
        $last_name    = trim($data['last_name'] ?? '');
        $username     = trim($data['username'] ?? '');
        $email        = trim($data['email'] ?? '');
        $phone_number = trim($data['phone_number'] ?? '');
        $password     = $data['password'] ?? '';

        // Check required fields
        if (empty($first_name))   $errors['first_name']   = "First name is required.";
        if (empty($last_name))    $errors['last_name']    = "Last name is required.";
        if (empty($username))     $errors['username']     = "Username cannot be empty.";
        if (empty($email))        $errors['email']        = "Email address is required.";
        if (empty($password))     $errors['password']     = "Password is required.";

        // Username validation
        if (!empty($username)) {
            if (strlen($username) < 5) {
                $errors['username'] = "Username must be at least 5 characters.";
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $errors['username'] = "Username can only contain letters, numbers, and underscores.";
            }
        }

        // Email validation
        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Invalid email format.";
            }
            if (!str_ends_with(strtolower($email), '@gmail.com')) {
                $errors['email'] = "We only accept Gmail addresses (e.g., user@gmail.com).";
            }
        }

        // Phone validation
        if (empty($phone_number)) {
            $errors['phone_number'] = "Phone number is required.";
        } else {
            $cleaned = preg_replace('/\D/', '', $phone_number);
            if (!str_starts_with($cleaned, '09')) {
                $errors['phone_number'] = "Phone number must start with '09'.";
            } elseif (strlen($cleaned) !== 11) {
                $errors['phone_number'] = "Phone number must be exactly 11 digits.";
            }
        }

        return $errors;
    }

    // Checks for duplicate email or username
    public function checkDuplicates(string $email, string $username): array {
        $errors = [];

        // Check email
        $stmt = $this->conn->prepare("SELECT 1 FROM tbl_customer WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors['email'] = "This email is already registered.";
        }
        $stmt->close();

        // Check username
        $stmt = $this->conn->prepare("SELECT 1 FROM tbl_customer WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors['username'] = "This username is taken.";
        }
        $stmt->close();

        return $errors;
    }
}