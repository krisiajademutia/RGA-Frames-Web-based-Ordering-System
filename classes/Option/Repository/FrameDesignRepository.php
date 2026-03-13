<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class FrameDesignRepository implements OptionRepositoryInterface {
    private $db;
    private $uploadDir;

    public function __construct($db, $uploadDir = '../uploads/') {
        $this->db = $db;
        $this->uploadDir = $uploadDir;
    }

    public function getById(int $id): ?array {
        // Fetch design details
        $stmt = $this->db->prepare("SELECT * FROM tbl_frame_designs WHERE frame_design_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $design = $stmt->get_result()->fetch_assoc();

        if ($design) {
            // Fetch associated images
            $imgStmt = $this->db->prepare("SELECT * FROM tbl_frame_design_images WHERE frame_design_id = ? ORDER BY is_primary DESC");
            $imgStmt->bind_param("i", $id);
            $imgStmt->execute();
            $design['images'] = $imgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        return $design ?: null;
    }

    public function create(array $data, array $files): bool {
        $isActive = (int)($data['is_active'] ?? 1);
        $stmt = $this->db->prepare("INSERT INTO tbl_frame_designs (design_name, price, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $data['design_name'], $data['price'], $isActive);

        if (!$stmt->execute()) return false;

        $designId = $this->db->insert_id;

        if (!empty($files['design_images']['name'][0])) {
            $this->uploadImages($designId, $files['design_images'], true);
        }

        return true;
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_frame_designs ORDER BY design_name ASC");
    }

    public function update(int $id, array $data, array $files = []): bool {
        $designName = $data['design_name'] ?? ($data['name'] ?? '');
        $price = $data['price'] ?? 0;
        $isActive = (int)($data['is_active'] ?? 1);
        $existingImages = $data['existing_images'] ?? []; // Array of image names to KEEP

        // 1. Update design info
        $stmt = $this->db->prepare("UPDATE tbl_frame_designs SET design_name = ?, price = ?, is_active = ? WHERE frame_design_id = ?");
        $stmt->bind_param("sdii", $designName, $price, $isActive, $id);
        if (!$stmt->execute()) return false;

        // 2. Handle Removal of old images
        $currentImages = $this->getById($id)['images'] ?? [];
        foreach ($currentImages as $img) {
            if (!in_array($img['image_name'], $existingImages)) {
                // Delete file from server
                $filePath = $this->uploadDir . $img['image_name'];
                if (file_exists($filePath)) unlink($filePath);
                
                // Delete record from DB
                $this->db->query("DELETE FROM tbl_frame_design_images WHERE image_id = " . $img['image_id']);
            }
        }

        // 3. Upload new images
        if (!empty($files['design_images']['name'][0])) {
            // If no images were kept, the first new one becomes primary
            $hasPrimary = $this->db->query("SELECT 1 FROM tbl_frame_design_images WHERE frame_design_id = $id AND is_primary = 1")->num_rows > 0;
            $this->uploadImages($id, $files['design_images'], !$hasPrimary);
        }

        return true;
    }

    public function delete(int $id): bool {
        // Cleanup files
        $images = $this->db->query("SELECT image_name FROM tbl_frame_design_images WHERE frame_design_id = $id");
        while ($img = $images->fetch_assoc()) {
            $filePath = $this->uploadDir . $img['image_name'];
            if (file_exists($filePath)) unlink($filePath);
        }

        $this->db->query("DELETE FROM tbl_frame_design_images WHERE frame_design_id = $id");
        $stmt = $this->db->prepare("DELETE FROM tbl_frame_designs WHERE frame_design_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    private function uploadImages(int $designId, array $imageFiles, bool $setFirstAsPrimary) {
        $count = count($imageFiles['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($imageFiles['error'][$i] !== UPLOAD_ERR_OK) continue;

            $ext = pathinfo($imageFiles['name'][$i], PATHINFO_EXTENSION);
            $imageName = 'design_' . time() . '_' . uniqid() . '.' . $ext;
            
            if (move_uploaded_file($imageFiles['tmp_name'][$i], $this->uploadDir . $imageName)) {
                $isPrimary = ($setFirstAsPrimary && $i === 0) ? 1 : 0;
                $stmt = $this->db->prepare("INSERT INTO tbl_frame_design_images (frame_design_id, image_name, is_primary) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $designId, $imageName, $isPrimary);
                $stmt->execute();
            }
        }
    }
}