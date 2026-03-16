<?php
require_once __DIR__ . '/../Order/DirectOrderInterface.php';

class PrintingDirectOrderService implements DirectOrderInterface {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function placeBuyNow(int $customerId, array $data): array {
    $this->conn->begin_transaction();
    try {
        $refNo = 'RGA-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
    
        // FIX: Check for 'sub_total' if 'total_price' is empty so it doesn't default to 0!
        $subTotal    = (float)($data['sub_total'] ?? $data['total_price'] ?? 0); 
        $discount    = (float)($data['discount_amount'] ?? 0);
        $finalTotal  = (float)($data['final_total'] ?? $data['total_price'] ?? $subTotal);
        
        $paymentMethod   = $data['payment_method'] ?? 'CASH';
        $deliveryOption  = $data['delivery_option'] ?? 'PICKUP';
        $deliveryAddress = $data['delivery_address'] ?? null;

        // 1. Insert Order
        // IMPORTANT: Verify that these column names match your tbl_orders exactly
        $stmt = $this->conn->prepare("
            INSERT INTO tbl_orders 
            (customer_id, order_reference_no, sub_total, discount_amount, total_price, payment_method, order_status, delivery_option, delivery_address) 
            VALUES (?, ?, ?, ?, ?, ?, 'PENDING', ?, ?)
        ");
        
        $stmt->bind_param("isdddsss", 
            $customerId, 
            $refNo, 
            $subTotal, 
            $discount, 
            $finalTotal, // This is what the customer actually pays
            $paymentMethod, 
            $deliveryOption, 
            $deliveryAddress
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Order insertion failed: " . $stmt->error);
        }
        $orderId = $this->conn->insert_id;

        // 2. Insert Printing Item
        // FIX: Some versions of your DB might use 'subtotal' (no underscore). 
        // Check your tbl_printing_order_items table!
        $stmtItem = $this->conn->prepare("
            INSERT INTO tbl_printing_order_items 
            (order_id, paper_type_id, image_path, width_inch, height_inch, quantity, sub_total) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $paperTypeId = (int)$data['paper_type_id'];
        $imagePath   = $data['image_path'];
        $width       = (float)$data['width'];
        $height      = (float)$data['height'];
        $quantity    = (int)$data['quantity'];

        $stmtItem->bind_param("iisddid", 
            $orderId, 
            $paperTypeId, 
            $imagePath, 
            $width, 
            $height, 
            $quantity, 
            $subTotal // This records the base price of the item
        );
        
        if (!$stmtItem->execute()) {
            throw new Exception("Printing item insertion failed: " . $stmtItem->error);
        }

        // 3. Insert Payment
        $stmtPay = $this->conn->prepare("INSERT INTO tbl_payment (order_id, total_amount, payment_status) VALUES (?, ?, 'PENDING')");
        $stmtPay->bind_param("id", $orderId, $finalTotal);
        
        if (!$stmtPay->execute()) {
            throw new Exception("Payment insertion failed: " . $stmtPay->error);
        }

        $this->conn->commit();
        return [
            'success'  => true, 
            'order_id' => $orderId, 
            'ref_no'   => $refNo
        ];

    } catch (Exception $e) {
        $this->conn->rollback();
        // Log error for debugging
        error_log("Printing BuyNow Error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
}