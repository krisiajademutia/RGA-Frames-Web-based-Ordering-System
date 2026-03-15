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

    // UPGRADED: Unified Cart + Aliased Width/Height
    public function getCartItemsForCheckout(int $customer_id): array {
        // 1. Fetch Frame Items (with Aliases for width and height!)
        $stmt1 = $this->conn->prepare("
            SELECT
                ci.*,
                'FRAME' as category_type,
                rm.product_name    AS ready_name,
                fd.design_name     AS custom_design_name,
                cfp.custom_width   AS width, 
                cfp.custom_height  AS height
            FROM tbl_frame_order_items ci
            JOIN tbl_cart c                        ON ci.cart_id        = c.cart_id
            LEFT JOIN tbl_ready_made_product rm    ON ci.r_product_id   = rm.r_product_id
            LEFT JOIN tbl_custom_frame_product cfp ON ci.c_product_id   = cfp.c_product_id
            LEFT JOIN tbl_frame_designs fd         ON cfp.frame_design_id = fd.frame_design_id
            WHERE c.customer_id = ? AND ci.source_type = 'CART'
        ");
        $stmt1->bind_param("i", $customer_id);
        $stmt1->execute();
        $frames = $stmt1->get_result()->fetch_all(MYSQLI_ASSOC);

        // 2. Fetch Standalone Printing Items
        $stmt2 = $this->conn->prepare("
            SELECT 
                pi.*, 
                'PRINTING' as category_type,
                pt.paper_name
            FROM tbl_printing_order_items pi
            JOIN tbl_cart c ON pi.cart_id = c.cart_id
            LEFT JOIN tbl_paper_type pt ON pi.paper_type_id = pt.paper_type_id
            WHERE c.customer_id = ? AND pi.source_type = 'CART'
        ");
        $stmt2->bind_param("i", $customer_id);
        $stmt2->execute();
        $prints = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

        // 3. Merge them together
        return array_merge($frames, $prints);
    }

    // Returns count of COMPLETED past orders
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

    // UPGRADED: Now accepts $isBuyNow and $buyNowItemData to handle the Omni-channel saving!
    public function placeOrder(int $customer_id, array $orderData, array $cartItems, ?array $paymentProof, bool $isBuyNow = false, ?array $buyNowItemData = null): bool {
        $this->conn->begin_transaction();

        try {
            // 1. Insert Base Order
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

            // 2. Insert Payment Record
            $stmt2 = $this->conn->prepare("
                INSERT INTO tbl_payment (order_id, payment_status, total_amount)
                VALUES (?, 'PENDING', ?)
            ");
            $stmt2->bind_param("id", $order_id, $orderData['total_price']);
            $stmt2->execute();
            $payment_id = $this->conn->insert_id;

            // 3. Payment Proof (If GCash)
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

            // 4. THE SMART SAVER (Buy Now vs Cart)
            if ($isBuyNow && $buyNowItemData) {
                $itemType = $buyNowItemData['item_type'] ?? 'CUSTOM_FRAME';
                $qty = (int)($buyNowItemData['quantity'] ?? 1);
                $subTotal = (float)$orderData['sub_total']; 

                if ($itemType === 'CUSTOM_FRAME') {
                    // Create Custom Frame profile
                    $stmtCF = $this->conn->prepare("
                        INSERT INTO tbl_custom_frame_product 
                        (service_type, frame_type_id, frame_design_id, frame_color_id, frame_size_id, custom_width, custom_height, primary_matboard_id, secondary_matboard_id, mount_type_id, paper_type_id, image_path) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmtCF->bind_param("siiiidddiiis", 
                        $buyNowItemData['service_type'], $buyNowItemData['frame_type_id'], $buyNowItemData['frame_design_id'], 
                        $buyNowItemData['frame_color_id'], $buyNowItemData['frame_size_id'], $buyNowItemData['width'], 
                        $buyNowItemData['height'], $buyNowItemData['primary_matboard_id'], $buyNowItemData['secondary_matboard_id'], 
                        $buyNowItemData['mount_type_id'], $buyNowItemData['paper_type_id'], $buyNowItemData['image_path']
                    );
                    $stmtCF->execute();
                    $c_product_id = $this->conn->insert_id;
                    
                    // Link to order items
                    $stmtOrder = $this->conn->prepare("INSERT INTO tbl_frame_order_items (order_id, source_type, c_product_id, quantity, sub_total) VALUES (?, 'ORDER', ?, ?, ?)");
                    $stmtOrder->bind_param("iiid", $order_id, $c_product_id, $qty, $subTotal);
                    $stmtOrder->execute();

                } elseif ($itemType === 'PRINTING') {
                    // Save straight to printing table
                    $stmtPrint = $this->conn->prepare("
                        INSERT INTO tbl_printing_order_items 
                        (order_id, source_type, paper_type_id, width_inch, height_inch, image_path, quantity, sub_total) 
                        VALUES (?, 'ORDER', ?, ?, ?, ?, ?, ?)
                    ");
                    $stmtPrint->bind_param("iiddsid", 
                        $order_id, $buyNowItemData['paper_type_id'], $buyNowItemData['width'], 
                        $buyNowItemData['height'], $buyNowItemData['image_path'], $qty, $subTotal
                    );
                    $stmtPrint->execute();

                } elseif ($itemType === 'READY_MADE') {
                    // Save straight to frame order table using R_product_id
                    $stmtRM = $this->conn->prepare("INSERT INTO tbl_frame_order_items (order_id, source_type, r_product_id, quantity, sub_total) VALUES (?, 'ORDER', ?, ?, ?)");
                    $stmtRM->bind_param("iiid", $order_id, $buyNowItemData['r_product_id'], $qty, $subTotal);
                    $stmtRM->execute();
                }

            } else {
                // It's a CART checkout - Move ALL items from Cart to Order
                
                // Move Frame Items
                $this->conn->query("
                    UPDATE tbl_frame_order_items 
                    SET source_type = 'ORDER', order_id = $order_id, cart_id = NULL 
                    WHERE cart_id = (SELECT cart_id FROM tbl_cart WHERE customer_id = $customer_id) 
                    AND source_type = 'CART'
                ");
                
                // Update linked printing items (Frame + Print combo)
                $this->conn->query("
                    UPDATE tbl_printing_order_items poi 
                    JOIN tbl_frame_order_items foi ON poi.printing_order_item_id = foi.printing_order_item_id 
                    SET poi.order_id = $order_id, poi.cart_id = NULL 
                    WHERE foi.order_id = $order_id
                ");
                
                // Move Standalone Printing Items
                $this->conn->query("
                    UPDATE tbl_printing_order_items 
                    SET source_type = 'ORDER', order_id = $order_id, cart_id = NULL 
                    WHERE cart_id = (SELECT cart_id FROM tbl_cart WHERE customer_id = $customer_id) 
                    AND source_type = 'CART'
                ");
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log('CheckoutRepository::placeOrder error: ' . $e->getMessage());
            return false;
        }
    }
}