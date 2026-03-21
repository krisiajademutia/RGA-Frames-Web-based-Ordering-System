<?php

namespace Classes\Cart\Repository;

interface CartRepositoryInterface
{
    public function fetchCartItems(int $customerId): array;
    public function fetchPrintItems(int $customerId): array;
    public function deleteItem(int $itemId): void;
    public function deletePrintItem(int $printingOrderItemId): void;
    public function deleteSelectedItems(array $itemIds, int $customerId): void;
    public function deleteSelectedPrintItems(array $printIds, int $customerId): void;
    public function deleteAllItems(int $customerId): void;
    public function deleteAllPrintItems(int $customerId): void;
}