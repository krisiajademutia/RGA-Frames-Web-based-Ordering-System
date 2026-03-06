<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class MountTypeRepository implements OptionRepositoryInterface {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function create(array $data, array $files): bool {
        $stmt = $this->db->prepare("INSERT INTO tbl_mount_type (mount_name, additional_fee, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $data['generic_name'], $data['generic_price'], $data['is_active']);
        return $stmt->execute();
    }
    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_mount_type ORDER BY mount_name ASC");
    }
}