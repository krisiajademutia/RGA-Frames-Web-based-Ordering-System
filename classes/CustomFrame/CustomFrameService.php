<?php
// classes/CustomFrame/CustomFrameService.php

require_once __DIR__ . '/../Order/DirectOrderInterface.php';
require_once __DIR__ . '/Repository/CustomFrameRepository.php';

class CustomFrameService implements DirectOrderInterface {
    private CustomFrameRepository $repo;
    private $conn;

    public function __construct($conn) {
        $this->repo = new CustomFrameRepository($conn);
        $this->conn = $conn;
    }

    /**
     * KEEP: Used by your custom shop screen to load dropdowns/prices
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

    /**
     * KEEP: Essential for calculating totals before saving to DB
     */
    public function calculatePrice(array $data): array {
        $basePrice  = 0.0;
        $extraPrice = 0.0;
        $printPrice = 0.0;

        $w = (float)($data['custom_width'] ?? $data['width'] ?? 0);
        $h = (float)($data['custom_height'] ?? $data['height'] ?? 0);

        if (!empty($data['frame_size_id']) && $data['frame_size_id'] !== 'OTHER') {
            $fs = $this->repo->getFrameSizeById((int)$data['frame_size_id']);
            if ($fs) {
                $w = (float)$fs['width_inch'];
                $h = (float)$fs['height_inch'];
            }
        }

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

        $primaryId   = (int)($data['primary_matboard_id']   ?? 0);
        $secondaryId = (int)($data['secondary_matboard_id'] ?? 0);

        if ($primaryId > 0 && $secondaryId > 0) {
            $mc1 = $this->repo->getMatboardById($primaryId);
            $extraPrice += $mc1 ? (float)$mc1['base_price'] : 0;
        }

        if (!empty($data['mount_type_id'])) {
            $mt = $this->repo->getMountById((int)$data['mount_type_id']);
            $extraPrice += $mt ? (float)$mt['additional_fee'] : 0;
        }

        if (!empty($data['service_type']) && $data['service_type'] === 'FRAME&PRINT' && $w > 0 && $h > 0) {
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

    /**
     * KEEP: Used for normal Cart additions
     */
    public function addToCart(int $customerId, array $data): array {
        $this->conn->begin_transaction();
        try {
            $prices = $this->calculatePrice($data);
            $cProductId = $this->repo->insertCustomFrameProduct(
                !empty($data['frame_type_id'])   ? (int)$data['frame_type_id']   : null,
                !empty($data['frame_design_id']) ? (int)$data['frame_design_id'] : null,
                !empty($data['frame_color_id'])  ? (int)$data['frame_color_id']  : null,
                $prices['width'], $prices['height'], $prices['base_price']
            );

            $cartId = $this->repo->getOrCreateCart($customerId);
            $printingItemId = null;
            $serviceType = (!empty($data['service_type']) && $data['service_type'] === 'FRAME&PRINT') ? 'FRAME&PRINT' : 'FRAME_ONLY';

            if ($serviceType === 'FRAME&PRINT') {
                $printingItemId = $this->repo->insertPrintingOrderItem(
                    $cartId, null, (int)($data['paper_type_id'] ?? 0),
                    $data['image_path'] ?? '', $prices['width'], $prices['height'],
                    max(1, (int)($data['quantity'] ?? 1)), $prices['print_price']
                );
            }

            $this->repo->insertFrameOrderItem(
                'CUSTOM', null, $cProductId, 'CART', $cartId, null,
                $serviceType, $printingItemId,
                (int)($data['primary_matboard_id'] ?? 0) ?: null,
                (int)($data['secondary_matboard_id'] ?? 0) ?: null,
                (int)($data['mount_type_id'] ?? 0) ?: null,
                max(1, (int)($data['quantity'] ?? 1)),
                $prices['base_price'], $prices['extra_price'], $prices['sub_total']
            );

            $this->conn->commit();
            return ['success' => true, 'message' => 'Added to cart successfully!'];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * UPDATED: Renamed to placeBuyNow to match DirectOrderInterface
     */
    public function placeBuyNow(int $customerId, array $data): array {
        $this->conn->begin_transaction();
        try {
            $prices = $this->calculatePrice($data);

            // 1. Insert Custom Product
            $cProductId = $this->repo->insertCustomFrameProduct(
                (int)($data['frame_type_id'] ?? 0) ?: null,
                (int)($data['frame_design_id'] ?? 0) ?: null,
                (int)($data['frame_color_id'] ?? 0) ?: null,
                $prices['width'], $prices['height'], $prices['base_price']
            );

            // 2. Insert Main Order
            $refNo          = 'RGA-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            $qty            = max(1, (int)($data['quantity'] ?? 1));
            $subTotal       = $prices['grand_total'];
            $discountAmount = (float)($data['discount_amount'] ?? 0.00);
            $finalTotal     = (float)($data['final_total'] ?? $subTotal);

            $orderId = $this->repo->insertOrder(
                $customerId, $refNo, $subTotal, $discountAmount, $finalTotal,
                strtoupper($data['payment_method'] ?? 'CASH'),
                strtoupper($data['delivery_option'] ?? 'PICKUP'),
                $data['delivery_address'] ?? null
            );

            // 3. Handle Printing if needed
            $printingItemId = null;
            $serviceType = (!empty($data['service_type']) && $data['service_type'] === 'FRAME&PRINT') ? 'FRAME&PRINT' : 'FRAME_ONLY';

            if ($serviceType === 'FRAME&PRINT') {
                $printingItemId = $this->repo->insertPrintingOrderItem(
                    null, $orderId, (int)($data['paper_type_id'] ?? 0),
                    $data['image_path'] ?? '', $prices['width'], $prices['height'],
                    $qty, $prices['print_price']
                );
            }

            // 4. Link everything
            $this->repo->insertFrameOrderItem(
                'CUSTOM', null, $cProductId, 'ORDER', null, $orderId,
                $serviceType, $printingItemId,
                (int)($data['primary_matboard_id'] ?? 0) ?: null,
                (int)($data['secondary_matboard_id'] ?? 0) ?: null,
                (int)($data['mount_type_id'] ?? 0) ?: null,
                $qty, $prices['base_price'], $prices['extra_price'], $prices['sub_total']
            );

            // 5. Payment record
            $this->repo->insertPayment($orderId, $finalTotal);

            $this->conn->commit();
            return ['success' => true, 'order_id' => $orderId, 'ref_no' => $refNo];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}