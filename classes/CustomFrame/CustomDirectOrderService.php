<?php
// classes/CustomFrame/CustomDirectOrderService.php

require_once __DIR__ . '/../Order/DirectOrderInterface.php';
require_once __DIR__ . '/Repository/CustomFrameRepository.php';
require_once __DIR__ . '/CustomFrameService.php';

class CustomDirectOrderService implements DirectOrderInterface {
    private CustomFrameRepository $repo;
    private CustomFrameService $customFrameService;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->repo = new CustomFrameRepository($conn);
        $this->customFrameService = new CustomFrameService($conn);
    }

    public function placeBuyNow(int $customerId, array $data): array {
        $this->conn->begin_transaction();
        try {
            // 1. Calculate the TRUE price (Using the fixed function that includes Matboards/Prints)
            $prices = $this->customFrameService->calculatePrice($data);

            // 2. Insert Custom Product
            $cProductId = $this->repo->insertCustomFrameProduct(
                (int)($data['frame_type_id'] ?? 0) ?: null,
                (int)($data['frame_design_id'] ?? 0) ?: null,
                (int)($data['frame_color_id'] ?? 0) ?: null,
                $prices['width'], $prices['height'], $prices['base_price']
            );

            // 3. Accept the secure discount passed directly from checkout_process.php
            $qty            = max(1, (int)($data['quantity'] ?? 1));
            $subTotal       = $prices['grand_total'];
            $discountAmount = (float)($data['discount_amount'] ?? 0.00);
            $finalTotal     = (float)($data['final_total'] ?? $subTotal);

            // 4. Insert Main Order
            $refNo = 'RGA-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            $orderId = $this->repo->insertOrder(
                $customerId, $refNo, $subTotal, $discountAmount, $finalTotal,
                strtoupper($data['payment_method'] ?? 'CASH'),
                strtoupper($data['delivery_option'] ?? 'PICKUP'),
                $data['delivery_address'] ?? null
            );

            // 5. Handle Printing safely (Ignores spaces or weird characters from frontend)
            $printingItemId = null;
            $rawServiceType = strtoupper(str_replace([' ', '_', '&'], '', $data['service_type'] ?? ''));

            if ($rawServiceType === 'FRAMEPRINT') {
                $printingItemId = $this->repo->insertPrintingOrderItem(
                    null, $orderId, (int)($data['paper_type_id'] ?? 0),
                    $data['image_path'] ?? '', $prices['width'], $prices['height'],
                    $qty, $prices['print_price']
                );
            }

            // 6. Link everything
            $this->repo->insertFrameOrderItem(
                'CUSTOM', null, $cProductId, 'ORDER', null, $orderId,
                (!empty($data['service_type']) && str_contains(strtoupper($data['service_type']), 'PRINT')) ? 'FRAME&PRINT' : 'FRAME_ONLY', 
                $printingItemId,
                (int)($data['primary_matboard_id'] ?? 0) ?: null,
                (int)($data['secondary_matboard_id'] ?? 0) ?: null,
                (int)($data['mount_type_id'] ?? 0) ?: null,
                $qty, $prices['base_price'], $prices['extra_price'], $prices['sub_total']
            );

            // 7. Payment record
            $this->repo->insertPayment($orderId, $finalTotal);

            $this->conn->commit();
            return ['success' => true, 'order_id' => $orderId, 'ref_no' => $refNo];

        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Database Error: ' . $e->getMessage()];
        }
    }
}