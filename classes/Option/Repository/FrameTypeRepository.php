<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class FrameTypeRepository implements OptionRepositoryInterface {
    private $db;
    private $uploadDir;

    // We add $uploadDir to the constructor so the Repository knows where to save files
    public function __construct($db, $uploadDir = '../uploads/') { 
        $this->db = $db; 
        $this->uploadDir = $uploadDir;
    }

    public function create(array $data, array $files): bool {
        $imageName = null;

        // 1. Handle File Upload Logic
        if (isset($files['type_image']) && $files['type_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($files['type_image']['name'], PATHINFO_EXTENSION);
            // Create a unique name to prevent overwriting: e.g., type_17123456.jpg
            $imageName = 'type_' . time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $this->uploadDir . $imageName;

            if (!move_uploaded_file($files['type_image']['tmp_name'], $targetPath)) {
                return false; // File upload failed
            }
        }

        // 2. Prepare Query with all 4 columns (including image_name)
        $stmt = $this->db->prepare("INSERT INTO tbl_frame_types (type_name, type_price, image_name, is_active) VALUES (?, ?, ?, ?)");
        
        // "sdsi" -> string (name), double (price), string (image), int (active)
        $stmt->bind_param("sdsi", 
            $data['type_name'], 
            $data['type_price'], 
            $imageName, 
            $data['is_active']
        );

        return $stmt->execute();
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_frame_types ORDER BY type_name ASC");
    }
}