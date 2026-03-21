<?php

namespace Classes\Cart;

use Classes\Cart\Repository\CartRepositoryInterface;

class CartService
{
    public function __construct(private CartRepositoryInterface $repository) {}

    public function getCartItems(int $customerId): array
    {
        return $this->repository->fetchCartItems($customerId);
    }

    public function removeItem(int $itemId): void
    {
        $this->repository->deleteItem($itemId);
    }

    public function removeSelectedItems(array $rawIds, int $customerId): void
    {
        $sanitized = array_values(
            array_filter(array_map('intval', $rawIds), fn($id) => $id > 0)
        );
        if (empty($sanitized)) return;
        $this->repository->deleteSelectedItems($sanitized, $customerId);
    }

    public function removeAllItems(int $customerId): void
    {
        $this->repository->deleteAllItems($customerId);
        $this->repository->deleteAllPrintItems($customerId);
    }

    public function removePrintItem(int $printingOrderItemId): void
    {
        $this->repository->deletePrintItem($printingOrderItemId);
    }

    public function removeSelectedPrintItems(array $rawIds, int $customerId): void
    {
        $sanitized = array_values(
            array_filter(array_map('intval', $rawIds), fn($id) => $id > 0)
        );
        if (empty($sanitized)) return;
        $this->repository->deleteSelectedPrintItems($sanitized, $customerId);
    }
}