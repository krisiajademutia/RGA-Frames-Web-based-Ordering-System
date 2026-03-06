<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class FrameDesignRepository implements OptionRepositoryInterface {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function create(array $data, array $files): bool {
        $stmt = $this->db->prepare("INSERT INTO tbl_frame_designs (design_name, price, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $data['design_name'], $data['price'], $data['is_active']);
        return $stmt->execute();
    }
    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_frame_designs ORDER BY design_name ASC");
    }
}