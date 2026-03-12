<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class FrameDesignRepository implements OptionRepositoryInterface {
    private $db;
    private $uploadDir;

    public function __construct($db, $uploadDir = '../uploads/') {
        $this->db = $db;
        $this->uploadDir = $uploadDir;
    }

    // NEW: Added to allow fetching design details for editing
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tbl_frame_designs WHERE frame_design_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    public function create(array $data, array $files): bool {
        // 1. Insert into tbl_frame_designs
        $isActive = (int)($data['is_active'] ?? 1);
        $stmt = $this->db->prepare("INSERT INTO tbl_frame_designs (design_name, price, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $data['design_name'], $data['price'], $isActive);

        if (!$stmt->execute()) {
            return false;
        }

        $designId = $this->db->insert_id;

        // 2. Handle multiple image uploads (design_images[])
        if (!empty($files['design_images']['name'][0])) {
            $imageFiles = $files['design_images'];
            $count      = count($imageFiles['name']);

            for ($i = 0; $i < $count; $i++) {
                if ($imageFiles['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $ext        = pathinfo($imageFiles['name'][$i], PATHINFO_EXTENSION);
                $imageName  = 'design_' . time() . '_' . uniqid() . '.' . $ext;
                $targetPath = $this->uploadDir . $imageName;

                if (!move_uploaded_file($imageFiles['tmp_name'][$i], $targetPath)) {
                    continue;
                }

                // First image is primary
                $isPrimary = ($i === 0) ? 1 : 0;
                $stmt2 = $this->db->prepare("INSERT INTO tbl_frame_design_images (frame_design_id, image_name, is_primary) VALUES (?, ?, ?)");
                $stmt2->bind_param("isi", $designId, $imageName, $isPrimary);
                $stmt2->execute();
            }
        }

        return true;
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_frame_designs ORDER BY design_name ASC");
    }

    public function update(int $id, array $data, array $files = []): bool {
        // Debug/Fix: Match keys used in your form and DB
        $designName = $data['design_name'] ?? ($data['name'] ?? '');
        $price = $data['price'] ?? 0;
        $isActive = (int)($data['is_active'] ?? 1);

        $stmt = $this->db->prepare("UPDATE tbl_frame_designs SET design_name = ?, price = ?, is_active = ? WHERE frame_design_id = ?");
        $stmt->bind_param("sdii", $designName, $price, $isActive, $id);

        if (!$stmt->execute()) {
            return false;
        }

        // Append any newly uploaded images (no primary override — existing primary stays)
        if (!empty($files['design_images']['name'][0])) {
            $imageFiles = $files['design_images'];
            $count      = count($imageFiles['name']);

            for ($i = 0; $i < $count; $i++) {
                if ($imageFiles['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $ext        = pathinfo($imageFiles['name'][$i], PATHINFO_EXTENSION);
                $imageName  = 'design_' . time() . '_' . uniqid() . '.' . $ext;
                $targetPath = $this->uploadDir . $imageName;

                if (!move_uploaded_file($imageFiles['tmp_name'][$i], $targetPath)) {
                    continue;
                }

                $isPrimary = 0; // appended images are never primary
                $stmt2 = $this->db->prepare("INSERT INTO tbl_frame_design_images (frame_design_id, image_name, is_primary) VALUES (?, ?, ?)");
                $stmt2->bind_param("isi", $id, $imageName, $isPrimary);
                $stmt2->execute();
            }
        }

        return true;
    }

    public function delete(int $id): bool {
        // Delete images first in case there is no CASCADE on the FK
        $this->db->query("DELETE FROM tbl_frame_design_images WHERE frame_design_id = $id");

        $stmt = $this->db->prepare("DELETE FROM tbl_frame_designs WHERE frame_design_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}