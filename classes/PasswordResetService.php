<?php

class PasswordResetService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function resetPassword(array $user, string $new_password, string $confirm_password): bool {
        // 1. Validation check
        if ($new_password !== $confirm_password) {
            return false;
        }

        // 2. Identify the correct table and column based on role
        $type = strtoupper($user['type'] ?? 'CUSTOMER');
        $table = ($type === 'ADMIN') ? 'tbl_admin' : 'tbl_customer';
        $id_column = ($type === 'ADMIN') ? 'admin_id' : 'customer_id';

        // 3. UserFinder fetches the ID as 'id', so we grab it securely
        $user_id = $user['id'] ?? 0;
        
        if (empty($user_id)) {
            return false; // Failsafe
        }

        // 4. Hash the password using the modern standard (matches your AuthService)
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // 5. Update the specific user by their exact ID
        $sql = "UPDATE {$table} SET password = ? WHERE {$id_column} = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $hashed_password, $user_id);
        $stmt->execute();

        // 6. Return true if we successfully updated the row
        if ($stmt->affected_rows > 0) {
            return true;
        }

        return false;
    }
}