<?php
// classes/Checkout/CheckoutService.php
require_once __DIR__ . '/Repository/CheckoutRepository.php';

class CheckoutService {
    private $repo;

    public function __construct($conn) {
        $this->repo = new CheckoutRepository($conn);
    }

    public function getCustomerDetails(int $customer_id): ?array {
        return $this->repo->getCustomerDetails($customer_id);
    }

    public function getCartItems(int $customer_id): array {
        return $this->repo->getCartItemsForCheckout($customer_id);
    }

    public function processCheckout(int $customer_id, array $post, array $files, array $cartItems, float $cartTotal): array {
        if (empty($cartItems)) {
            return ['success' => false, 'message' => 'Your cart is empty!'];
        }

        $delivery_option = strtoupper(trim($post['delivery_option'] ?? 'PICKUP'));
        $payment_method  = strtoupper(trim($post['payment_method']  ?? 'CASH'));

        // Fixed: read delivery_address (matches form field name)
        $address = ($delivery_option === 'DELIVERY')
            ? trim($post['delivery_address'] ?? '')
            : null;

        if ($delivery_option === 'DELIVERY' && empty($address)) {
            return ['success' => false, 'message' => 'Please enter your delivery address.'];
        }

        $delivery_fee = ($delivery_option === 'DELIVERY') ? 150.00 : 0.00;
        $total_price  = $cartTotal + $delivery_fee;

        $orderData = [
            'total_price'      => $total_price,
            'payment_method'   => $payment_method,
            'delivery_option'  => $delivery_option,
            'delivery_address' => $address,
            'reference_no'     => 'RGA-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
        ];

        $paymentProof = null;

        if ($payment_method === 'GCASH') {
            if (!isset($files['receipt_image']) || $files['receipt_image']['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => 'GCash receipt is required.'];
            }

            $uploadDir = __DIR__ . '/../../uploads/uploaded_receipts/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = strtolower(pathinfo($files['receipt_image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                return ['success' => false, 'message' => 'Invalid file format. Only JPG, PNG, WEBP allowed.'];
            }

            if ($files['receipt_image']['size'] > 10 * 1024 * 1024) {
                return ['success' => false, 'message' => 'Receipt image too large. Maximum 10MB.'];
            }

            $fileName = 'gcash_checkout_' . $customer_id . '_' . time() . '.' . $ext;
            $dest     = $uploadDir . $fileName;

            if (!move_uploaded_file($files['receipt_image']['tmp_name'], $dest)) {
                return ['success' => false, 'message' => 'Failed to upload receipt. Please try again.'];
            }

            $paymentProof = [
                'file_path' => 'uploads/uploaded_receipts/' . $fileName,
                'amount'    => (float)($post['gcash_amount'] ?? 0),
            ];
        }

        $success = $this->repo->placeOrder($customer_id, $orderData, $cartItems, $paymentProof);

        return $success
            ? ['success' => true,  'message' => 'Order placed successfully!', 'ref_no' => $orderData['reference_no']]
            : ['success' => false, 'message' => 'Something went wrong. Please try again.'];
    }
}