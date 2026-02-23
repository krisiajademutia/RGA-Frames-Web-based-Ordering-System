<?php

class AuthService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
 * Attempt login and set session on success.
 * Returns true on success, false on failure (wrong password or user not found).
 */
public function attemptLogin(?array $user, string $providedPassword): bool {
    if (!$user || !password_verify($providedPassword, $user['password'])) {
        return false;
    }

    // Success → set session
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['role']       = $user['type'];

    // Update last login (only for admin for now)
    if ($user['type'] === 'ADMIN') {
        $stmt = $this->conn->prepare("UPDATE tbl_admin SET last_login = NOW() WHERE admin_id = ?");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $stmt->close();
    }

    return true;
}
}