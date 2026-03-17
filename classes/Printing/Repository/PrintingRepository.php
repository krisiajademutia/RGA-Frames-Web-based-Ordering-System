<?php
// classes/Printing/Repository/PrintingRepository.php

class PrintingRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getPaperTypeById(int $paperTypeId): ?array {
        $stmt = $this->conn->prepare("SELECT max_width_inch, max_height_inch FROM tbl_paper_type WHERE paper_type_id = ?");
        $stmt->bind_param("i", $paperTypeId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function getOrCreateCart(int $customerId): int {
        // 🔥 FIXED: Added ORDER BY cart_id DESC so it targets your NEWEST cart, not an old checked-out one!
        $stmt = $this->conn->prepare("SELECT cart_id FROM tbl_cart WHERE customer_id = ? ORDER BY cart_id DESC LIMIT 1");
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return (int)$result->fetch_assoc()['cart_id'];
        }

        $stmt_ins = $this->conn->prepare("INSERT INTO tbl_cart (customer_id) VALUES (?)");
        $stmt_ins->bind_param("i", $customerId);
        $stmt_ins->execute();
        return $stmt_ins->insert_id;
    }

    public function insertPrintingItem(int $cartId, int $paperTypeId, string $filename, float $w, float $h, int $qty, float $subTotal): bool {
        $sql = "INSERT INTO tbl_printing_order_items (cart_id, paper_type_id, image_path, width_inch, height_inch, quantity, sub_total) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisddid", $cartId, $paperTypeId, $filename, $w, $h, $qty, $subTotal);
        return $stmt->execute();
    }
}