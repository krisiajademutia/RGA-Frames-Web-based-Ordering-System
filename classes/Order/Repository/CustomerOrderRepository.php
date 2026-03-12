<?php
// classes/Order/Repository/CustomerOrderRepository.php

class CustomerOrderRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getCountsByCustomer(int $customer_id): array {
        $stmt = $this->conn->prepare("
            SELECT
                COUNT(*)                                                            AS all_orders,
                COUNT(CASE WHEN order_status = 'PENDING'          THEN 1 END)      AS pending,
                COUNT(CASE WHEN order_status = 'PROCESSING'       THEN 1 END)      AS processing,
                COUNT(CASE WHEN order_status = 'READY_FOR_PICKUP' THEN 1 END)      AS ready_for_pickup,
                COUNT(CASE WHEN order_status = 'FOR_DELIVERY'     THEN 1 END)      AS for_delivery,
                COUNT(CASE WHEN order_status = 'COMPLETED'        THEN 1 END)      AS completed,
                COUNT(CASE WHEN order_status = 'CANCELLED'        THEN 1 END)      AS cancelled,
                COUNT(CASE WHEN order_status = 'REJECTED'         THEN 1 END)      AS rejected
            FROM tbl_orders
            WHERE customer_id = ?
        ");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?? [];
    }

    public function getOrdersByCustomer(int $customer_id, string $status = 'ALL', string $search = ''): array {
        $sql = "
            SELECT
                o.order_id, o.order_reference_no, o.created_at,
                o.total_price, o.order_status, o.payment_method, o.delivery_option,
                p.payment_id, p.payment_status, p.total_amount,
                COALESCE(SUM(pu.uploaded_amount), 0) AS amount_paid,
                -- Item summary for card display
                MAX(CASE WHEN i.r_product_id IS NOT NULL THEN rm.product_name
                         WHEN i.c_product_id IS NOT NULL THEN fd.design_name
                         ELSE NULL END) AS item_label,
                MAX(CASE WHEN i.r_product_id IS NOT NULL OR i.c_product_id IS NOT NULL THEN 1 ELSE 0 END) AS has_frame,
                MAX(CASE WHEN i.service_type = 'FRAME&PRINT' OR poi.printing_order_item_id IS NOT NULL THEN 1 ELSE 0 END) AS has_print,
                MAX(CASE WHEN i.c_product_id IS NOT NULL THEN 1 ELSE 0 END) AS is_custom,
                SUM(i.quantity) AS total_qty
            FROM tbl_orders o
            LEFT JOIN tbl_payment p                  ON o.order_id    = p.order_id
            LEFT JOIN tbl_payment_proof_uploads pu   ON p.payment_id  = pu.payment_id
            LEFT JOIN tbl_frame_order_items i        ON o.order_id    = i.order_id
            LEFT JOIN tbl_ready_made_product rm      ON i.r_product_id = rm.r_product_id
            LEFT JOIN tbl_custom_frame_product cfp   ON i.c_product_id = cfp.c_product_id
            LEFT JOIN tbl_frame_designs fd           ON cfp.frame_design_id = fd.frame_design_id
            LEFT JOIN tbl_printing_order_items poi   ON i.printing_order_item_id = poi.printing_order_item_id
            WHERE o.customer_id = ?
        ";
        $params = [$customer_id];
        $types  = "i";

        if ($status !== 'ALL') {
            $sql   .= " AND o.order_status = ?";
            $params[] = $status;
            $types   .= "s";
        }

        if (!empty($search)) {
            $sql   .= " AND (o.order_id LIKE ? OR o.order_reference_no LIKE ?)";
            $like      = "%$search%";
            $params[]  = $like;
            $params[]  = $like;
            $types    .= "ss";
        }

        $sql .= " GROUP BY o.order_id, p.payment_id ORDER BY o.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getOrderByIdForCustomer(int $order_id, int $customer_id): ?array {
        $stmt = $this->conn->prepare("
            SELECT
                o.*,
                p.payment_id, p.payment_status, p.total_amount, p.date_paid,
                COALESCE(SUM(pu.uploaded_amount), 0) AS amount_paid
            FROM tbl_orders o
            LEFT JOIN tbl_payment p                ON o.order_id   = p.order_id
            LEFT JOIN tbl_payment_proof_uploads pu ON p.payment_id = pu.payment_id
            WHERE o.order_id = ? AND o.customer_id = ?
            GROUP BY o.order_id, p.payment_id
        ");
        $stmt->bind_param("ii", $order_id, $customer_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function getPaymentProofs(int $payment_id): array {
        $stmt = $this->conn->prepare("
            SELECT * FROM tbl_payment_proof_uploads
            WHERE payment_id = ?
            ORDER BY upload_date ASC
        ");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getFrameImage(int $frame_design_id): ?string {
        $stmt = $this->conn->prepare("
            SELECT image_name FROM tbl_frame_design_images
            WHERE frame_design_id = ? AND is_primary = 1
            LIMIT 1
        ");
        $stmt->bind_param("i", $frame_design_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['image_name'] ?? null;
    }
}