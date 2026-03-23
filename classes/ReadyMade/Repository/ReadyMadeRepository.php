<?php

namespace Classes\ReadyMade\Repository;

interface IReadyMadeRepository
{
    public function getAll(): array;
    public function getById(int $id): ?array;
    public function getProductImages(int $productId): array;
    public function decrementStock(int $productId, int $quantity): bool;
    
    // UPDATED INTERFACE to match the corrected add_to_cart function
    public function addToCart(int $customerId, array $itemData, int $cartId): bool;
    
    // NEW METHODS for fetching data required for backend recalculation
    public function getMatboardColors(): array;
    public function getMatboardColorPrice(int $colorId): float;
    public function getPaperTypes(): array;
    public function getPaperTypeMultiplier(int $paperTypeId): float;
    public function getMountTypes(): array;
    public function getMountTypeFee(int $mountTypeId): float;
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
                p.r_product_id, p.product_name, p.width, p.height, p.product_price,
                t.type_name, d.design_name, c.color_name,
                IFNULL(s.quantity, 0) AS stock,
                (SELECT image_name FROM tbl_ready_made_product_images WHERE r_product_id = p.r_product_id ORDER BY is_primary DESC, image_id ASC LIMIT 1) AS image_name
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
            SELECT image_name, is_primary FROM tbl_ready_made_product_images WHERE r_product_id = ? ORDER BY is_primary DESC
        ");
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

   public function addToCart(int $customerId, array $itemData, int $cartId): bool
    {
        $stmt = $this->db->prepare("
            SELECT item_id, quantity, sub_total FROM tbl_frame_order_items
            WHERE cart_id = ? AND r_product_id = ? AND source_type = 'CART'
                AND frame_category = 'READY_MADE'
                AND (primary_matboard_id = ? OR (primary_matboard_id IS NULL AND ? IS NULL))
                AND (secondary_matboard_id = ? OR (secondary_matboard_id IS NULL AND ? IS NULL))
                AND service_type = ?
            LIMIT 1
        ");
        
        $stmt->bind_param('iisssss', 
            $cartId, 
            $itemData['r_product_id'],
            $itemData['primary_matboard_id'], $itemData['primary_matboard_id'],
            $itemData['secondary_matboard_id'], $itemData['secondary_matboard_id'],
            $itemData['service_type']
        );
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();

        if ($existing) {
            $newQty  = $existing['quantity'] + $itemData['quantity'];
            $newSub  = ($itemData['base_price'] + $itemData['extra_price']) * $newQty;
            $upd = $this->db->prepare("UPDATE tbl_frame_order_items SET quantity = ?, sub_total = ? WHERE item_id = ?");
            $upd->bind_param('idi', $newQty, $newSub, $existing['item_id']);
            return $upd->execute();
        }

        $insert = $this->db->prepare("
            INSERT INTO tbl_frame_order_items
                (cart_id, source_type, frame_category, r_product_id, service_type, 
                 primary_matboard_id, secondary_matboard_id, mount_type_id, 
                 printing_order_item_id, quantity, base_price, extra_price, sub_total)
            VALUES (?, 'CART', 'READY_MADE', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // FIXED: Exactly 11 characters to perfectly match the 11 variables!
        $insert->bind_param('iissssiiddd', 
            $cartId, 
            $itemData['r_product_id'], 
            $itemData['service_type'], 
            $itemData['primary_matboard_id'], 
            $itemData['secondary_matboard_id'], 
            $itemData['mount_type_id'], 
            $itemData['printing_order_item_id'], 
            $itemData['quantity'], 
            $itemData['base_price'], 
            $itemData['extra_price'], 
            $itemData['sub_total']
        );
        return $insert->execute();
    }

    public function decrementStock(int $productId, int $quantity): bool
    {
        $stmt = $this->db->prepare("UPDATE tbl_ready_made_product_stocks SET quantity = GREATEST(0, quantity - ?) WHERE r_product_id = ?");
        $stmt->bind_param('ii', $quantity, $productId);
        return $stmt->execute();
    }

    public function getMatboardColors(): array
    {
        $result = $this->db->query("
            SELECT matboard_color_id, matboard_color_name, IFNULL(base_price, 0) AS base_price, image_name
            FROM tbl_matboard_colors
            WHERE is_active = 1
            ORDER BY matboard_color_name ASC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getMatboardColorPrice(int $colorId): float
    {
        $stmt = $this->db->prepare("SELECT IFNULL(base_price, 0) AS base_price FROM tbl_matboard_colors WHERE matboard_color_id = ?");
        $stmt->bind_param('i', $colorId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? (float)$row['base_price'] : 0;
    }

    public function getPaperTypes(): array
    {
        $result = $this->db->query("SELECT * FROM tbl_paper_type WHERE is_active = 1");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // --- NEW: Hardcoded multipliers as multipliers are missing in the database table ---
    public function getPaperTypeMultiplier(int $paperTypeId): float
    {
        $stmt = $this->db->prepare("SELECT LOWER(paper_name) AS paper_name FROM tbl_paper_type WHERE paper_type_id = ?");
        $stmt->bind_param('i', $paperTypeId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) return 0;
        
        $paperName = $row['paper_name'];
        if (str_contains($paperName, 'photo')) return 1.5;
        if (str_contains($paperName, 'canvas')) return 2.5;
        return 0; // Or whatever your default is
    }

    public function getMountTypes(): array
    {
        $result = $this->db->query("SELECT mount_type_id, mount_name, IFNULL(additional_fee, 0) AS additional_fee FROM tbl_mount_type WHERE is_active = 1 ORDER BY additional_fee ASC, mount_type_id ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getMountTypeFee(int $mountTypeId): float
    {
        $stmt = $this->db->prepare("SELECT IFNULL(additional_fee, 0) AS additional_fee FROM tbl_mount_type WHERE mount_type_id = ?");
        $stmt->bind_param('i', $mountTypeId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? (float)$row['additional_fee'] : 0;
    }
}