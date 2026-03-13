<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class MatboardColorRepository implements OptionRepositoryInterface {
    private $db;
    private $uploadDir;

    public function __construct($db, $uploadDir = '../uploads/') {
        $this->db = $db;
        $this->uploadDir = $uploadDir;
    }

    // NEW: Fetches specific matboard details for editing
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tbl_matboard_colors WHERE matboard_color_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    public function create(array $data, array $files): bool {
        $fileName = null;

        if (isset($files['image_name']) && $files['image_name']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($files['image_name']['name'], PATHINFO_EXTENSION);
            $fileName = 'matboard_' . time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $this->uploadDir . $fileName;

            if (!move_uploaded_file($files['image_name']['tmp_name'], $targetPath)) {
                return false;
            }
        }

        $isActive = (int)($data['is_active'] ?? 1);
        $basePrice = (float)($data['base_price'] ?? 0.00);
        
        $stmt = $this->db->prepare("INSERT INTO tbl_matboard_colors (matboard_color_name, base_price, image_name, is_active) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdsi", $data['matboard_color_name'], $basePrice, $fileName, $isActive);
        return $stmt->execute();
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_matboard_colors ORDER BY matboard_color_name ASC");
    }

    public function update(int $id, array $data, array $files = []): bool {
        $colorName = $data['matboard_color_name'] ?? ($data['name'] ?? '');
        $isActive = (int)($data['is_active'] ?? 1);
        $basePrice = (float)($data['base_price'] ?? 0.00);
        $existingImage = $data['existing_image'] ?? null; // Tied to the hidden field in your form

        $current = $this->getById($id);
        $finalImage = $current['image_name'] ?? null;

        // Case 1: New File Uploaded
        if (isset($files['image_name']) && $files['image_name']['error'] === UPLOAD_ERR_OK) {
            // Delete old file if it exists
            if ($finalImage && file_exists($this->uploadDir . $finalImage)) {
                unlink($this->uploadDir . $finalImage);
            }

            $ext = pathinfo($files['image_name']['name'], PATHINFO_EXTENSION);
            $finalImage = 'matboard_' . time() . '_' . uniqid() . '.' . $ext;
            
            if (!move_uploaded_file($files['image_name']['tmp_name'], $this->uploadDir . $finalImage)) {
                return false;
            }
        } 
        // Case 2: User cleared/removed the image in the UI
        elseif (empty($existingImage) && $finalImage) {
            if (file_exists($this->uploadDir . $finalImage)) {
                unlink($this->uploadDir . $finalImage);
            }
            $finalImage = null;
        }

        $stmt = $this->db->prepare("UPDATE tbl_matboard_colors SET matboard_color_name = ?, base_price = ?, image_name = ?, is_active = ? WHERE matboard_color_id = ?");
        $stmt->bind_param("sdsii", $colorName, $basePrice, $finalImage, $isActive, $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool {
        // Physical file cleanup
        $current = $this->getById($id);
        if ($current && !empty($current['image_name'])) {
            $file = $this->uploadDir . $current['image_name'];
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM tbl_matboard_colors WHERE matboard_color_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}