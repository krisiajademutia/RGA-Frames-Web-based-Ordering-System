<?php
// classes/Checkout/Repository/CheckoutRepository.php

class CheckoutRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

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
        // 1. Fetch Frame Items
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
            WHERE c.customer_id = ? AND pi.order_id IS NULL
        ");
        $stmt2->bind_param("i", $customer_id);
        $stmt2->execute();
        $prints = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

        return array_merge($frames, $prints);
    }

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

            // 4. THE OMNI-CHANNEL SAVER
            if ($isBuyNow && $buyNowItemData) {
                $itemType = $buyNowItemData['item_type'] ?? 'CUSTOM_FRAME';
                $qty      = (int)($buyNowItemData['quantity'] ?? 1);
                $subTotal = (float)$orderData['sub_total'];

                if ($itemType === 'CUSTOM_FRAME') {
                    $service_type   = $buyNowItemData['service_type'] ?? 'FRAME_ONLY';
                    $f_type_id      = !empty($buyNowItemData['frame_type_id'])   ? $buyNowItemData['frame_type_id']   : null;
                    $f_design_id    = !empty($buyNowItemData['frame_design_id']) ? $buyNowItemData['frame_design_id'] : null;
                    $f_color_id     = !empty($buyNowItemData['frame_color_id'])  ? $buyNowItemData['frame_color_id']  : null;
                    $custom_w       = (float)($buyNowItemData['width']  ?? 0);
                    $custom_h       = (float)($buyNowItemData['height'] ?? 0);
                    $mat1_id        = !empty($buyNowItemData['primary_matboard_id'])   ? $buyNowItemData['primary_matboard_id']   : null;
                    $mat2_id        = !empty($buyNowItemData['secondary_matboard_id']) ? $buyNowItemData['secondary_matboard_id'] : null;
                    $mount_id       = !empty($buyNowItemData['mount_type_id']) ? $buyNowItemData['mount_type_id'] : null;

                    // A. Create Custom Frame Profile
                    $stmtCF = $this->conn->prepare("
                        INSERT INTO tbl_custom_frame_product 
                        (frame_type_id, frame_design_id, frame_color_id, custom_width, custom_height, calculated_price) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmtCF->bind_param("iiiddd", $f_type_id, $f_design_id, $f_color_id, $custom_w, $custom_h, $subTotal);
                    $stmtCF->execute();
                    $c_product_id = $this->conn->insert_id;

                    // B. Attached Print
                    $printing_id = null;
                    if ($service_type === 'FRAME&PRINT') {
                        $paper_id  = !empty($buyNowItemData['paper_type_id']) ? $buyNowItemData['paper_type_id'] : null;
                        $img_path  = $buyNowItemData['image_path'] ?? null;
                        $zero_price = 0.00;

                        $stmtPrint = $this->conn->prepare("
                            INSERT INTO tbl_printing_order_items 
                            (order_id, paper_type_id, image_path, width_inch, height_inch, quantity, sub_total) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmtPrint->bind_param("iisddid", $order_id, $paper_id, $img_path, $custom_w, $custom_h, $qty, $zero_price);
                        $stmtPrint->execute();
                        $printing_id = $this->conn->insert_id;
                    }

                    // C. Link Main Order
                    $stmtOrder = $this->conn->prepare("
                        INSERT INTO tbl_frame_order_items 
                        (order_id, source_type, frame_category, c_product_id, service_type, printing_order_item_id, primary_matboard_id, secondary_matboard_id, mount_type_id, quantity, base_price, extra_price, sub_total) 
                        VALUES (?, 'ORDER', 'CUSTOM', ?, ?, ?, ?, ?, ?, ?, 0, 0, ?)
                    ");
                    $stmtOrder->bind_param("iisiiiiid", $order_id, $c_product_id, $service_type, $printing_id, $mat1_id, $mat2_id, $mount_id, $qty, $subTotal);
                    $stmtOrder->execute();

                } elseif ($itemType === 'PRINTING') {
                    $p_paper_id = !empty($buyNowItemData['paper_type_id']) ? $buyNowItemData['paper_type_id'] : null;
                    $p_width    = (float)($buyNowItemData['width']  ?? 0);
                    $p_height   = (float)($buyNowItemData['height'] ?? 0);
                    $p_image    = (string)($buyNowItemData['image_path'] ?? '');

                    $stmtPrint = $this->conn->prepare("
                        INSERT INTO tbl_printing_order_items 
                        (order_id, paper_type_id, image_path, width_inch, height_inch, quantity, sub_total) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmtPrint->bind_param("iisddid", $order_id, $p_paper_id, $p_image, $p_width, $p_height, $qty, $subTotal);
                    $stmtPrint->execute();

                } elseif ($itemType === 'READY_MADE') {
                    // ── FIX: respect the actual service_type (FRAME_ONLY or FRAME&PRINT) ──
                    $r_product_id   = !empty($buyNowItemData['r_product_id']) ? (int)$buyNowItemData['r_product_id'] : null;
                    $rm_service     = strtoupper($buyNowItemData['service_type'] ?? 'FRAME_ONLY');
                    $printingItemId = null;

                    if ($rm_service === 'FRAME&PRINT') {
                        $rm_paper_id    = (int)($buyNowItemData['paper_type_id'] ?? 0);
                        $rm_image_path  = (string)($buyNowItemData['image_path'] ?? '');
                        $rm_width       = (float)($buyNowItemData['width']  ?? 0);
                        $rm_height      = (float)($buyNowItemData['height'] ?? 0);
                        $rm_print_price = (float)($buyNowItemData['print_price'] ?? 0);

                        $stmtPrint = $this->conn->prepare("
                            INSERT INTO tbl_printing_order_items 
                            (order_id, paper_type_id, image_path, width_inch, height_inch, quantity, sub_total) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmtPrint->bind_param('iisddid',
                            $order_id, $rm_paper_id, $rm_image_path,
                            $rm_width, $rm_height, $qty, $rm_print_price
                        );
                        $stmtPrint->execute();
                        $printingItemId = (int)$this->conn->insert_id;
                    }

                    $stmtRM = $this->conn->prepare("
                        INSERT INTO tbl_frame_order_items 
                        (order_id, source_type, frame_category, r_product_id,
                         service_type, printing_order_item_id, quantity, base_price, extra_price, sub_total) 
                        VALUES (?, 'ORDER', 'READY_MADE', ?, ?, ?, ?, 0, 0, ?)
                    ");
                    $stmtRM->bind_param('iisiid',
                        $order_id, $r_product_id, $rm_service,
                        $printingItemId, $qty, $subTotal
                    );
                    $stmtRM->execute();
                }

            } else {
                // IT'S A CART CHECKOUT
                $cartQuery = $this->conn->query("SELECT cart_id FROM tbl_cart WHERE customer_id = $customer_id LIMIT 1");

                if ($cartQuery && $cartQuery->num_rows > 0) {
                    $cartRow = $cartQuery->fetch_assoc();
                    $c_id = (int)$cartRow['cart_id'];

                    $this->conn->query("
                        UPDATE tbl_frame_order_items 
                        SET source_type = 'ORDER', order_id = $order_id, cart_id = NULL 
                        WHERE cart_id = $c_id AND source_type = 'CART'
                    ");

                    $this->conn->query("
                        UPDATE tbl_printing_order_items 
                        SET order_id = $order_id, cart_id = NULL 
                        WHERE cart_id = $c_id
                    ");

                    $this->conn->query("DELETE FROM tbl_cart WHERE cart_id = $c_id");
                }
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