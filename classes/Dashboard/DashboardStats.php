<?php
class DashboardStats {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function getTotalEarnings() {
        $query = "SELECT SUM(total_price) as total FROM tbl_orders WHERE order_status = 'COMPLETED'";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        return $row['total'] ? (float)$row['total'] : 0.00;
    }

    public function getSoldReadyMadeFrames() {
        $query = "
            SELECT SUM(i.quantity) as total 
            FROM tbl_frame_order_items i
            JOIN tbl_orders o ON i.order_id = o.order_id
            WHERE o.order_status = 'COMPLETED' AND i.frame_category = 'READY_MADE'
        ";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        return $row['total'] ? (int)$row['total'] : 0;
    }

    public function getSoldCustomFrames() {
        $query = "
            SELECT SUM(i.quantity) as total 
            FROM tbl_frame_order_items i
            JOIN tbl_orders o ON i.order_id = o.order_id
            WHERE o.order_status = 'COMPLETED' AND i.frame_category = 'CUSTOM'
        ";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        return $row['total'] ? (int)$row['total'] : 0;
    }

    public function getPostedReadyMadeFrames() {
        // Counting the total number of ready-made products in the inventory
        $query = "SELECT COUNT(*) as total FROM tbl_ready_made_product";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        return $row['total'] ? (int)$row['total'] : 0;
    }
}
?>