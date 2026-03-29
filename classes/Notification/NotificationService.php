<?php
// classes/Notification/NotificationService.php

class NotificationService {
    private $repo;

    public function __construct(NotificationRepository $repository) {
        $this->repo = $repository;
    }

    // Notify a specific customer
    public function notifyCustomer($customer_id, $order_id, $title, $message) {
        // If order_id is 0 or less, convert it to NULL so the database doesn't crash
        $order_id_val = ($order_id > 0) ? $order_id : null;
        return $this->repo->createCustomerNotification($customer_id, $order_id_val, $title, $message);
    }

    // Notify the admin (customer_id is set to NULL)
    public function notifyAdmin($order_id, $title, $message) {
        // If order_id is 0 or less, convert it to NULL so the database doesn't crash
        $order_id_val = ($order_id > 0) ? $order_id : null;
        return $this->repo->createAdminNotification($order_id_val, $title, $message);
    }

    // Helper function to get the cool Reference Number
    public function getOrderReference($order_id) {
        $ref = $this->repo->getOrderReference($order_id);
        return $ref ? $ref : "#" . $order_id;
    }
}
?>