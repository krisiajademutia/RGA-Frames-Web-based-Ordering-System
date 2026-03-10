<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class FrameColorRepository implements OptionRepositoryInterface {
    private $db;
    private $uploadDir;

    public function __construct($db, $uploadDir = null) {
        $this->db = $db;
        $this->uploadDir = $uploadDir;
    }

    public function create(array $data, array $files): bool {
        if (!isset($files['color_image']) || $files['color_image']['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $ext = pathinfo($files['color_image']['name'], PATHINFO_EXTENSION);
        $fileName = 'color_' . time() . '_' . uniqid() . '.' . $ext;
        $targetPath = ($this->uploadDir ?? "../../uploads/") . $fileName;

        if (move_uploaded_file($files['color_image']['tmp_name'], $targetPath)) {
            $isActive = (int)($data['is_active'] ?? 1);
            $stmt = $this->db->prepare("INSERT INTO tbl_frame_colors (color_name, color_image, is_active) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $data['color_name'], $fileName, $isActive);
            return $stmt->execute();
        }

        return false;
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_frame_colors ORDER BY color_name ASC");
    }

   public function update(int $id, array $data, array $files = []): bool {

    if (isset($files['color_image']) && $files['color_image']['error'] === UPLOAD_ERR_OK) {

        $ext = pathinfo($files['color_image']['name'], PATHINFO_EXTENSION);
        $fileName = 'color_' . time() . '_' . uniqid() . '.' . $ext;
        $targetPath = ($this->uploadDir ?? "../../uploads/") . $fileName;

        if (move_uploaded_file($files['color_image']['tmp_name'], $targetPath)) {

            $stmt = $this->db->prepare("
                UPDATE tbl_frame_colors 
                SET color_name = ?, color_image = ?, is_active = ?
                WHERE frame_color_id = ?
            ");

            $stmt->bind_param("ssii", $data['name'], $fileName, $data['is_active'], $id);
            return $stmt->execute();
        }

        return false;
    }

    $stmt = $this->db->prepare("
        UPDATE tbl_frame_colors 
        SET color_name = ?, is_active = ?
        WHERE frame_color_id = ?
    ");

    $stmt->bind_param("sii", $data['name'], $data['is_active'], $id);
    return $stmt->execute();
}

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tbl_frame_colors WHERE frame_color_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}