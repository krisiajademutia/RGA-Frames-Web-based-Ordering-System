<?php
// classes/ReadyMade/ReadyMadeDirectOrderService.php

require_once __DIR__ . '/../Order/DirectOrderInterface.php';

class ReadyMadeDirectOrderService implements DirectOrderInterface {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function placeBuyNow(int $customerId, array $data): array {
        try {
            $this->conn->begin_transaction();

            $refNo = "RGA-" . strtoupper(uniqid());
            $total = (float)($data['total_price'] ?? 0); 
            
            $payMethod = strtoupper($data['payment_method'] ?? 'CASH');
            if (!in_array($payMethod, ['CASH', 'GCASH'])) { $payMethod = 'CASH'; }
            
            $stmtOrder = $this->conn->prepare("INSERT INTO tbl_orders (customer_id, order_reference_no, sub_total, total_price, payment_method, order_status) VALUES (?, ?, ?, ?, ?, 'PENDING')");
            $stmtOrder->bind_param("isdds", $customerId, $refNo, $total, $total, $payMethod);
            $stmtOrder->execute();
            $orderId = $stmtOrder->insert_id;

            $productId = (int)($data['r_product_id'] ?? 0);
            $svcType   = $data['service_type'] ?? 'FRAME_ONLY';
            $priMat    = !empty($data['primary_matboard_id']) && $data['primary_matboard_id'] !== 'None' ? $data['primary_matboard_id'] : null;
            $secMat    = !empty($data['secondary_matboard_id']) && $data['secondary_matboard_id'] !== 'None' ? $data['secondary_matboard_id'] : null;
            $mount     = !empty($data['mount_type_id']) ? $data['mount_type_id'] : null;
            $printId   = !empty($data['printing_order_item_id']) ? $data['printing_order_item_id'] : null;
            $qty       = (int)($data['quantity'] ?? 1);
            $basePrice = (float)($data['base_price'] ?? 0);
            $extPrice  = (float)($data['extra_price'] ?? 0);
            $itemSubTotal = (float)($data['sub_total'] ?? 0);

            $stmtItem = $this->conn->prepare("
                INSERT INTO tbl_frame_order_items 
                (order_id, source_type, frame_category, r_product_id, service_type, 
                 primary_matboard_id, secondary_matboard_id, mount_type_id, 
                 printing_order_item_id, quantity, base_price, extra_price, sub_total) 
                VALUES (?, 'ORDER', 'READY_MADE', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            // FIXED: Exactly 11 characters to perfectly match the 11 variables!
            $stmtItem->bind_param("iissssiiddd", 
                $orderId, 
                $productId, 
                $svcType, 
                $priMat, 
                $secMat, 
                $mount, 
                $printId,
                $qty, 
                $basePrice, 
                $extPrice, 
                $itemSubTotal
            );
            $stmtItem->execute();

            if ($printId) {
                $updPrint = $this->conn->prepare("UPDATE tbl_printing_order_items SET order_id = ? WHERE printing_order_item_id = ?");
                $updPrint->bind_param("ii", $orderId, $printId);
                $updPrint->execute();
            }

            $stmtPay = $this->conn->prepare("INSERT INTO tbl_payment (order_id, total_amount, payment_status) VALUES (?, ?, 'PENDING')");
            $stmtPay->bind_param("id", $orderId, $total);
            $stmtPay->execute();

            $this->conn->commit();

            return [
                'success'      => true,
                'order_id'     => $orderId,
                'reference_no' => $refNo,
                'message'      => 'Order placed successfully.'
            ];

        } catch (\Exception $e) {
            $this->conn->rollback();
            return [
                'success' => false,
                'message' => 'Database Error: ' . $e->getMessage()
            ];
        }
    }
}
?>