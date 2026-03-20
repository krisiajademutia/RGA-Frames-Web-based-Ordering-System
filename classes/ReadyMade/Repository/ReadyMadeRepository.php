<?php

namespace Classes\ReadyMade\Repository;

interface IReadyMadeRepository
{
    public function getAll(): array;
    public function getById(int $id): ?array;
    public function getProductImages(int $productId): array;
    public function addToCart(int $customerId, int $productId, int $quantity, float $unitPrice): bool;
    public function decrementStock(int $productId, int $quantity): bool;
    public function getMountTypes(): array;
}

class ReadyMadeRepository implements IReadyMadeRepository
{
    private \mysqli $db;

    public function __construct(\mysqli $conn)
    {
        $this->db = $conn;
    }

    public function getAll(): array
    {
        $sql = "
            SELECT
                p.r_product_id,
                p.product_name,
                p.width,
                p.height,
                p.product_price,
                t.type_name,
                d.design_name,
                c.color_name,
                IFNULL(s.quantity, 0) AS stock,
                (
                    SELECT image_name
                    FROM tbl_ready_made_product_images
                    WHERE r_product_id = p.r_product_id
                    ORDER BY is_primary DESC, image_id ASC
                    LIMIT 1
                ) AS image_name
            FROM tbl_ready_made_product p
            LEFT JOIN tbl_frame_types   t ON p.frame_type_id   = t.frame_type_id
            LEFT JOIN tbl_frame_designs d ON p.frame_design_id = d.frame_design_id
            LEFT JOIN tbl_frame_colors  c ON p.frame_color_id  = c.frame_color_id
            LEFT JOIN tbl_ready_made_product_stocks s ON p.r_product_id = s.r_product_id
            ORDER BY p.r_product_id DESC
        ";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, IFNULL(s.quantity, 0) AS stock,
                t.type_name, d.design_name, c.color_name
            FROM tbl_ready_made_product p
            LEFT JOIN tbl_frame_types   t ON p.frame_type_id   = t.frame_type_id
            LEFT JOIN tbl_frame_designs d ON p.frame_design_id = d.frame_design_id
            LEFT JOIN tbl_frame_colors  c ON p.frame_color_id  = c.frame_color_id
            LEFT JOIN tbl_ready_made_product_stocks s ON p.r_product_id = s.r_product_id
            WHERE p.r_product_id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function getProductImages(int $productId): array
    {
        $stmt = $this->db->prepare("
            SELECT image_name, is_primary
            FROM tbl_ready_made_product_images
            WHERE r_product_id = ?
            ORDER BY is_primary DESC
        ");
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addToCart(int $customerId, int $productId, int $quantity, float $unitPrice): bool
    {
        $stmt = $this->db->prepare("SELECT cart_id FROM tbl_cart WHERE customer_id = ? LIMIT 1");
        $stmt->bind_param('i', $customerId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            $cartId = (int)$row['cart_id'];
        } else {
            $ins = $this->db->prepare("INSERT INTO tbl_cart (customer_id) VALUES (?)");
            $ins->bind_param('i', $customerId);
            $ins->execute();
            $cartId = (int)$this->db->insert_id;
        }

        $chk = $this->db->prepare("
            SELECT item_id, quantity FROM tbl_frame_order_items
            WHERE cart_id = ? AND r_product_id = ? AND source_type = 'CART'
                AND frame_category = 'READY_MADE'
            LIMIT 1
        ");
        $chk->bind_param('ii', $cartId, $productId);
        $chk->execute();
        $existing = $chk->get_result()->fetch_assoc();

        if ($existing) {
            $newQty  = $existing['quantity'] + $quantity;
            $newSub  = $newQty * $unitPrice;
            $upd = $this->db->prepare("
                UPDATE tbl_frame_order_items
                SET quantity = ?, sub_total = ?
                WHERE item_id = ?
            ");
            $upd->bind_param('idi', $newQty, $newSub, $existing['item_id']);
            return $upd->execute();
        }

        $subTotal = $quantity * $unitPrice;
        $svcType  = 'FRAME_ONLY';
        $insert   = $this->db->prepare("
            INSERT INTO tbl_frame_order_items
                (cart_id, source_type, frame_category, r_product_id,
                service_type, quantity, base_price, extra_price, sub_total)
            VALUES (?, 'CART', 'READY_MADE', ?, ?, ?, ?, 0, ?)
        ");
        $insert->bind_param('iisidd', $cartId, $productId, $svcType, $quantity, $unitPrice, $subTotal);
        return $insert->execute();
    }

    public function decrementStock(int $productId, int $quantity): bool
    {
        $stmt = $this->db->prepare("
            UPDATE tbl_ready_made_product_stocks
            SET quantity = GREATEST(0, quantity - ?)
            WHERE r_product_id = ?
        ");
        $stmt->bind_param('ii', $quantity, $productId);
        return $stmt->execute();
    }

    public function getMountTypes(): array
    {
        $result = $this->db->query("
            SELECT mount_type_id, mount_name, IFNULL(additional_fee, 0) AS additional_fee
            FROM tbl_mount_type
            WHERE is_active = 1
            ORDER BY additional_fee ASC, mount_type_id ASC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}