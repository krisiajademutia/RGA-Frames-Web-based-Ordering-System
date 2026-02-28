<?php
// Interface for Dependency Inversion
interface IDailySalesRepository {
    public function getDailySalesData();
}

class DailySalesRepository implements IDailySalesRepository {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function getDailySalesData() {
        // This query perfectly matches your rga_frames_db schema
        $query = "
            SELECT 
                DATE(o.created_at) as sale_date,
                SUM(o.total_price) as daily_earnings,
                
                -- Subquery to count Ready-Made frames sold that day
                (SELECT COALESCE(SUM(i.quantity), 0) 
                 FROM tbl_frame_order_items i 
                 JOIN tbl_orders o2 ON i.order_id = o2.order_id 
                 WHERE DATE(o2.created_at) = DATE(o.created_at) 
                 AND o2.order_status = 'COMPLETED' 
                 AND i.frame_category = 'READY_MADE') as ready_made_qty,
                 
                -- Subquery to count Custom frames sold that day
                (SELECT COALESCE(SUM(i.quantity), 0) 
                 FROM tbl_frame_order_items i 
                 JOIN tbl_orders o2 ON i.order_id = o2.order_id 
                 WHERE DATE(o2.created_at) = DATE(o.created_at) 
                 AND o2.order_status = 'COMPLETED' 
                 AND i.frame_category = 'CUSTOM') as custom_qty

            FROM tbl_orders o
            WHERE o.order_status = 'COMPLETED'
            GROUP BY DATE(o.created_at)
            ORDER BY sale_date DESC
            LIMIT 30
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>