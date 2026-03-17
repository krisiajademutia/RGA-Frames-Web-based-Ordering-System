<?php
// classes/Printing/PrintingService.php
require_once __DIR__ . '/Repository/PrintingRepository.php';

class PrintingService {
    private PrintingRepository $repo;

    public function __construct($conn) {
        $this->repo = new PrintingRepository($conn);
    }

    private function validateDimensions(int $paperTypeId, float $w, float $h): bool {
        $paper = $this->repo->getPaperTypeById($paperTypeId);
        if ($paper) {
            $max_w = (float)$paper['max_width_inch'];
            $max_h = (float)$paper['max_height_inch'];
            if ($max_w > 0 && ($w > $max_w || $h > $max_h)) {
                return false;
            }
        }
        return true;
    }

    // Notice we now accept the exact $target_dir from the process file
    private function handleImageUpload($file, string $target_dir): ?string {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;

        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $filename = time() . '_' . basename($file["name"]);
        if (move_uploaded_file($file["tmp_name"], $target_dir . $filename)) {
            return $filename;
        }
        return null;
    }

    public function addToCart(int $customerId, array $data, $file, string $target_dir): array {
        $typeId = (int)($data['type'] ?? 0);
        $w = (float)($data['width'] ?? 0);
        $h = (float)($data['height'] ?? 0);
        $qty = (int)($data['qty'] ?? 1);
        $subTotal = (float)($data['total_price'] ?? 0);

        if (!$this->validateDimensions($typeId, $w, $h)) {
            return ['success' => false, 'message' => ''];
        }

        $filename = $this->handleImageUpload($file, $target_dir);
        if (!$filename) return ['success' => false, 'message' => 'File upload failed.'];

        $cartId = $this->repo->getOrCreateCart($customerId);
        $success = $this->repo->insertPrintingItem(
            $cartId, $typeId, $filename, $w, $h, $qty, $subTotal
        );

        return $success 
            ? ['success' => true] 
            : ['success' => false, 'message' => 'Database error.'];
    }

    public function processBuyNow(array $data, $file, string $target_dir): array {
        $typeId = (int)($data['type'] ?? 0);
        $w = (float)($data['width'] ?? 0);
        $h = (float)($data['height'] ?? 0);
        $qty = (int)($data['qty'] ?? 1);
        $totalPrice = (float)($data['total_price'] ?? 0);
        $paperName = $data['paper_name'] ?? 'Standard';
        $size = $data['size'] ?? null;

        if (!$this->validateDimensions($typeId, $w, $h)) {
            return ['success' => false, 'message' => ''];
        }

        $filename = $this->handleImageUpload($file, $target_dir);
        if (!$filename) return ['success' => false, 'message' => 'Image upload failed.'];

        // Keep this exact path string for the Buy Now Checkout screen!
        $full_image_path = "uploads/customer_print/" . $filename;

        $_SESSION['buy_now_item'] = [
            'item_type'     => 'PRINTING',      
            'paper_type_id' => $typeId,           
            'paper_name'    => $paperName,     
            'size'          => $size,
            'width'         => $w,
            'height'        => $h,
            'quantity'      => $qty,
            'total_price'   => $totalPrice,
            'image_path'    => $full_image_path 
        ];

        return ['success' => true];
    }
}