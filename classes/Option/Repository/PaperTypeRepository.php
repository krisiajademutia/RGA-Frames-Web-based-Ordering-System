<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class PaperTypeRepository implements OptionRepositoryInterface {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function create(array $data, array $files): bool {
    $width  = $data['width_inch'] ?? 0;
    $height = $data['height_inch'] ?? 0;
    $dim    = $width . "x" . $height;

    $paperName    = $data['generic_name'] ?? '';
    $pricingLogic = $data['pricing_logic'] ?? 'FIXED'; // Default to 'FIXED' if empty
    $totalInches  = $data['total_inches'] ?? 0;
    $price        = $data['generic_price'] ?? 0;
    $isActive     = $data['is_active'] ?? 1;

    $stmt = $this->db->prepare("INSERT INTO tbl_paper_type (paper_name, pricing_logic, dimension, width_inch, height_inch, total_inch, price, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssddddi", 
        $paperName, 
        $pricingLogic, 
        $dim, 
        $width, 
        $height, 
        $totalInches, 
        $price, 
        $isActive
    );

    return $stmt->execute();
}
    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_paper_type ORDER BY paper_name ASC");
    }
}