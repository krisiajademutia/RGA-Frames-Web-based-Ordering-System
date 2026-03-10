<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class MountTypeRepository implements OptionRepositoryInterface {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function create(array $data, array $files): bool {
        $isActive = (int)($data['is_active'] ?? 1);
        $stmt = $this->db->prepare("INSERT INTO tbl_mount_type (mount_name, additional_fee, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $data['generic_name'], $data['generic_price'], $isActive);
        return $stmt->execute();
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM tbl_mount_type ORDER BY mount_name ASC");
    }

    public function update(int $id, array $data, array $files = []): bool {
        $stmt = $this->db->prepare("UPDATE tbl_mount_type SET mount_name = ?, additional_fee = ?, is_active = ? WHERE mount_type_id = ?");
        $stmt->bind_param("sdii", $data['name'], $data['price'], $data['is_active'], $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tbl_mount_type WHERE mount_type_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}