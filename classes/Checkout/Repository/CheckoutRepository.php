<?php
// classes/Checkout/Repository/CheckoutRepository.php

// 🟢 NEW: Pull in our SOLID Strategies
require_once __DIR__ . '/OrderStrategies.php';

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

        $selectedIds  = array_values(array_filter(array_map('intval',  (array)($_SESSION['selected_cart_items']       ?? [])), fn($id) => $id > 0));
        $selectedPIds = array_values(array_filter(array_map('intval',  (array)($_SESSION['selected_print_cart_items'] ?? [])), fn($id) => $id > 0));

        // 1. Fetch selected Frame Items
        $frames = [];
        if (!empty($selectedIds)) {
            $ph    = implode(',', array_fill(0, count($selectedIds), '?'));
            $types = 'i' . str_repeat('i', count($selectedIds));

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
                  AND ci.item_id IN ($ph)
            ");
            $stmt1->bind_param($types, $customer_id, ...$selectedIds);
            $stmt1->execute();
            $frames = $stmt1->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        // 2. Fetch Printing Items linked to selected frame items (FRAME&PRINT)
        $linkedPrintingIds = array_values(array_filter(array_column($frames, 'printing_order_item_id')));
        $prints = [];
        if (!empty($linkedPrintingIds)) {
            $pPh    = implode(',', array_fill(0, count($linkedPrintingIds), '?'));
            $pTypes = 'i' . str_repeat('i', count($linkedPrintingIds));
            $stmt2  = $this->conn->prepare("
                SELECT pi.*, 'PRINTING' as category_type, pt.paper_name
                FROM tbl_printing_order_items pi
                JOIN tbl_cart c ON pi.cart_id = c.cart_id
                LEFT JOIN tbl_paper_type pt ON pi.paper_type_id = pt.paper_type_id
                WHERE c.customer_id = ? AND pi.order_id IS NULL
                  AND pi.printing_order_item_id IN ($pPh)
            ");
            $stmt2->bind_param($pTypes, $customer_id, ...$linkedPrintingIds);
            $stmt2->execute();
            $prints = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        // 3. Fetch selected standalone Print-Only items
        $standalonePrints = [];
        if (!empty($selectedPIds)) {
            $spPh    = implode(',', array_fill(0, count($selectedPIds), '?'));
            $spTypes = 'i' . str_repeat('i', count($selectedPIds));
            $stmt3   = $this->conn->prepare("
                SELECT pi.*, 'PRINTING' as category_type, pt.paper_name
                FROM tbl_printing_order_items pi
                JOIN tbl_cart c ON pi.cart_id = c.cart_id
                LEFT JOIN tbl_paper_type pt ON pi.paper_type_id = pt.paper_type_id
                WHERE c.customer_id = ? AND pi.order_id IS NULL
                  AND pi.printing_order_item_id IN ($spPh)
                  AND NOT EXISTS (
                      SELECT 1 FROM tbl_frame_order_items f
                      WHERE f.printing_order_item_id = pi.printing_order_item_id
                        AND f.source_type = 'CART'
                  )
            ");
            $stmt3->bind_param($spTypes, $customer_id, ...$selectedPIds);
            $stmt3->execute();
            $standalonePrints = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        return array_merge($frames, $prints, $standalonePrints);
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

            // 4. THE OMNI-CHANNEL SAVER (🟢 NOW 100% SOLID! 🟢)
            if ($isBuyNow && $buyNowItemData) {
                $itemType = $buyNowItemData['item_type'] ?? 'CUSTOM_FRAME';
                $qty      = (int)($buyNowItemData['quantity'] ?? 1);
                $subTotal = (float)$orderData['sub_total']; 

                // Look how clean this is! The Repository uses the Factory to get the right strategy
                $saver = ItemSaverFactory::make($itemType);
                
                // Then it just delegates the save command. No if/else statements needed!
                $saver->saveItem($this->conn, $order_id, $buyNowItemData, $subTotal, $qty);

            } else {
                // IT'S A CART CHECKOUT - The completely bulletproof transfer
                $cartQuery = $this->conn->query("SELECT cart_id FROM tbl_cart WHERE customer_id = $customer_id ORDER BY cart_id DESC LIMIT 1");

                if ($cartQuery && $cartQuery->num_rows > 0) {
                    $cartRow = $cartQuery->fetch_assoc();
                    $c_id = (int)$cartRow['cart_id'];

                    // 1. Safely move Frame Items to the new Order ID
                    $this->conn->query("
                        UPDATE tbl_frame_order_items 
                        SET source_type = 'ORDER', order_id = $order_id, cart_id = NULL 
                        WHERE cart_id = $c_id AND source_type = 'CART'
                    ");
                    
                    // 2. Safely move Printing Items to the new Order ID
                    $this->conn->query("
                        UPDATE tbl_printing_order_items 
                        SET order_id = $order_id, cart_id = NULL 
                        WHERE cart_id = $c_id
                    ");

                    // 3. Clean up the empty cart so they can shop again!
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