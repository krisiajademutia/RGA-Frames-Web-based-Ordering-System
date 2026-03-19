<?php

namespace Classes\Cart\Repository;

interface CartRepositoryInterface
{
    public function fetchCartItems(int $customerId): array;
    public function deleteItem(int $itemId): void;
    public function deleteSelectedItems(array $itemIds, int $customerId): void;
    public function deleteAllItems(int $customerId): void;
}