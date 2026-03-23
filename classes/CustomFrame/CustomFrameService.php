<?php
// classes/CustomFrame/CustomFrameService.php

require_once __DIR__ . '/Repository/CustomFrameRepository.php';

class CustomFrameService {
    private CustomFrameRepository $repo;
    private $conn;

    public function __construct($conn) {
        $this->repo = new CustomFrameRepository($conn);
        $this->conn = $conn;
    }

    /**
     * Used by custom shop screen
     */
    public function getFrameBuilderData(): array {
        return [
            'frame_types'        => $this->repo->getActiveFrameTypes(),
            'frame_designs'      => $this->repo->getActiveFrameDesigns(),
            'frame_colors'       => $this->repo->getActiveFrameColors(),
            'frame_sizes'        => $this->repo->getActiveFrameSizes(),
            'matboard_colors'    => $this->repo->getActiveMatboardColors(),
            'mount_types'        => $this->repo->getActiveMountTypes(),
            'paper_types'        => $this->repo->getActivePaperTypes(),
            'fixed_print_prices' => $this->repo->getActiveFixedPrintPrices(),
        ];
    }

   public function calculatePrice(array $data): array {
        $basePrice  = 0.0;
        $extraPrice = 0.0;
        $printPrice = 0.0;

        $w = (float)($data['custom_width'] ?? $data['width'] ?? 0);
        $h = (float)($data['custom_height'] ?? $data['height'] ?? 0);

        // 1. Check if using a fixed frame size
        if (!empty($data['frame_size_id']) && $data['frame_size_id'] !== 'OTHER') {
            $fs = $this->repo->getFrameSizeById((int)$data['frame_size_id']);
            if ($fs) {
                $w = (float)$fs['width_inch'];
                $h = (float)$fs['height_inch'];
            }
        }

        // 2. Base Frame Price
        if ($w > 0 && $h > 0) {
            if (!empty($data['frame_type_id'])) {
                $ft = $this->repo->getFrameTypeById((int)$data['frame_type_id']);
                $basePrice += $ft ? (float)$ft['type_price'] : 0;
            }
            if (!empty($data['frame_design_id'])) {
                $fd = $this->repo->getFrameDesignById((int)$data['frame_design_id']);
                $designPrice = $fd ? (float)$fd['price'] : 0;
                $basePrice += (($w + $h) / 6) * $designPrice;
            }
        }

        // 3. Matboard Price (🟢 THE FIX: Syncing with JS Business Rules 🟢)
        $primaryId   = (int)($data['primary_matboard_id']   ?? 0);
        $secondaryId = (int)($data['secondary_matboard_id'] ?? 0);

        // ONLY charge if BOTH are selected, and only charge the primary price once!
        if ($primaryId > 0 && $secondaryId > 0) {
            $mc1 = $this->repo->getMatboardById($primaryId);
            $extraPrice += $mc1 ? (float)$mc1['base_price'] : 0;
        }

        // 4. Mount Type Price
        if (!empty($data['mount_type_id'])) {
            $mt = $this->repo->getMountById((int)$data['mount_type_id']);
            $extraPrice += $mt ? (float)$mt['additional_fee'] : 0;
        }

        // 5. Print Price
        $rawServiceType = strtoupper(str_replace([' ', '_', '&'], '', $data['service_type'] ?? ''));
        if ($rawServiceType === 'FRAMEPRINT' && $w > 0 && $h > 0) {
            $paperTypeId = (int)($data['paper_type_id'] ?? 0);
            if ($paperTypeId > 0) {
                $fixedPriceItem = $this->repo->getFixedPrintPrice($paperTypeId, $w, $h);
                if ($fixedPriceItem) {
                    $printPrice = (float)$fixedPriceItem['fixed_price'];
                } else {
                    $pt = $this->repo->getPaperTypeById($paperTypeId);
                    if ($pt) {
                        $printPrice = ($w * $h) * (float)$pt['multiplier'];
                    }
                }
            }
            $extraPrice += $printPrice;
        }

        // 6. Calculate Totals
        $unitSubTotal = $basePrice + $extraPrice;
        $qty          = max(1, (int)($data['quantity'] ?? 1));
        $grandTotal   = $unitSubTotal * $qty;

        return [
            'base_price'  => round($basePrice, 2),
            'extra_price' => round($extraPrice, 2),
            'print_price' => round($printPrice, 2),
            'sub_total'   => round($unitSubTotal, 2),
            'grand_total' => round($grandTotal, 2),
            'width'       => $w,
            'height'      => $h,
        ];
    }

