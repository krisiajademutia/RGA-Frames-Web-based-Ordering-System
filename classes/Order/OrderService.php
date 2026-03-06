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
        $this->itemRepo = new OrderItemRepository($conn);
        $this->conn = $conn;

    }

    public function getDashboardSummary() {
        return $this->orderRepo->getSummaryCounts();
    }

    public function getOrdersForStatus($status, $filters = [])
    {
        return $this->orderRepo->getOrdersByStatus($status, $filters);
    }

    public function getFullOrderDetails(int $order_id) {
        $order = $this->orderRepo->getOrderById($order_id);
        if (!$order) return null;
        $order['items'] = $this->itemRepo->getItemsForOrder($order_id);
        return $order;
    }

    public function changeOrderStatus(int $order_id, string $new_status) {
        return $this->orderRepo->updateStatus($order_id, $new_status);
    }
}