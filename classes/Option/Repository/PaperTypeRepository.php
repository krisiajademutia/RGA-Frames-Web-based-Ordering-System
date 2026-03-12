<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class PaperTypeRepository implements OptionRepositoryInterface {
    private $db;

    public function __construct($db) { 
        $this->db = $db; 
    }

    /**
     * NEW: Fetches a single record by ID for the edit form
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT 
            paper_type_id,
            paper_name,
            paper_name AS name,
            pricing_logic,
            dimension,
            width_inch,
            width_inch AS width,
            height_inch,
            height_inch AS height,
            total_inch,
            total_inch AS total_inches,
            price,
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
        $paperName    = $data['paper_name'] ?? $data['edit_paper_name'] ?? '';
        $width        = (float)($data['width'] ?? $data['edit_width'] ?? 0);
        $height       = (float)($data['height'] ?? $data['edit_height'] ?? 0);
        $price        = (float)($data['price'] ?? $data['edit_price'] ?? 0);
        
        $pricingLogic = strtoupper($data['pricing_logic'] ?? 'FIXED'); 
        $dim          = ($width > 0 && $height > 0) ? $width . "x" . $height : null;
        
        $totalInches  = (float)($data['total_inches'] ?? ($width * $height)); 
        $isActive     = (int)($data['is_active'] ?? 1);

        $sql = "INSERT INTO tbl_paper_type 
                (paper_name, pricing_logic, dimension, width_inch, height_inch, total_inch, price, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssddddi", 
            $paperName, $pricingLogic, $dim, $width, $height, $totalInches, $price, $isActive
        );

        return $stmt->execute();
    }

    /**
     * Fetches all paper types with aliased names to match form inputs
     */
    public function getAll() {
        // We use aliases (e.g., width_inch AS width) so that when the 
        // admin_custom_frame_options.php loops through $row, $row['width'] exists.
        return $this->db->query("SELECT 
            paper_type_id,
            paper_name,
            paper_name AS name,
            pricing_logic,
            dimension,
            width_inch,
            width_inch AS width,
            height_inch,
            height_inch AS height,
            total_inch,
            total_inch AS total_inches,
            price,
            is_active
            FROM tbl_paper_type 
            ORDER BY paper_name ASC");
    }

    /**
     * Handles updating an existing Paper Type
     */
    public function update(int $id, array $data, array $files = []): bool {
        // Check for 'edit_paper_name' (from raw POST) or 'name' (from posting_options.php mapping)
        $paperName    = $data['edit_paper_name'] ?? $data['name'] ?? $data['paper_name'] ?? '';
        $width        = (float)($data['edit_width'] ?? $data['width_inch'] ?? $data['width'] ?? 0);
        $height       = (float)($data['edit_height'] ?? $data['height_inch'] ?? $data['height'] ?? 0);
        $price        = (float)($data['edit_price'] ?? $data['price'] ?? 0);
        
        $pricingLogic = strtoupper($data['pricing_logic'] ?? 'FIXED');
        $dim          = ($width > 0 && $height > 0) ? $width . "x" . $height : null;
        $totalInches  = (float)($data['total_inches'] ?? ($width * $height));
        $isActive     = (int)($data['is_active'] ?? 1);

        $sql = "UPDATE tbl_paper_type SET 
                paper_name = ?, 
                pricing_logic = ?, 
                dimension = ?, 
                width_inch = ?, 
                height_inch = ?, 
                total_inch = ?, 
                price = ?, 
                is_active = ? 
                WHERE paper_type_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssddddii", 
            $paperName, $pricingLogic, $dim, $width, $height, $totalInches, $price, $isActive, $id
        );

        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tbl_paper_type WHERE paper_type_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}