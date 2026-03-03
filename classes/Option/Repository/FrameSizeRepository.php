<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class FrameSizeRepository implements OptionRepositoryInterface {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function create(array $data, array $files): bool {
        $dim = $data['width'] . "x" . $data['height'];
        $stmt = $this->db->prepare("INSERT INTO tbl_frame_sizes (dimension, width_inch, height_inch, total_inch, price, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sddddi", $dim, $data['width'], $data['height'], $data['total_inches'], $data['base_price'], $data['is_active']);
        return $stmt->execute();
    }
    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_frame_sizes ORDER BY total_inch ASC");
    }
}