<?php
// classes/UserRepository.php

class UserRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Checks if an email exists in either the admin or customer tables.
     */
    public function emailExists(string $email): bool {
        $stmt = $this->conn->prepare("
            SELECT email FROM tbl_admin WHERE email = ? 
            UNION 
            SELECT email FROM tbl_customer WHERE email = ?
        ");
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $stmt->store_result();
        
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        
        return $exists;
    }

    /**
     * Checks if a username exists in either the admin or customer tables.
     */
    public function usernameExists(string $username): bool {
        $stmt = $this->conn->prepare("
            SELECT username FROM tbl_admin WHERE username = ? 
            UNION 
            SELECT username FROM tbl_customer WHERE username = ?
        ");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $stmt->store_result();
        
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        
        return $exists;
    }

    /**
     * Inserts a new customer record and returns the new user ID on success, or false on failure.
     */
    public function createCustomer(array $data, string $hashedPassword): int|false {
        $stmt = $this->conn->prepare(
            "INSERT INTO tbl_customer 
             (first_name, last_name, username, email, phone_number, password) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssssss", 
            $data['first_name'], 
            $data['last_name'], 
            $data['username'], 
            $data['email'], 
            $data['phone_number'], 
            $hashedPassword
        );

        if ($stmt->execute()) {
            $insertId = $this->conn->insert_id;
            $stmt->close();
            return $insertId;
        }

        $stmt->close();
        return false;
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

    /**
     * Updates the last login timestamp for an admin.
     */
    public function updateLastLogin(int $userId, string $role): void {
        if (strtoupper($role) === 'ADMIN') {
            $stmt = $this->conn->prepare("UPDATE tbl_admin SET last_login = NOW() WHERE admin_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    /**
     * Updates a user's password based on their role and ID.
     */
    public function updatePassword(int $userId, string $role, string $hashedPassword): bool {
        $table = (strtoupper($role) === 'ADMIN') ? 'tbl_admin' : 'tbl_customer';
        $idColumn = (strtoupper($role) === 'ADMIN') ? 'admin_id' : 'customer_id';

        $sql = "UPDATE {$table} SET password = ? WHERE {$idColumn} = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $hashedPassword, $userId);
        $stmt->execute();
        
        $success = $stmt->affected_rows > 0;
        $stmt->close();

        return $success;
    }
}
