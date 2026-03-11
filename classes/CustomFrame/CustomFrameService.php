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

    // ── Builder page data ────────────────────────────────
    public function getFrameBuilderData(): array {
        return [
            'frame_types'     => $this->repo->getActiveFrameTypes(),
            'frame_designs'   => $this->repo->getActiveFrameDesigns(),
            'frame_colors'    => $this->repo->getActiveFrameColors(),
            'frame_sizes'     => $this->repo->getActiveFrameSizes(),
            'matboard_colors' => $this->repo->getActiveMatboardColors(),
            'mount_types'     => $this->repo->getActiveMountTypes(),
            'paper_types'     => $this->repo->getActivePaperTypes(),
        ];
    }

    // ── Tiered size price (width+height → find matching tier) ──
    private function calcTieredSizePrice(float $w, float $h): float {
        $totalInch = $w + $h;

        // Fetch all active sizes ordered by total_inch ASC
        $sizes = $this->repo->getActiveFrameSizes();

        // If exact match exists, return its price
        foreach ($sizes as $s) {
            if ((float)$s['total_inch'] == $totalInch) {
                return (float)$s['price'];
            }
        }

        // Find the next tier UP (first size whose total_inch >= our total)
        foreach ($sizes as $s) {
            if ((float)$s['total_inch'] >= $totalInch) {
                return (float)$s['price'];
            }
        }

        // If larger than all tiers, use the largest tier price
        if (!empty($sizes)) {
            return (float)end($sizes)['price'];
        }

        return 0.0;
    }

    // ── Server-side price calculation ────────────────────
    public function calculatePrice(array $data): array {
        $basePrice  = 0.0;
        $extraPrice = 0.0;

        // Frame type price
        if (!empty($data['frame_type_id'])) {
            $ft = $this->repo->getFrameTypeById((int)$data['frame_type_id']);
            $basePrice += $ft ? (float)$ft['type_price'] : 0;
        }

        // Frame design price
        if (!empty($data['frame_design_id'])) {
            $fd = $this->repo->getFrameDesignById((int)$data['frame_design_id']);
            $basePrice += $fd ? (float)$fd['price'] : 0;
        }

        // Frame size price — tiered logic
        $customWidth  = (float)($data['custom_width']  ?? 0);
        $customHeight = (float)($data['custom_height'] ?? 0);

        if (!empty($data['frame_size_id']) && $data['frame_size_id'] !== 'OTHER') {
            // Preset size — get its dimensions then run through tier
            $fs = $this->repo->getFrameSizeById((int)$data['frame_size_id']);
            if ($fs) {
                $customWidth  = (float)$fs['width_inch'];
                $customHeight = (float)$fs['height_inch'];
            }
        }

        // Both preset and custom sizes go through the same tier logic
        if ($customWidth > 0 && $customHeight > 0) {
            $basePrice += $this->calcTieredSizePrice($customWidth, $customHeight);
        }

        // Matboard — only charge when BOTH primary AND secondary are selected (not None/0)
        $primaryId   = (int)($data['primary_matboard_id']   ?? 0);
        $secondaryId = (int)($data['secondary_matboard_id'] ?? 0);

        if ($primaryId > 0 && $secondaryId > 0) {
            $mc1 = $this->repo->getMatboardById($primaryId);
            $mc2 = $this->repo->getMatboardById($secondaryId);
            $extraPrice += $mc1 ? (float)$mc1['base_price'] : 0;
            $extraPrice += $mc2 ? (float)$mc2['base_price'] : 0;
        }

        // Mount type
        if (!empty($data['mount_type_id'])) {
            $mt = $this->repo->getMountById((int)$data['mount_type_id']);
            $extraPrice += $mt ? (float)$mt['additional_fee'] : 0;
        }

        // Print price
        $printPrice = 0.0;
        if (!empty($data['service_type']) && $data['service_type'] === 'FRAME&PRINT') {
            if (!empty($data['paper_type_id'])) {
                $pt = $this->repo->getPaperTypeById((int)$data['paper_type_id']);
                if ($pt) {
                    if ($pt['pricing_logic'] === 'FIXED') {
                        $printPrice = (float)$pt['price'];
                    } else {
                        // CALCULATED: total_inch × price per inch
                        $totalInch  = ($customWidth + $customHeight) * 2;
                        $printPrice = $totalInch * (float)$pt['price'];
                    }
                }
            }
            $extraPrice += $printPrice;
        }

        $unitSubTotal = $basePrice + $extraPrice;
        $qty          = max(1, (int)($data['quantity'] ?? 1));
        $grandTotal   = $unitSubTotal * $qty;

        return [
            'base_price'  => round($basePrice, 2),
            'extra_price' => round($extraPrice, 2),
            'print_price' => round($printPrice, 2),
            'sub_total'   => round($unitSubTotal, 2),
            'grand_total' => round($grandTotal, 2),
            'width'       => $customWidth,
            'height'      => $customHeight,
        ];
    }

    // ── Add to Cart ──────────────────────────────────────
    public function addToCart(int $customerId, array $data): array {
        $this->conn->begin_transaction();
        try {
            $prices = $this->calculatePrice($data);

            // 1. Insert custom frame product
            $cProductId = $this->repo->insertCustomFrameProduct(
                !empty($data['frame_type_id'])   ? (int)$data['frame_type_id']   : null,
                !empty($data['frame_design_id']) ? (int)$data['frame_design_id'] : null,
                !empty($data['frame_color_id'])  ? (int)$data['frame_color_id']  : null,
                (!empty($data['frame_size_id']) && $data['frame_size_id'] !== 'OTHER') ? (int)$data['frame_size_id'] : null,
                $prices['width'],
                $prices['height'],
                $prices['base_price']
            );

            // 2. Get or create cart
            $cartId = $this->repo->getOrCreateCart($customerId);

            // 3. Handle print if Frame & Print
            $printingItemId = null;
            $serviceType    = 'FRAME_ONLY';

            if (!empty($data['service_type']) && $data['service_type'] === 'FRAME&PRINT') {
                $serviceType = 'FRAME&PRINT';
                $imagePath   = $data['image_path'] ?? '';
                $paperTypeId = (int)($data['paper_type_id'] ?? 0);
                $width       = $prices['width'];
                $height      = $prices['height'];
                $totalInch   = ($width + $height) * 2;
                $qty         = max(1, (int)($data['quantity'] ?? 1));

                $printingItemId = $this->repo->insertPrintingOrderItem(
                    $cartId, null, $paperTypeId,
                    $imagePath,
                    "{$width}x{$height}",
                    $width, $height, $totalInch,
                    $qty,
                    $prices['print_price']
                );
            }

            // 4. Insert frame order item
            $this->repo->insertFrameOrderItem(
                'CUSTOM', null, $cProductId,
                'CART', $cartId, null,
                $serviceType, $printingItemId,
                (!empty($data['primary_matboard_id']) && (int)$data['primary_matboard_id'] > 0)   ? (int)$data['primary_matboard_id']   : null,
                (!empty($data['secondary_matboard_id']) && (int)$data['secondary_matboard_id'] > 0) ? (int)$data['secondary_matboard_id'] : null,
                !empty($data['mount_type_id']) ? (int)$data['mount_type_id'] : null,
                max(1, (int)($data['quantity'] ?? 1)),
                $prices['base_price'],
                $prices['extra_price'],
                $prices['sub_total']
            );

            $this->conn->commit();
            return ['success' => true, 'message' => 'Added to cart successfully!'];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Failed to add to cart: ' . $e->getMessage()];
        }
    }

    // ── Buy Now (direct order) ───────────────────────────
    public function buyNow(int $customerId, array $data): array {
        $this->conn->begin_transaction();
        try {
            $prices = $this->calculatePrice($data);

            // 1. Insert custom frame product
            $cProductId = $this->repo->insertCustomFrameProduct(
                !empty($data['frame_type_id'])   ? (int)$data['frame_type_id']   : null,
                !empty($data['frame_design_id']) ? (int)$data['frame_design_id'] : null,
                !empty($data['frame_color_id'])  ? (int)$data['frame_color_id']  : null,
                (!empty($data['frame_size_id']) && $data['frame_size_id'] !== 'OTHER') ? (int)$data['frame_size_id'] : null,
                $prices['width'],
                $prices['height'],
                $prices['base_price']
            );

            // 2. Generate reference number
            $refNo = 'RGA-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            // 3. Create order
            $qty          = max(1, (int)($data['quantity'] ?? 1));
            $grandTotal   = $prices['grand_total'];
            $paymentMethod = strtoupper($data['payment_method'] ?? 'CASH');
            $deliveryOption = strtoupper($data['delivery_option'] ?? 'PICKUP');
            $deliveryAddress = $data['delivery_address'] ?? null;

            $orderId = $this->repo->insertOrder(
                $customerId, $refNo, $grandTotal,
                $paymentMethod, $deliveryOption, $deliveryAddress
            );

            // 4. Handle print if Frame & Print
            $printingItemId = null;
            $serviceType    = 'FRAME_ONLY';

            if (!empty($data['service_type']) && $data['service_type'] === 'FRAME&PRINT') {
                $serviceType = 'FRAME&PRINT';
                $imagePath   = $data['image_path'] ?? '';
                $paperTypeId = (int)($data['paper_type_id'] ?? 0);
                $width       = $prices['width'];
                $height      = $prices['height'];
                $totalInch   = ($width + $height) * 2;

                $printingItemId = $this->repo->insertPrintingOrderItem(
                    null, $orderId, $paperTypeId,
                    $imagePath,
                    "{$width}x{$height}",
                    $width, $height, $totalInch,
                    $qty,
                    $prices['print_price']
                );
            }

            // 5. Insert frame order item
            $this->repo->insertFrameOrderItem(
                'CUSTOM', null, $cProductId,
                'ORDER', null, $orderId,
                $serviceType, $printingItemId,
                (!empty($data['primary_matboard_id']) && (int)$data['primary_matboard_id'] > 0)   ? (int)$data['primary_matboard_id']   : null,
                (!empty($data['secondary_matboard_id']) && (int)$data['secondary_matboard_id'] > 0) ? (int)$data['secondary_matboard_id'] : null,
                !empty($data['mount_type_id']) ? (int)$data['mount_type_id'] : null,
                $qty,
                $prices['base_price'],
                $prices['extra_price'],
                $prices['sub_total']
            );

            $this->conn->commit();
            return [
                'success'  => true,
                'message'  => 'Order placed successfully!',
                'order_id' => $orderId,
                'ref_no'   => $refNo,
            ];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Failed to place order: ' . $e->getMessage()];
        }
    }
}