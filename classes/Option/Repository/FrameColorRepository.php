<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class FrameColorRepository implements OptionRepositoryInterface {
    private $db;
    private $uploadDir;

    // We add $uploadDir to the constructor to match how you call it in posting_options.php
    public function __construct($db, $uploadDir = null) { 
        $this->db = $db; 
        $this->uploadDir = $uploadDir;
    }

    public function create(array $data, array $files): bool {
        // 1. Validate file existence
        if (!isset($files['color_image']) || $files['color_image']['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $fileName = basename($files['color_image']['name']);
        // Use the passed uploadDir if available, otherwise fallback to a default
        $targetPath = ($this->uploadDir ?? "../../uploads/") . $fileName;

        // 2. Move file
        if (move_uploaded_file($files['color_image']['tmp_name'], $targetPath)) {
            // 3. Database Insertion
            $stmt = $this->db->prepare("INSERT INTO tbl_frame_colors (color_name, color_image, is_active) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $data['color_name'], $fileName, $data['is_active']);
            
            return $stmt->execute();
        }
        
        return false;
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_frame_colors ORDER BY color_name ASC");
    }
}