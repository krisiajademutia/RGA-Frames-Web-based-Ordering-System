<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class FrameTypeRepository implements OptionRepositoryInterface {
    private $db;
    private $uploadDir;

    public function __construct($db, $uploadDir = '../uploads/') {
        $this->db = $db;
        $this->uploadDir = $uploadDir;
    }

    // NEW: Added to allow fetching specific type details for editing
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tbl_frame_types WHERE frame_type_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    public function create(array $data, array $files): bool {
        $imageName = null;

        if (isset($files['type_image']) && $files['type_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($files['type_image']['name'], PATHINFO_EXTENSION);
            $imageName = 'type_' . time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $this->uploadDir . $imageName;

            if (!move_uploaded_file($files['type_image']['tmp_name'], $targetPath)) {
                return false;
            }
        }

        $isActive = (int)($data['is_active'] ?? 1);
        $stmt = $this->db->prepare("INSERT INTO tbl_frame_types (type_name, type_price, image_name, is_active) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdsi", $data['type_name'], $data['type_price'], $imageName, $isActive);
        return $stmt->execute();
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_frame_types ORDER BY type_name ASC");
    }

    public function update(int $id, array $data, array $files = []): bool {
        // Debug/Fix: Match keys used in your opt-form-grid
        $typeName = $data['type_name'] ?? ($data['name'] ?? '');
        $typePrice = $data['type_price'] ?? ($data['price'] ?? 0);
        $isActive = (int)($data['is_active'] ?? 1);

        $stmt = $this->db->prepare("UPDATE tbl_frame_types SET type_name = ?, type_price = ?, is_active = ? WHERE frame_type_id = ?");
        $stmt->bind_param("sdii", $typeName, $typePrice, $isActive, $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tbl_frame_types WHERE frame_type_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}