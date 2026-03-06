<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class PaperTypeRepository implements OptionRepositoryInterface {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function create(array $data, array $files): bool {
        $dim = $data['width'] . "x" . $data['height'];
        $stmt = $this->db->prepare("INSERT INTO tbl_paper_type (paper_name, pricing_logic, dimension, width_inch, height_inch, total_inch, price, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssddddi", $data['generic_name'], $data['pricing_logic'], $dim, $data['width'], $data['height'], $data['total_inches'], $data['generic_price'], $data['is_active']);
        return $stmt->execute();
    }
    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_paper_type ORDER BY paper_name ASC");
    }
}