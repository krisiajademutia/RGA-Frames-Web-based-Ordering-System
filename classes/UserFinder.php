<?php

class UserFinder {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Internal helper: find user in a specific table
     */
    private function findInTable(string $table, string $idColumn, string $email, bool $includePassword = false): ?array {
        $fields = "$idColumn AS id, first_name";
        if ($includePassword) {
            $fields .= ", password";
        }
        $fields .= ", '$table' AS type";

        $sql = "SELECT $fields 
                FROM $table 
                WHERE email = ? 
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = $result->fetch_assoc() ?: null;
        $stmt->close();

        return $user;
    }

    public function findByUsernameOrEmail(string $identifier): ?array {
        // Admin first (priority)
        $stmt = $this->conn->prepare(
            "SELECT admin_id AS id, first_name, password, 'ADMIN' AS type 
             FROM tbl_admin 
             WHERE username = ? OR email = ? 
             LIMIT 1"
        );
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return $row;
        }
        $stmt->close();

        // Customer
        $stmt = $this->conn->prepare(
            "SELECT customer_id AS id, first_name, password, 'CUSTOMER' AS type 
             FROM tbl_customer 
             WHERE username = ? OR email = ? 
             LIMIT 1"
        );
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return $row;
        }
        $stmt->close();

        return null;
    }

    public function findByEmail(string $email): ?array {
    // 1. Search Admin Table
    $user = $this->findInTable('tbl_admin', 'admin_id', $email);
    if ($user) {
        $user['type'] = 'ADMIN'; // Force clean type name
        return $user;
    }

    // 2. Search Customer Table
    $user = $this->findInTable('tbl_customer', 'customer_id', $email);
    if ($user) {
        $user['type'] = 'CUSTOMER'; // Force clean type name
        return $user;
    }

    return null;
}
}