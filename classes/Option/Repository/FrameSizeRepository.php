<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class FrameSizeRepository implements OptionRepositoryInterface {
    private $db;
    public function __construct($db) { $this->db = $db; }

    // NEW: Added to fetch specific size details for the edit form
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tbl_frame_sizes WHERE frame_size_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    public function create(array $data, array $files): bool {
        $dim = $data['width'] . "x" . $data['height'];
        $isActive = (int)($data['is_active'] ?? 1);
        $stmt = $this->db->prepare("INSERT INTO tbl_frame_sizes (dimension, width_inch, height_inch, total_inch, price, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sddddi", $dim, $data['width'], $data['height'], $data['total_inches'], $data['base_price'], $isActive);
        return $stmt->execute();
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_frame_sizes ORDER BY total_inch ASC");
    }

    public function update(int $id, array $data, array $files = []): bool {
        // Debug/Fix: Match the keys coming from your opt-form-grid
        $price = $data['base_price'] ?? ($data['price'] ?? 0);
        $totalInches = $data['total_inches'] ?? 0;
        $isActive = (int)($data['is_active'] ?? 1);

        $stmt = $this->db->prepare("UPDATE tbl_frame_sizes SET total_inch = ?, price = ?, is_active = ? WHERE frame_size_id = ?");
        $stmt->bind_param("ddii", $totalInches, $price, $isActive, $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tbl_frame_sizes WHERE frame_size_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}