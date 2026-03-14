<?php
// classes/Checkout/Repository/CheckoutRepository.php

class CheckoutRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Now also fetches customer_type for the photographer discount check
    public function getCustomerDetails(int $customer_id): ?array {
        $stmt = $this->conn->prepare("
            SELECT first_name, last_name, phone_number, email, customer_type
            FROM tbl_customer
            WHERE customer_id = ?
        ");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function getCartItemsForCheckout(int $customer_id): array {
        $stmt = $this->conn->prepare("
            SELECT
                ci.*,
                rm.product_name    AS ready_name,
                fd.design_name     AS custom_design_name,
                cfp.custom_width,
                cfp.custom_height
            FROM tbl_frame_order_items ci
            JOIN tbl_cart c                        ON ci.cart_id        = c.cart_id
            LEFT JOIN tbl_ready_made_product rm    ON ci.r_product_id   = rm.r_product_id
            LEFT JOIN tbl_custom_frame_product cfp ON ci.c_product_id   = cfp.c_product_id
            LEFT JOIN tbl_frame_designs fd         ON cfp.frame_design_id = fd.frame_design_id
            WHERE c.customer_id = ? AND ci.source_type = 'CART'
        ");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Returns count of COMPLETED past orders — used for repeat customer discount
    public function getCompletedOrderCount(int $customer_id): int {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS cnt
            FROM tbl_orders
            WHERE customer_id = ? AND order_status = 'COMPLETED'
        ");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    public function placeOrder(int $customer_id, array $orderData, array $cartItems, ?array $paymentProof): bool {
        $this->conn->begin_transaction();

        try {
            // 1. Insert order — now includes sub_total and discount_amount
            $stmt1 = $this->conn->prepare("
                INSERT INTO tbl_orders
                    (customer_id, order_reference_no, sub_total, discount_amount,
                     total_price, order_status, payment_method,
                     delivery_option, delivery_address)
                VALUES (?, ?, ?, ?, ?, 'PENDING', ?, ?, ?)
            ");
            $stmt1->bind_param("isdddsss",
                $customer_id,
                $orderData['reference_no'],
                $orderData['sub_total'],
                $orderData['discount_amount'],
                $orderData['total_price'],
                $orderData['payment_method'],
                $orderData['delivery_option'],
                $orderData['delivery_address']
            );
            $stmt1->execute();
            $order_id = $this->conn->insert_id;

            // 2. Insert payment record
            $stmt2 = $this->conn->prepare("
                INSERT INTO tbl_payment (order_id, payment_status, total_amount)
                VALUES (?, 'PENDING', ?)
            ");
            $stmt2->bind_param("id", $order_id, $orderData['total_price']);
            $stmt2->execute();
            $payment_id = $this->conn->insert_id;

            // 3. Insert GCash payment proof if provided
            if ($paymentProof) {
                $stmt3 = $this->conn->prepare("
                    INSERT INTO tbl_payment_proof_uploads
                        (payment_id, payment_proof, uploaded_amount, verification_status)
                    VALUES (?, ?, ?, 'Pending Verification')
                ");
                $stmt3->bind_param("isd",
                    $payment_id,
                    $paymentProof['file_path'],
                    $paymentProof['amount']
                );
                $stmt3->execute();
            }

            // 4. Move cart items → order
            $stmt4 = $this->conn->prepare("
                UPDATE tbl_frame_order_items
                SET source_type = 'ORDER', order_id = ?, cart_id = NULL
                WHERE cart_id = (SELECT cart_id FROM tbl_cart WHERE customer_id = ?)
                  AND source_type = 'CART'
            ");
            $stmt4->bind_param("ii", $order_id, $customer_id);
            $stmt4->execute();

            // 5. Update linked printing order items
            $stmt5 = $this->conn->prepare("
                UPDATE tbl_printing_order_items poi
                JOIN tbl_frame_order_items foi ON poi.printing_order_item_id = foi.printing_order_item_id
                SET poi.order_id = ?, poi.cart_id = NULL
                WHERE foi.order_id = ?
            ");
            $stmt5->bind_param("ii", $order_id, $order_id);
            $stmt5->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log('CheckoutRepository::placeOrder error: ' . $e->getMessage());
            return false;
        }
    }
}