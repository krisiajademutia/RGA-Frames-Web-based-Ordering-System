<?php
require_once __DIR__ . '/OptionRepositoryInterface.php';

class MountTypeRepository implements OptionRepositoryInterface {
    private $db;
    public function __construct($db) { $this->db = $db; }

    // NEW: Added to allow fetching specific mount details for editing
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tbl_mount_type WHERE mount_type_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

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
        // Debug/Fix: Match keys used in your opt-form-grid (generic_name/generic_price)
        $mountName = $data['generic_name'] ?? ($data['name'] ?? '');
        $mountPrice = $data['generic_price'] ?? ($data['price'] ?? 0);
        $isActive = (int)($data['is_active'] ?? 1);

        $stmt = $this->db->prepare("UPDATE tbl_mount_type SET mount_name = ?, additional_fee = ?, is_active = ? WHERE mount_type_id = ?");
        $stmt->bind_param("sdii", $mountName, $mountPrice, $isActive, $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tbl_mount_type WHERE mount_type_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}