<?php
// classes/CustomFrame/Repository/CustomFrameRepository.php

class CustomFrameRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ── Lookup fetchers ──────────────────────────────────

    public function getActiveFrameTypes(): array {
        $r = $this->conn->query("SELECT * FROM tbl_frame_types WHERE is_active=1 ORDER BY type_name");
        return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getActiveFrameDesigns(): array {
        $r = $this->conn->query("
            SELECT fd.*, fdi.image_name AS primary_image
            FROM tbl_frame_designs fd
            LEFT JOIN tbl_frame_design_images fdi
                ON fd.frame_design_id = fdi.frame_design_id AND fdi.is_primary = 1
            WHERE fd.is_active = 1
            ORDER BY fd.design_name
        ");
        return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getActiveFrameColors(): array {
        $r = $this->conn->query("SELECT * FROM tbl_frame_colors WHERE is_active=1 ORDER BY color_name");
        return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getActiveFrameSizes(): array {
        $r = $this->conn->query("SELECT * FROM tbl_frame_sizes WHERE is_active=1 ORDER BY width_inch ASC, height_inch ASC");
        return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getActiveMatboardColors(): array {
        $r = $this->conn->query("SELECT * FROM tbl_matboard_colors WHERE is_active=1 ORDER BY matboard_color_name");
        return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getActiveMountTypes(): array {
        $r = $this->conn->query("SELECT * FROM tbl_mount_type WHERE is_active=1 ORDER BY mount_name");
        return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getActivePaperTypes(): array {
        $r = $this->conn->query("SELECT * FROM tbl_paper_type WHERE is_active=1 ORDER BY paper_name");
        return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getActiveFixedPrintPrices(): array {
        $r = $this->conn->query("SELECT * FROM tbl_fixed_print_prices");
        return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    }

    // ── Price fetchers ───────────────────────────────────

    public function getFrameTypeById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM tbl_frame_types WHERE frame_type_id = ? AND is_active = 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function getFrameDesignById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM tbl_frame_designs WHERE frame_design_id = ? AND is_active = 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function getFrameSizeById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM tbl_frame_sizes WHERE frame_size_id = ? AND is_active = 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function getMatboardById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM tbl_matboard_colors WHERE matboard_color_id = ? AND is_active = 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function getMountById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM tbl_mount_type WHERE mount_type_id = ? AND is_active = 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function getPaperTypeById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM tbl_paper_type WHERE paper_type_id = ? AND is_active = 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function getFixedPrintPrice(int $paperTypeId, float $width, float $height): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM tbl_fixed_print_prices WHERE paper_type_id = ? AND width_inch = ? AND height_inch = ? LIMIT 1");
        $stmt->bind_param("idd", $paperTypeId, $width, $height);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    // ── Write operations ─────────────────────────────────

    public function insertCustomFrameProduct(
        ?int $frameTypeId, ?int $frameDesignId, ?int $frameColorId,
        float $customWidth, float $customHeight,
        float $calculatedPrice
    ): int {
        $stmt = $this->conn->prepare("
            INSERT INTO tbl_custom_frame_product
                (frame_type_id, frame_design_id, frame_color_id,
                 custom_width, custom_height, calculated_price)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiiddd",
            $frameTypeId, $frameDesignId, $frameColorId,
            $customWidth, $customHeight, $calculatedPrice
        );
        $stmt->execute();
        return (int)$this->conn->insert_id;
    }

    public function insertPrintingOrderItem(
        ?int $cartId, ?int $orderId, int $paperTypeId,
        string $imagePath, float $width, float $height,
        int $quantity, float $subTotal
    ): int {
        $stmt = $this->conn->prepare("
            INSERT INTO tbl_printing_order_items
                (cart_id, order_id, paper_type_id, image_path,
                 width_inch, height_inch, quantity, sub_total)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiisddid",
            $cartId, $orderId, $paperTypeId, $imagePath,
            $width, $height, $quantity, $subTotal
        );
        $stmt->execute();
        return (int)$this->conn->insert_id;
    }

    public function getOrCreateCart(int $customerId): int {
        $stmt = $this->conn->prepare("SELECT cart_id FROM tbl_cart WHERE customer_id = ?");
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) return (int)$row['cart_id'];

        $stmt2 = $this->conn->prepare("INSERT INTO tbl_cart (customer_id) VALUES (?)");
        $stmt2->bind_param("i", $customerId);
        $stmt2->execute();
        return (int)$this->conn->insert_id;
    }

    public function insertFrameOrderItem(
        string $frameCategory, ?int $rProductId, ?int $cProductId,
        string $sourceType, ?int $cartId, ?int $orderId,
        string $serviceType, ?int $printingOrderItemId,
        ?int $primaryMatboardId, ?int $secondaryMatboardId,
        ?int $mountTypeId, int $quantity,
        float $basePrice, float $extraPrice, float $subTotal
    ): int {
        $stmt = $this->conn->prepare("
            INSERT INTO tbl_frame_order_items
                (frame_category, r_product_id, c_product_id, source_type,
                 cart_id, order_id, service_type, printing_order_item_id,
                 primary_matboard_id, secondary_matboard_id, mount_type_id,
                 quantity, base_price, extra_price, sub_total)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("siiisiiiiiidddd",
            $frameCategory, $rProductId, $cProductId, $sourceType,
            $cartId, $orderId, $serviceType, $printingOrderItemId,
            $primaryMatboardId, $secondaryMatboardId, $mountTypeId,
            $quantity, $basePrice, $extraPrice, $subTotal
        );
        $stmt->execute();
        return (int)$this->conn->insert_id;
    }

    // Updated: now accepts sub_total and discount_amount
    public function insertOrder(
        int $customerId, string $refNo,
        float $subTotal, float $discountAmount, float $totalPrice,
        string $paymentMethod, string $deliveryOption, ?string $deliveryAddress
    ): int {
        $stmt = $this->conn->prepare("
            INSERT INTO tbl_orders
                (customer_id, order_reference_no, sub_total, discount_amount,
                 total_price, payment_method, order_status, delivery_option, delivery_address)
            VALUES (?, ?, ?, ?, ?, ?, 'PENDING', ?, ?)
        ");
        $stmt->bind_param("isdddsss",
            $customerId, $refNo,
            $subTotal, $discountAmount, $totalPrice,
            $paymentMethod, $deliveryOption, $deliveryAddress
        );
        $stmt->execute();
        return (int)$this->conn->insert_id;
    }

    public function insertPayment(int $orderId, float $totalAmount): int {
        $stmt = $this->conn->prepare("
            INSERT INTO tbl_payment (order_id, total_amount, payment_status)
            VALUES (?, ?, 'PENDING')
        ");
        $stmt->bind_param("id", $orderId, $totalAmount);
        $stmt->execute();
        return (int)$this->conn->insert_id;
    }
}