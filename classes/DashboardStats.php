<?php
// classes/DashboardStats.php

interface IDashboardStats {
    public function getTotalEarnings();
    public function getSoldReadyMadeFrames();
    public function getSoldCustomFrames();
    public function getPostedReadyMadeFrames();
}

class DashboardStats implements IDashboardStats {
    private $db;

    // Changed from PDO to mysqli to match your db_connect.php
    public function __construct(mysqli $dbConnection) {
        $this->db = $dbConnection;
    }

    public function getTotalEarnings() {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM tbl_payment WHERE payment_status = 'FULL'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function getSoldReadyMadeFrames() {
        $sql = "SELECT COALESCE(SUM(i.quantity), 0) as total 
                FROM tbl_frame_order_items i 
                JOIN tbl_orders o ON i.order_id = o.order_id 
                WHERE i.frame_category = 'READY_MADE' AND o.order_status = 'COMPLETED'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function getSoldCustomFrames() {
        $sql = "SELECT COALESCE(SUM(i.quantity), 0) as total 
                FROM tbl_frame_order_items i 
                JOIN tbl_orders o ON i.order_id = o.order_id 
                WHERE i.frame_category = 'CUSTOM' AND o.order_status = 'COMPLETED'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function getPostedReadyMadeFrames() {
        $sql = "SELECT COUNT(r_product_id) as total FROM tbl_ready_made_product";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }
}
?>