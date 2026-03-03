<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class MatboardColorRepository implements OptionRepositoryInterface {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function create(array $data, array $files): bool {
        $img = $files['matboard_image']['name'];
        move_uploaded_file($files['matboard_image']['tmp_name'], __DIR__ . "/../../uploads/" . basename($img));
        $stmt = $this->db->prepare("INSERT INTO tbl_matboard_colors (matboard_color_name, image_name, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $data['matboard_color_name'], $img, $data['is_active']);
        return $stmt->execute();
    }
    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_matboard_colors ORDER BY matboard_color_name ASC");
    }
}