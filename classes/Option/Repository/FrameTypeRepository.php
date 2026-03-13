<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class FrameTypeRepository implements OptionRepositoryInterface {
    private $db;
    private $uploadDir;

    public function __construct($db, $uploadDir = '../uploads/') {
        $this->db = $db;
        $this->uploadDir = $uploadDir;
    }

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
            if (!move_uploaded_file($files['type_image']['tmp_name'], $this->uploadDir . $imageName)) return false;
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
        $typeName = $data['type_name'] ?? ($data['name'] ?? '');
        $typePrice = $data['type_price'] ?? ($data['price'] ?? 0);
        $isActive = (int)($data['is_active'] ?? 1);
        $existingImage = $data['existing_image'] ?? null; // Hidden field from form
        
        $current = $this->getById($id);
        $finalImage = $current['image_name'];

        // Case 1: New File Uploaded
        if (isset($files['type_image']) && $files['type_image']['error'] === UPLOAD_ERR_OK) {
            if ($finalImage && file_exists($this->uploadDir . $finalImage)) unlink($this->uploadDir . $finalImage);
            
            $ext = pathinfo($files['type_image']['name'], PATHINFO_EXTENSION);
            $finalImage = 'type_' . time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($files['type_image']['tmp_name'], $this->uploadDir . $finalImage);
        } 
        // Case 2: User clicked "Remove" (existing_image will be empty)
        elseif (empty($existingImage) && $finalImage) {
            if (file_exists($this->uploadDir . $finalImage)) unlink($this->uploadDir . $finalImage);
            $finalImage = null;
        }

        $stmt = $this->db->prepare("UPDATE tbl_frame_types SET type_name = ?, type_price = ?, image_name = ?, is_active = ? WHERE frame_type_id = ?");
        $stmt->bind_param("sdsii", $typeName, $typePrice, $finalImage, $isActive, $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $current = $this->getById($id);
        if ($current && $current['image_name'] && file_exists($this->uploadDir . $current['image_name'])) {
            unlink($this->uploadDir . $current['image_name']);
        }
        $stmt = $this->db->prepare("DELETE FROM tbl_frame_types WHERE frame_type_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}