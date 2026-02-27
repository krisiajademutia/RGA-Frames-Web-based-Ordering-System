<?php

class PasswordResetService {
    private $conn;

    private $roleConfigs = [
        'ADMIN'    => ['table' => 'tbl_admin',    'id_column' => 'admin_id'],
        'CUSTOMER' => ['table' => 'tbl_customer', 'id_column' => 'customer_id']
    ];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function resetPassword(array $user, string $new_password, string $confirm_password): bool {
        // 1. Validation check
        if ($new_password !== $confirm_password) {
            return false;
        }

        // 2. Type check
        $type = strtoupper($user['type']); // Force uppercase to match keys
        if (!isset($this->roleConfigs[$type])) {
            return false; 
        }

        $config = $this->roleConfigs[$type];
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // 3. Dynamic SQL
        $sql = "UPDATE {$config['table']} SET password = ? WHERE {$config['id_column']} = ?";

        $stmt = $this->conn->prepare($sql);
        
        // Bind parameters: 's' for hashed string, 'i' for integer ID
        $stmt->bind_param("si", $hashed_password, $user['id']);

        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}