    // Add this inside CustomFrameService.php
    public function uploadImage($file, string $target_dir): ?string {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Security check
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
            throw new Exception('Invalid image format.');
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            throw new Exception('Image too large. Max 10MB.');
        }

        // Using our beautiful naming convention!
        $filename = 'CUSTOM_PRINT_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        
        if (move_uploaded_file($file["tmp_name"], $target_dir . $filename)) {
            return $filename;
        }
        
        throw new Exception('Failed to move uploaded file.');
    }
  
    public function addToCart(int $customerId, array $data): array {
        $this->conn->begin_transaction();

        try {
            $prices = $this->calculatePrice($data);

            // 🟢 NEW BUSINESS RULE: Canvas 12x18 Minimum Check 🟢
            $serviceType = (!empty($data['service_type']) && $data['service_type'] === 'FRAME&PRINT')
                ? 'FRAME&PRINT'
                : 'FRAME_ONLY';

            if ($serviceType === 'FRAME&PRINT' && !empty($data['paper_type_id'])) {
                // Fetch the actual paper name from the database using the ID
                $pt = $this->repo->getPaperTypeById((int)$data['paper_type_id']);
                
                // Check if the word 'canvas' is anywhere in the paper name
                if ($pt && stripos($pt['paper_name'], 'canvas') !== false) {
                    $shortest_side = min($prices['width'], $prices['height']);
                    $longest_side = max($prices['width'], $prices['height']);

                    if ($shortest_side < 12 || $longest_side < 18) {
                        $this->conn->rollback(); // Cancel the database save!
                        return [
                            'success' => false, 
                            'message' => 'Canvas prints require a minimum frame size of 12x18 inches.'
                        ];
                    }
                }
            }
            // 🟢 END OF NEW BUSINESS RULE 🟢

            $cProductId = $this->repo->insertCustomFrameProduct(
                !empty($data['frame_type_id'])   ? (int)$data['frame_type_id']   : null,
                !empty($data['frame_design_id']) ? (int)$data['frame_design_id'] : null,
                !empty($data['frame_color_id'])  ? (int)$data['frame_color_id']  : null,
                $prices['width'],
                $prices['height'],
                $prices['base_price']
            );

            $cartId = $this->repo->getOrCreateCart($customerId);

            $printingItemId = null;

            if ($serviceType === 'FRAME&PRINT') {
                $printingItemId = $this->repo->insertPrintingOrderItem(
                    $cartId,
                    null,
                    (int)($data['paper_type_id'] ?? 0),
                    $data['image_path'] ?? '',
                    $prices['width'],
                    $prices['height'],
                    max(1, (int)($data['quantity'] ?? 1)),
                    $prices['print_price']
                );
            }

            $this->repo->insertFrameOrderItem(
                'CUSTOM',
                null,
                $cProductId,
                'CART',
                $cartId,
                null,
                $serviceType,
                $printingItemId,
                (int)($data['primary_matboard_id'] ?? 0) ?: null,
                (int)($data['secondary_matboard_id'] ?? 0) ?: null,
                (int)($data['mount_type_id'] ?? 0) ?: null,
                max(1, (int)($data['quantity'] ?? 1)),
                $prices['base_price'],
                $prices['extra_price'],
                $prices['sub_total'] // IMPORTANT: per item subtotal only
            );

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Added to cart successfully!'
            ];

        } catch (Exception $e) {
            $this->conn->rollback();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}