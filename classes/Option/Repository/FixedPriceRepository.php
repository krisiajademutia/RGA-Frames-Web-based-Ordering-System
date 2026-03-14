<?php

class FixedPriceRepository {
    private $db;

    public function __construct($db) { 
        $this->db = $db; 
    }

    /**
     * Fetches a single fixed price record by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tbl_fixed_print_prices WHERE fixed_price_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    /**
     * Creates a new entry in tbl_fixed_print_prices
     */
    public function create(array $data): bool {
        $paperTypeId = (int)($data['paper_type_id'] ?? 0);
        $dimension   = $data['dimension'] ?? '';
        $widthInch   = (float)($data['width_inch'] ?? 0);
        $heightInch  = (float)($data['height_inch'] ?? 0);
        $fixedPrice  = (float)($data['fixed_price'] ?? 0);

        $sql = "INSERT INTO tbl_fixed_print_prices 
                (paper_type_id, dimension, width_inch, height_inch, fixed_price) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("isddd", 
            $paperTypeId, $dimension, $widthInch, $heightInch, $fixedPrice
        );

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Fetches all fixed prices with joined paper names for the table view
     */
    public function getAll() {
        return $this->db->query("SELECT f.*, p.paper_name 
                                FROM tbl_fixed_print_prices f 
                                JOIN tbl_paper_type p ON f.paper_type_id = p.paper_type_id 
                                ORDER BY p.paper_name ASC, f.dimension ASC");
    }

    /**
     * Updates an existing fixed price record
     */
    public function update(int $id, array $data): bool {
        $paperTypeId = (int)($data['paper_type_id'] ?? 0);
        $dimension   = $data['dimension'] ?? '';
        $widthInch   = (float)($data['width_inch'] ?? 0);
        $heightInch  = (float)($data['height_inch'] ?? 0);
        $fixedPrice  = (float)($data['fixed_price'] ?? 0);

        $sql = "UPDATE tbl_fixed_print_prices SET 
                paper_type_id = ?, 
                dimension = ?, 
                width_inch = ?, 
                height_inch = ?, 
                fixed_price = ? 
                WHERE fixed_price_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("isdddi", 
            $paperTypeId, $dimension, $widthInch, $heightInch, $fixedPrice, $id
        );

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Deletes a fixed price record
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tbl_fixed_print_prices WHERE fixed_price_id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}