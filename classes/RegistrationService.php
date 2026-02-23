<?php
class RegistrationService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function register(array $data): array {
        $first_name   = trim($data['first_name']);
        $last_name    = trim($data['last_name']);
        $username     = trim($data['username']);
        $email        = trim($data['email']);
        $phone_number = !empty($data['phone_number']) ? preg_replace('/\D/', '', $data['phone_number']) : null;
        $password     = $data['password'];

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare(
            "INSERT INTO tbl_customer 
             (first_name, last_name, username, email, phone_number, password) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssss", $first_name, $last_name, $username, $email, $phone_number, $hashed_password);

        if ($stmt->execute()) {
            $user_id = $this->conn->insert_id;
            $stmt->close();

            // Auto-login
            $_SESSION['user_id']     = $user_id;
            $_SESSION['first_name']  = $first_name;
            $_SESSION['role']        = 'CUSTOMER';

            return ['success' => true, 'user_id' => $user_id, 'message' => 'Registration successful!'];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return ['success' => false, 'message' => 'Database error: ' . $error];
        }
    }
}