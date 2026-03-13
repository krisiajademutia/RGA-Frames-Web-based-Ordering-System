<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class FrameColorRepository implements OptionRepositoryInterface {
    private $db;
    private $uploadDir;

    public function __construct($db, $uploadDir = '../uploads/') {
        $this->db = $db;
        $this->uploadDir = $uploadDir;
    }

    // NEW: Implemented to allow fetching details when the pencil icon is clicked
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tbl_frame_colors WHERE frame_color_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    public function create(array $data, array $files): bool {
        $fileName = null;

        if (isset($files['color_image']) && $files['color_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($files['color_image']['name'], PATHINFO_EXTENSION);
            $fileName = 'color_' . time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $this->uploadDir . $fileName;

            if (!move_uploaded_file($files['color_image']['tmp_name'], $targetPath)) {
                return false;
            }
        }

        $isActive = (int)($data['is_active'] ?? 1);
        $stmt = $this->db->prepare("INSERT INTO tbl_frame_colors (color_name, color_image, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $data['color_name'], $fileName, $isActive);
        return $stmt->execute();
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_frame_colors ORDER BY color_name ASC");
    }

    public function update(int $id, array $data, array $files = []): bool {
        $colorName = $data['color_name'] ?? ($data['name'] ?? '');
        $isActive = (int)($data['is_active'] ?? 1);
        $existingImage = $data['existing_image'] ?? null; // From the hidden input in your form

        $current = $this->getById($id);
        $finalImage = $current['color_image'] ?? null;

        // Case 1: New File Uploaded
        if (isset($files['color_image']) && $files['color_image']['error'] === UPLOAD_ERR_OK) {
            // Delete old file if it exists
            if ($finalImage && file_exists($this->uploadDir . $finalImage)) {
                unlink($this->uploadDir . $finalImage);
            }

            $ext = pathinfo($files['color_image']['name'], PATHINFO_EXTENSION);
            $finalImage = 'color_' . time() . '_' . uniqid() . '.' . $ext;
            
            if (!move_uploaded_file($files['color_image']['tmp_name'], $this->uploadDir . $finalImage)) {
                return false;
            }
        } 
        // Case 2: User clicked "Remove" (existing_image hidden field will be empty)
        elseif (empty($existingImage) && $finalImage) {
            if (file_exists($this->uploadDir . $finalImage)) {
                unlink($this->uploadDir . $finalImage);
            }
            $finalImage = null;
        }

        $stmt = $this->db->prepare("UPDATE tbl_frame_colors SET color_name = ?, color_image = ?, is_active = ? WHERE frame_color_id = ?");
        $stmt->bind_param("ssii", $colorName, $finalImage, $isActive, $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool {
        // Cleanup physical file before record deletion
        $current = $this->getById($id);
        if ($current && !empty($current['color_image'])) {
            $file = $this->uploadDir . $current['color_image'];
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM tbl_frame_colors WHERE frame_color_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}