<?php
namespace Classes\Frames\Repository;

class ReadyMadeFrameRepository implements FrameRepositoryInterface {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function getAll() {
        $sql = "SELECT p.*, t.type_name, d.design_name, c.color_name, IFNULL(s.quantity, 0) as stock,
                (SELECT image_name FROM tbl_ready_made_product_images 
                 WHERE r_product_id = p.r_product_id 
                 ORDER BY is_primary DESC, image_id ASC LIMIT 1) as image_name
                FROM tbl_ready_made_product p
                LEFT JOIN tbl_frame_types t ON p.frame_type_id = t.frame_type_id
                LEFT JOIN tbl_frame_designs d ON p.frame_design_id = d.frame_design_id
                LEFT JOIN tbl_frame_colors c ON p.frame_color_id = c.frame_color_id
                LEFT JOIN tbl_ready_made_product_stocks s ON p.r_product_id = s.r_product_id
                ORDER BY p.r_product_id DESC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById(int $id) {
        $id = (int)$id;
        $sql = "SELECT p.*, s.quantity FROM tbl_ready_made_product p 
                LEFT JOIN tbl_ready_made_product_stocks s ON p.r_product_id = s.r_product_id 
                WHERE p.r_product_id = $id";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }

    public function create(array $data) {
        $sql = "INSERT INTO tbl_ready_made_product 
                (product_name, frame_type_id, frame_design_id, frame_color_id, width, height, product_price) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("siiiddd", 
            $data['product_name'], $data['frame_type_id'], $data['frame_design_id'], 
            $data['frame_color_id'], $data['width'], $data['height'], 
            $data['product_price']
        );
        
        if ($stmt->execute()) {
            $new_id = $this->db->insert_id;
            $sql_stock = "INSERT INTO tbl_ready_made_product_stocks (r_product_id, quantity) VALUES (?, ?)";
            $stmt_stock = $this->db->prepare($sql_stock);
            $stmt_stock->bind_param("ii", $new_id, $data['stock_quantity']);
            $stmt_stock->execute();
            return $new_id;
        }
        return false;
    }

    public function addImage(int $productId, string $fileName, int $isPrimary = 0) {
        $sql = "INSERT INTO tbl_ready_made_product_images (r_product_id, image_name, is_primary) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("isi", $productId, $fileName, $isPrimary);
        return $stmt->execute();
    }

    public function update(int $id, array $data) {
        $sql = "UPDATE tbl_ready_made_product SET 
                product_name=?, frame_type_id=?, frame_design_id=?, frame_color_id=?, 
                width=?, height=?, product_price=? 
                WHERE r_product_id=?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("siiidddi", 
            $data['product_name'], $data['frame_type_id'], $data['frame_design_id'], 
            $data['frame_color_id'], $data['width'], $data['height'], 
            $data['product_price'], $id
        );
        
        $stmt->execute();

        $sql_stock = "UPDATE tbl_ready_made_product_stocks SET quantity=? WHERE r_product_id=?";
        $stmt_stock = $this->db->prepare($sql_stock);
        $stmt_stock->bind_param("ii", $data['stock_quantity'], $id);
        return $stmt_stock->execute();
    }

    public function delete(int $id) {
    // 1. Delete image records from the database first
    $stmt_img = $this->db->prepare("DELETE FROM tbl_ready_made_product_images WHERE r_product_id = ?");
    $stmt_img->bind_param("i", $id);
    $stmt_img->execute();

    // 2. Delete stock records
    $stmt1 = $this->db->prepare("DELETE FROM tbl_ready_made_product_stocks WHERE r_product_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();

    // 3. Delete the product itself
    $stmt2 = $this->db->prepare("DELETE FROM tbl_ready_made_product WHERE r_product_id = ?");
    $stmt2->bind_param("i", $id);
    return $stmt2->execute();
}
}