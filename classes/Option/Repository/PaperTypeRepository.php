<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class PaperTypeRepository implements OptionRepositoryInterface {
    private $db;

    public function __construct($db) { 
        $this->db = $db; 
    }

    /**
     * Fetches a single record by ID for the edit form
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT 
            paper_type_id,
            paper_name,
            paper_name AS name,
            multiplier,
            min_width_inch,
            min_height_inch,
            max_width_inch,
            max_height_inch,
            is_active
            FROM tbl_paper_type 
            WHERE paper_type_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    /**
     * Handles creating a new Paper Type
     */
    public function create(array $data, array $files): bool {
        $paperName  = $data['paper_name'] ?? $data['edit_paper_name'] ?? '';
        $multiplier = (float)($data['multiplier'] ?? 0);
        $minWidth   = (float)($data['min_width_inch'] ?? 0);
        $minHeight  = (float)($data['min_height_inch'] ?? 0);
        $maxWidth   = (float)($data['max_width_inch'] ?? 0);
        $maxHeight  = (float)($data['max_height_inch'] ?? 0);
        $isActive   = (int)($data['is_active'] ?? 1);

        $sql = "INSERT INTO tbl_paper_type 
                (paper_name, multiplier, min_width_inch, min_height_inch, max_width_inch, max_height_inch, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sdddddi", 
            $paperName, $multiplier, $minWidth, $minHeight, $maxWidth, $maxHeight, $isActive
        );

        return $stmt->execute();
    }

    /**
     * Fetches all paper types with corrected column names
     */
    public function getAll() {
        return $this->db->query("SELECT 
            paper_type_id,
            paper_name,
            paper_name AS name,
            multiplier,
            min_width_inch,
            min_height_inch,
            max_width_inch,
            max_height_inch,
            is_active
            FROM tbl_paper_type 
            ORDER BY paper_name ASC");
    }

    /**
     * Handles updating an existing Paper Type
     */
    public function update(int $id, array $data, array $files = []): bool {
        $paperName  = $data['edit_paper_name'] ?? $data['name'] ?? $data['paper_name'] ?? '';
        $multiplier = (float)($data['multiplier'] ?? 0);
        $minWidth   = (float)($data['min_width_inch'] ?? 0);
        $minHeight  = (float)($data['min_height_inch'] ?? 0);
        $maxWidth   = (float)($data['max_width_inch'] ?? 0);
        $maxHeight  = (float)($data['max_height_inch'] ?? 0);
        $isActive   = (int)($data['is_active'] ?? 1);

        $sql = "UPDATE tbl_paper_type SET 
                paper_name = ?, 
                multiplier = ?, 
                min_width_inch = ?, 
                min_height_inch = ?, 
                max_width_inch = ?, 
                max_height_inch = ?, 
                is_active = ? 
                WHERE paper_type_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sdddddii", 
            $paperName, $multiplier, $minWidth, $minHeight, $maxWidth, $maxHeight, $isActive, $id
        );

        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tbl_paper_type WHERE paper_type_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // --- FIXED PRINT PRICE METHODS ---

    /**
     * Fetches all fixed prices for a specific paper type (For the Modal List)
     */
    public function getAllFixedPrices(int $paperTypeId) {
        $stmt = $this->db->prepare("SELECT * FROM tbl_fixed_print_prices WHERE paper_type_id = ? ORDER BY width_inch ASC, height_inch ASC");
        $stmt->bind_param("i", $paperTypeId);
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Creates a new entry in tbl_fixed_print_prices
     */
    public function createFixedPrice($data) {
        // Validate required fields (add more validation as needed)
        if (!isset($data['paper_type_id']) || !isset($data['price'])) {
            return false;
        }
        
        $stmt = $this->db->prepare("INSERT INTO fixed_prices (paper_type_id, price) VALUES (?, ?)");
        $stmt->bind_param("id", $data['paper_type_id'], $data['price']);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Updates an existing fixed price entry
     */
    public function updateFixedPrice($id, $data) {
        // Validate required fields
        if (!isset($data['price'])) {
            return false;
        }
        
        $stmt = $this->db->prepare("UPDATE fixed_prices SET price = ? WHERE id = ?");
        $stmt->bind_param("di", $data['price'], $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Deletes a fixed price entry
     */
    public function deleteFixedPrice($id) {
        $stmt = $this->db->prepare("DELETE FROM fixed_prices WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}