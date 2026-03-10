<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class MatboardColorRepository implements OptionRepositoryInterface {
    private $db;
    private $uploadDir;

    public function __construct($db, $uploadDir = null) {
        $this->db = $db;
        $this->uploadDir = $uploadDir;
    }

    public function create(array $data, array $files): bool {
        if (!isset($files['matboard_image']) || $files['matboard_image']['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $ext = pathinfo($files['matboard_image']['name'], PATHINFO_EXTENSION);
        $fileName = 'matboard_' . time() . '_' . uniqid() . '.' . $ext;
        $targetPath = ($this->uploadDir ?? __DIR__ . "/../../uploads/") . $fileName;

        if (!move_uploaded_file($files['matboard_image']['tmp_name'], $targetPath)) {
            return false;
        }

        $isActive = (int)($data['is_active'] ?? 1);
        $stmt = $this->db->prepare("INSERT INTO tbl_matboard_colors (matboard_color_name, image_name, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $data['matboard_color_name'], $fileName, $isActive);
        return $stmt->execute();
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_matboard_colors ORDER BY matboard_color_name ASC");
    }

    public function update(int $id, array $data, array $files = []): bool {
        $stmt = $this->db->prepare("UPDATE tbl_matboard_colors SET matboard_color_name = ?, is_active = ? WHERE matboard_color_id = ?");
        $stmt->bind_param("sii", $data['name'], $data['is_active'], $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tbl_matboard_colors WHERE matboard_color_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}