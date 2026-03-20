<?php
// classes/Order/CustomerOrderService.php

require_once __DIR__ . '/Repository/CustomerOrderRepository.php';
require_once __DIR__ . '/Repository/OrderItemRepository.php';

class CustomerOrderService {
    private $repo;
    private $itemRepo;

    public function __construct($repo, $itemRepo) {
        $this->repo     = $repo;
        $this->itemRepo = $itemRepo;
    }

    public function getTabCounts(int $customer_id): array {
        return $this->repo->getCountsByCustomer($customer_id);
    }

    public function getOrders(int $customer_id, string $status = 'ALL', string $search = ''): array {
        return $this->repo->getOrdersByCustomer($customer_id, $status, $search);
    }
    public function getOrderDetails(int $order_id, int $customer_id): ?array {
        $order = $this->repo->getOrderByIdForCustomer($order_id, $customer_id);
        if (!$order) return null;

        $order['items']  = $this->itemRepo->getItemsForOrder($order_id);
        
        $order['proofs'] = [];
        $verified_paid = 0; // Start at 0

        // If there is a payment record, get the proofs and calculate the true paid amount
        if (!empty($order['payment_id'])) {
            $proofs = $this->repo->getPaymentProofs((int)$order['payment_id']);
            $order['proofs'] = $proofs;

            // Loop through proofs and ONLY add up the "Verified" ones
            foreach ($proofs as $proof) {
                if ($proof['verification_status'] === 'Verified') {
                    $verified_paid += (float)$proof['uploaded_amount'];
                }
            }
        }
        $order['amount_paid'] = $verified_paid;

        return $order;
    }
}