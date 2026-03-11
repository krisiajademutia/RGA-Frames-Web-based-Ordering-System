<?php
// classes/Order/OrderService.php

require_once __DIR__ . '/Repository/OrderRepository.php';
require_once __DIR__ . '/Repository/OrderItemRepository.php';

class OrderService {
    private $orderRepo;
    private $itemRepo;
    private $conn;

    public function __construct($conn) {
        $this->orderRepo = new OrderRepository($conn);
        $this->itemRepo  = new OrderItemRepository($conn);
        $this->conn      = $conn;
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

    public function recalculatePaymentStatus(int $payment_id) {
        // Get total_amount from tbl_payment
        $stmt = $this->conn->prepare("
            SELECT p.total_amount,
                   COALESCE(SUM(pu.uploaded_amount), 0) AS verified_total
            FROM tbl_payment p
            LEFT JOIN tbl_payment_proof_uploads pu 
                   ON p.payment_id = pu.payment_id 
                  AND pu.verification_status = 'Verified'
            WHERE p.payment_id = ?
            GROUP BY p.payment_id
        ");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

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