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
        $stmt = $this->db->prepare("INSERT INTO tbl_frame_sizes (dimension, width_inch, height_inch, is_active) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sddi", $dim, $data['width'], $data['height'], $isActive);
        return $stmt->execute();
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_frame_sizes ORDER BY width_inch ASC");
    }

    public function update(int $id, array $data, array $files = []): bool {
        $isActive = (int)($data['is_active'] ?? 1);

        $stmt = $this->db->prepare("UPDATE tbl_frame_sizes SET is_active = ? WHERE frame_size_id = ?");
        $stmt->bind_param("ii", $isActive, $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tbl_frame_sizes WHERE frame_size_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}