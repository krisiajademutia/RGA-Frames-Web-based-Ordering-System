<?php
// classes/Checkout/CheckoutService.php

require_once __DIR__ . '/Repository/CheckoutRepository.php';
require_once __DIR__ . '/../CustomFrame/CustomFrameService.php';

class CheckoutService {
    private $repo;
    private $conn;

    // ── Business rule constants ──────────────────────────
    const BULK_QTY_THRESHOLD = 30;   // min total frames
    const DISCOUNT_RATE      = 0.20; // 20%

    public function __construct($conn) {
        $this->repo = new CheckoutRepository($conn);
        $this->conn = $conn;
    }

    public function getCustomerDetails(int $customer_id): ?array {
        return $this->repo->getCustomerDetails($customer_id);
    }

    public function getCartItems(int $customer_id): array {
        return $this->repo->getCartItemsForCheckout($customer_id);
    }

    /**
     * Discount engine — flat 20% if ANY one rule is satisfied. No stacking.
     *
     * Rules:
     * 1. BULK_ORDER      — total qty >= 30 frames
     * 2. REPEAT_CUSTOMER — customer has at least 3 COMPLETED past orders (Kuya's Rule)
     * 3. PHOTOGRAPHER    — customer_type = 'PHOTOGRAPHER'
     *
     * Returns:
     * discount_amount (float) — peso amount off (0.00 if no rule fires)
     * qualified       (bool)  — whether any rule was met
     */
    public function calculateDiscount(int $customer_id, array $customer, array $cartItems, float $subtotal): array {
        $totalQty = array_sum(array_column($cartItems, 'quantity'));
        $qualified = false;

        // Rule 1: Bulk order (30+ items)
        if ($totalQty >= self::BULK_QTY_THRESHOLD) {
            $qualified = true;
        }

        // Rule 2: Repeat customer (FIXED: Now requires 3 or more completed orders)
        if (!$qualified && $this->repo->getCompletedOrderCount($customer_id) >= 3) {
            $qualified = true;
        }

        // Rule 3: Photographer
        if (!$qualified && strtoupper($customer['customer_type'] ?? '') === 'PHOTOGRAPHER') {
            $qualified = true;
        }

        // Because of the !$qualified checks above, it is IMPOSSIBLE to get 40% or 60%.
        // It maxes out at exactly 20%.
        $discountAmount = $qualified ? round($subtotal * self::DISCOUNT_RATE, 2) : 0.00;

        return [
            'qualified'       => $qualified,
            'discount_amount' => $discountAmount,
        ];
    }

    // ✅ DELIVERY CHECK (FIXED)
    public function isDeliveryUnlocked(array $cartItems): bool {
        return array_sum(array_column($cartItems, 'quantity')) >= self::BULK_QTY_THRESHOLD;
    }

    // ✅ MAIN CHECKOUT (FULLY FIXED)
    public function processCheckout(
        int $customer_id,
        array $post,
        array $files,
        array $cartItems,
        float $cartTotal,
        bool $isBuyNow = false,
        ?array $buyNowItemData = null
    ): array {

        if (empty($cartItems) && !$isBuyNow) {
            return ['success' => false, 'message' => 'Your cart is empty!'];
        }

        // 🔥 STEP 1: FIX BUY NOW TOTAL (SERVER-SIDE RECOMPUTE)
        if ($isBuyNow && $buyNowItemData) {
            $itemType = $buyNowItemData['item_type'] ?? 'CUSTOM_FRAME';
            
            if ($itemType === 'CUSTOM_FRAME') {
                // Only use CustomFrameService to recompute if it's actually a frame
                $cfService = new CustomFrameService($this->conn);
                $priceData = $cfService->calculatePrice($buyNowItemData);
                $cartTotal = $priceData['grand_total'];
            } else {
                // For PRINTING and READY_MADE, trust the price already calculated
                $cartTotal = (float)($buyNowItemData['total_price'] ?? $cartTotal);
            }
        }

        // 🔥 STEP 2: NORMALIZE ITEMS FOR DISCOUNT + DELIVERY
        $normalizedItems = $cartItems;

        if ($isBuyNow && $buyNowItemData) {
            $normalizedItems = [[
                'quantity' => (int)($buyNowItemData['quantity'] ?? 1)
            ]];
        }

        $delivery_option = strtoupper(trim($post['delivery_option'] ?? 'PICKUP'));
        $payment_method  = strtoupper(trim($post['payment_method'] ?? 'CASH'));

        $address = ($delivery_option === 'DELIVERY')
            ? trim($post['delivery_address'] ?? '')
            : null;

        if ($delivery_option === 'DELIVERY' && empty($address)) {
            return ['success' => false, 'message' => 'Please enter your delivery address.'];
        }

        // 🔥 FIXED DELIVERY CHECK
        if ($delivery_option === 'DELIVERY' && !$this->isDeliveryUnlocked($normalizedItems)) {
            return ['success' => false, 'message' => 'Delivery is only available for orders of 30 or more frames.'];
        }

        $delivery_fee = ($delivery_option === 'DELIVERY') ? 150.00 : 0.00;

        // 🔥 FIXED DISCOUNT
        $customer = $this->repo->getCustomerDetails($customer_id);
        $discount = $this->calculateDiscount($customer_id, $customer, $normalizedItems, $cartTotal);

        $total_price = round(($cartTotal - $discount['discount_amount']) + $delivery_fee, 2);
        if ($total_price < 0) $total_price = 0.00;

        $orderData = [
            'reference_no'     => 'RGA-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
            'sub_total'        => $cartTotal,
            'discount_amount'  => $discount['discount_amount'],
            'total_price'      => $total_price,
            'payment_method'   => $payment_method,
            'delivery_option'  => $delivery_option,
            'delivery_address' => $address,
        ];

        $paymentProof = null;

        // GCash handling
        if ($payment_method === 'GCASH') {
            if (!isset($files['receipt_image']) || $files['receipt_image']['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => 'GCash receipt is required.'];
            }

            $uploadDir = __DIR__ . '/../../uploads/uploaded_receipts/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = strtolower(pathinfo($files['receipt_image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                return ['success' => false, 'message' => 'Invalid file format.'];
            }

            if ($files['receipt_image']['size'] > 10 * 1024 * 1024) {
                return ['success' => false, 'message' => 'File too large.'];
            }

            $fileName = 'gcash_' . $customer_id . '_' . time() . '.' . $ext;
            $dest     = $uploadDir . $fileName;

            if (!move_uploaded_file($files['receipt_image']['tmp_name'], $dest)) {
                return ['success' => false, 'message' => 'Upload failed.'];
            }

            $paymentProof = [
                'file_path' => 'uploads/uploaded_receipts/' . $fileName,
                'amount'    => (float)($post['gcash_amount'] ?? 0),
            ];
        }

        // 🔥 FINAL SAVE
        $success = $this->repo->placeOrder(
            $customer_id,
            $orderData,
            $cartItems,
            $paymentProof,
            $isBuyNow,
            $buyNowItemData
        );

        return $success
            ? ['success' => true, 'message' => 'Order placed successfully!', 'ref_no' => $orderData['reference_no']]
            : ['success' => false, 'message' => 'Something went wrong.'];
    }
}