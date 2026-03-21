<?php
// classes/Notification/NotificationService.php

class NotificationService {
    private $conn;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    // Notify a specific customer
    public function notifyCustomer($customer_id, $order_id, $title, $message) {
        // If order_id is 0 or less, convert it to NULL so the database doesn't crash
        $order_id_val = ($order_id > 0) ? $order_id : null;

        $stmt = $this->conn->prepare("INSERT INTO tbl_notifications (customer_id, order_id, title, message, is_read) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("iiss", $customer_id, $order_id_val, $title, $message);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    // Notify the admin (customer_id is set to NULL)
    public function notifyAdmin($order_id, $title, $message) {
        // If order_id is 0 or less, convert it to NULL so the database doesn't crash
        $order_id_val = ($order_id > 0) ? $order_id : null;

        $stmt = $this->conn->prepare("INSERT INTO tbl_notifications (customer_id, order_id, title, message, is_read) VALUES (NULL, ?, ?, ?, 0)");
        // "iss" means: Integer (order_id), String (title), String (message)
        $stmt->bind_param("iss", $order_id_val, $title, $message);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    // Helper function to get the cool Reference Number
    public function getOrderReference($order_id) {
        // FIXED: Changed to 'order_reference_no' to match your database
        $stmt = $this->conn->prepare("SELECT order_reference_no FROM tbl_orders WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // FIXED: Return the correct array key
        return $res ? $res['order_reference_no'] : "#" . $order_id;
    }
}
?>