<?php
// classes/OtpService.php

class OtpService {
    private $conn;

    // ── SOLID (Open/Closed Principle) ──
    // Mapping roles to their specific ID columns in the tbl_otp table.
    private $roleConfigs = [
        'ADMIN'    => ['id_column' => 'admin_id'],
        'CUSTOMER' => ['id_column' => 'customer_id']
    ];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Generates a new OTP, clears old ones, saves it to database
     * and returns the OTP code (or false on failure)
     */
    public function generateAndSaveOtp(array $user, string $email): string|false {
        if (!isset($this->roleConfigs[$user['type']])) {
            return false;
        }

        $config = $this->roleConfigs[$user['type']];
        $id_col = $config['id_column'];

        $otp = sprintf("%06d", mt_rand(0, 999999));
        $expired_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // 1. Clear old OTPs (Dynamic query)
        $stmt = $this->conn->prepare("DELETE FROM tbl_otp WHERE {$id_col} = ?");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $stmt->close();

        // 2. Insert new OTP (Dynamic query)
        $stmt = $this->conn->prepare(
            "INSERT INTO tbl_otp ({$id_col}, otp_code, expired_at, is_used) 
             VALUES (?, ?, ?, 0)"
        );
        $stmt->bind_param("iss", $user['id'], $otp, $expired_at);
        $success = $stmt->execute();
        $stmt->close();

        return $success ? $otp : false;
    }

    /**
     * Verifies the entered OTP and marks as used if valid.
     * Returns true on success, false on invalid/expired/used.
     */
    public function verifyOtp(array $user, string $entered_otp): bool {
        if (!isset($this->roleConfigs[$user['type']])) {
            return false;
        }

        $config = $this->roleConfigs[$user['type']];
        $id_col = $config['id_column'];

        // Check if OTP is valid (Dynamic query)
        $sql = "SELECT * FROM tbl_otp 
                WHERE {$id_col} = ? AND otp_code = ? AND is_used = 0 AND expired_at > NOW() LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $user['id'], $entered_otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $stmt->close();

            // Mark as used (Dynamic query)
            $upd = $this->conn->prepare("UPDATE tbl_otp SET is_used = 1 WHERE {$id_col} = ? AND otp_code = ?");
            $upd->bind_param("is", $user['id'], $entered_otp);
            $upd->execute();
            $upd->close();

            return true;
        }

        $stmt->close();
        return false;
    }
}