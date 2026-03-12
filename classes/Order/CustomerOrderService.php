<?php
// classes/Order/CustomerOrderService.php

require_once __DIR__ . '/Repository/CustomerOrderRepository.php';
require_once __DIR__ . '/Repository/OrderItemRepository.php';

class CustomerOrderService {
    private $repo;
    private $itemRepo;

    public function __construct($conn) {
        $this->repo     = new CustomerOrderRepository($conn);
        $this->itemRepo = new OrderItemRepository($conn);
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
        $order['proofs'] = !empty($order['payment_id'])
            ? $this->repo->getPaymentProofs((int)$order['payment_id'])
            : [];
        return $order;
    }
}