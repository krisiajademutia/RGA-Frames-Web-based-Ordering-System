<?php
// classes/Notification/NotificationRepository.php

class NotificationRepository {
    private $conn;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    public function createCustomerNotification(int $customer_id, ?int $order_id_val, string $title, string $message): bool {
        $stmt = $this->conn->prepare("INSERT INTO tbl_notifications (customer_id, order_id, title, message, is_read) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("iiss", $customer_id, $order_id_val, $title, $message);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    public function createAdminNotification(?int $order_id_val, string $title, string $message): bool {
        $stmt = $this->conn->prepare("INSERT INTO tbl_notifications (customer_id, order_id, title, message, is_read) VALUES (NULL, ?, ?, ?, 0)");
        $stmt->bind_param("iss", $order_id_val, $title, $message);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    public function getOrderReference(int $order_id): ?string {
        $stmt = $this->conn->prepare("SELECT order_reference_no FROM tbl_orders WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $res ? $res['order_reference_no'] : null;
    }
}
?>
