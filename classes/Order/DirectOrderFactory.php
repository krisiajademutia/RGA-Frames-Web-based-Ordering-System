<?php
require_once __DIR__ . '/DirectOrderInterface.php';
require_once __DIR__ . '/../CustomFrame/CustomFrameService.php';
require_once __DIR__ . '/../Printing/PrintingDirectOrderService.php'; // Ensure this exists

class DirectOrderFactory {
    public static function getService($conn, string $itemType): DirectOrderInterface {
        switch (strtoupper($itemType)) {
            case 'CUSTOM_FRAME':
                return new CustomFrameService($conn);
            case 'PRINTING':
                require_once __DIR__ . '/../Printing/PrintingDirectOrderService.php';
                return new PrintingDirectOrderService($conn); 
            default:
                throw new Exception("Unknown item type: " . $itemType);
        }
    }
}