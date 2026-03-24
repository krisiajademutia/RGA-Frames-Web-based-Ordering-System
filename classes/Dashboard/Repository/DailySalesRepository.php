<?php
namespace Classes\Dashboard\Repository;

interface IDailySalesRepository {
    public function getDailySalesData();
    public function getTodaysCombinedBreakdown();
}

class DailySalesRepository implements IDailySalesRepository {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function getDailySalesData() {
        $query = "
            SELECT 
                DATE(o.created_at) as sale_date,
                SUM(o.total_price) as daily_earnings,
                (SELECT COALESCE(SUM(i.quantity), 0) FROM tbl_frame_order_items i JOIN tbl_orders o2 ON i.order_id = o2.order_id WHERE DATE(o2.created_at) = DATE(o.created_at) AND o2.order_status = 'COMPLETED' AND i.frame_category = 'READY_MADE') as ready_made_qty,
                (SELECT COALESCE(SUM(i.quantity), 0) FROM tbl_frame_order_items i JOIN tbl_orders o2 ON i.order_id = o2.order_id WHERE DATE(o2.created_at) = DATE(o.created_at) AND o2.order_status = 'COMPLETED' AND i.frame_category = 'CUSTOM') as custom_qty,
                (SELECT COALESCE(SUM(p.quantity), 0) FROM tbl_printing_order_items p JOIN tbl_orders o2 ON p.order_id = o2.order_id WHERE DATE(o2.created_at) = DATE(o.created_at) AND o2.order_status = 'COMPLETED') as printing_qty
            FROM tbl_orders o
            WHERE o.order_status = 'COMPLETED'
            GROUP BY DATE(o.created_at)
            ORDER BY sale_date DESC LIMIT 30
        ";
        return $this->conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public function getTodaysCombinedBreakdown() {
        $query = "
            SELECT 
                order_id, order_date, order_time, order_reference_no, customer_name, item_name, category, quantity, total_price 
            FROM (
                SELECT 
                    o.order_id,
                    DATE_FORMAT(o.created_at, '%b %d, %Y') as order_date,
                    DATE_FORMAT(o.created_at, '%h:%i %p') as order_time,  /* 🔥 Extracts time from your created_at column */
                    o.order_reference_no,
                    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                    COALESCE(rp.product_name, 'Custom Frame') as item_name,
                    REPLACE(f.frame_category, '_', ' ') as category,
                    f.quantity,
                    o.total_price,
                    o.created_at
                FROM tbl_orders o
                JOIN tbl_customer c ON o.customer_id = c.customer_id
                JOIN tbl_frame_order_items f ON o.order_id = f.order_id
                LEFT JOIN tbl_ready_made_product rp ON f.r_product_id = rp.r_product_id
                WHERE o.order_status = 'COMPLETED'

                UNION ALL

                SELECT 
                    o.order_id,
                    DATE_FORMAT(o.created_at, '%b %d, %Y') as order_date,
                    DATE_FORMAT(o.created_at, '%h:%i %p') as order_time,  /* 🔥 Extracts time from your created_at column */
                    o.order_reference_no,
                    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                    'Printing Service' as item_name,
                    'PRINT ONLY' as category,
                    p.quantity,
                    o.total_price,
                    o.created_at
                FROM tbl_orders o
                JOIN tbl_customer c ON o.customer_id = c.customer_id
                JOIN tbl_printing_order_items p ON o.order_id = p.order_id
                WHERE o.order_status = 'COMPLETED'
            ) as combined_tables
            ORDER BY created_at DESC, category ASC
        ";
        return $this->conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }
}
?>