<?php
// classes/Order/Repository/OrderRepository.php

class OrderRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getSummaryCounts() {
        $sql = "
            SELECT 
                COUNT(CASE WHEN order_status = 'PENDING' THEN 1 END) AS new_orders,
                COUNT(CASE WHEN order_status = 'COMPLETED' AND DATE(created_at) = CURDATE() THEN 1 END) AS completed_today,
                COUNT(CASE WHEN order_status IN ('PROCESSING', 'READY_FOR_PICKUP', 'FOR_DELIVERY') THEN 1 END) AS in_progress,
                COUNT(CASE WHEN order_status IN ('REJECTED', 'CANCELLED') THEN 1 END) AS issues
            FROM tbl_orders
        ";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_assoc() : ['new_orders'=>0,'completed_today'=>0,'in_progress'=>0,'issues'=>0];
    }

    public function getOrdersByStatus(string $status, $filters = []) {
        $sql = "
    SELECT 
        o.order_id, o.order_reference_no, o.created_at, o.total_price,
        o.order_status, o.payment_method, o.delivery_option,
        c.first_name, c.last_name, c.phone_number, c.email,

        foi.service_type,
        poi.order_id AS print_order

    FROM tbl_orders o
    JOIN tbl_customer c ON o.customer_id = c.customer_id

    LEFT JOIN tbl_frame_order_items foi
        ON o.order_id = foi.order_id

    LEFT JOIN tbl_printing_order_items poi
        ON o.order_id = poi.order_id

    WHERE o.order_status = ?
";
        $params = [$status];
        $types  = "s";

        if (!empty($filters['filterDate'])) {
            $sql .= " AND DATE(o.created_at) = ?";
            $params[] = $filters['filterDate'];
            $types .= "s";
        }

        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getOrderById(int $order_id) {
        // Get order + customer + payment summary
        $stmt = $this->conn->prepare("
            SELECT 
                o.*,
                c.first_name, c.last_name, c.phone_number, c.email, c.username,
                p.payment_id,
                p.total_amount,
                p.payment_status,
                p.date_paid,
                COALESCE(SUM(pu.uploaded_amount), 0) AS amount_paid
            FROM tbl_orders o
            JOIN tbl_customer c ON o.customer_id = c.customer_id
            LEFT JOIN tbl_payment p ON o.order_id = p.order_id
            LEFT JOIN tbl_payment_proof_uploads pu ON p.payment_id = pu.payment_id
            WHERE o.order_id = ?
            GROUP BY o.order_id, p.payment_id
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getPaymentProofs(int $payment_id) {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM tbl_payment_proof_uploads
            WHERE payment_id = ?
            ORDER BY upload_date ASC
        ");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updateStatus(int $order_id, string $new_status) {
        $stmt = $this->conn->prepare("UPDATE tbl_orders SET order_status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        return $stmt->execute();
    }

    public function verifyProof(int $upload_id) {
        $stmt = $this->conn->prepare("
            UPDATE tbl_payment_proof_uploads 
            SET verification_status = 'Verified' 
            WHERE upload_id = ?
        ");
        $stmt->bind_param("i", $upload_id);
        return $stmt->execute();
    }

    public function rejectProof(int $upload_id) {
        $stmt = $this->conn->prepare("
            UPDATE tbl_payment_proof_uploads 
            SET verification_status = 'Rejected' 
            WHERE upload_id = ?
        ");
        $stmt->bind_param("i", $upload_id);
        return $stmt->execute();
    }

    public function updatePaymentStatus(int $payment_id, string $status) {
        $stmt = $this->conn->prepare("
            UPDATE tbl_payment SET payment_status = ? WHERE payment_id = ?
        ");
        $stmt->bind_param("si", $status, $payment_id);
        return $stmt->execute();
    }
}