<?php
// classes/Order/OrderService.php

require_once __DIR__ . '/Repository/OrderRepository.php';
require_once __DIR__ . '/Repository/OrderItemRepository.php';

class OrderService {
    private $orderRepo;
    private $itemRepo;

    public function __construct($conn) {
        $this->orderRepo = new OrderRepository($conn);
        $this->itemRepo  = new OrderItemRepository($conn);
    }

    public function getDashboardSummary() {
        return $this->orderRepo->getSummaryCounts();
    }

    public function getOrdersForStatus($status, $filters = []) {
        return $this->orderRepo->getOrdersByStatus($status, $filters);
    }

    public function getFullOrderDetails(int $order_id) {
        $order = $this->orderRepo->getOrderById($order_id);
        if (!$order) return null;
        $order['items']  = $this->itemRepo->getItemsForOrder($order_id);
        // Fetch all proof uploads for this order's payment
        $order['proofs'] = !empty($order['payment_id'])
            ? $this->orderRepo->getPaymentProofs((int)$order['payment_id'])
            : [];
        return $order;
    }

    public function changeOrderStatus(int $order_id, string $new_status) {
        return $this->orderRepo->updateStatus($order_id, $new_status);
    }

    public function verifyProof(int $upload_id, int $payment_id) {
        $result = $this->orderRepo->verifyProof($upload_id);
        if ($result) {
            // Recalculate payment status after verification
            $this->recalculatePaymentStatus($payment_id);
        }
        return $result;
    }

    public function rejectProof(int $upload_id) {
        return $this->orderRepo->rejectProof($upload_id);
    }

    public function logCashPayment(int $payment_id, float $amount) {
        $this->orderRepo->logCashPayment($payment_id, $amount);
        $this->recalculatePaymentStatus($payment_id);
        // Return order_id so controller can notify
        $paymentData = $this->orderRepo->getPaymentTotals($payment_id);
        return $paymentData ? (int)$paymentData['order_id'] : 0;
    }

    public function recalculatePaymentStatus(int $payment_id) {
        $row = $this->orderRepo->getPaymentTotals($payment_id);

        if (!$row) return;

        $total    = (float)$row['total_amount'];
        $verified = (float)$row['verified_total'];

        if ($verified <= 0) {
            $status = 'PENDING';
        } elseif ($verified >= $total) {
            $status = 'FULL';
        } else {
            $status = 'PARTIAL';
        }

        $this->orderRepo->updatePaymentStatus($payment_id, $status);
    }
}