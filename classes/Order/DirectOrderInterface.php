<?php
// classes/Order/DirectOrderInterface.php
interface DirectOrderInterface {
    public function placeBuyNow(int $customerId, array $data): array;
}