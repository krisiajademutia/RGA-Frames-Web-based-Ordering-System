<?php
// classes/Order/DirectOrderFactory.php

require_once __DIR__ . '/DirectOrderInterface.php';
require_once __DIR__ . '/../CustomFrame/CustomDirectOrderService.php';  // NEW
require_once __DIR__ . '/../ReadyMade/ReadyMadeDirectOrderService.php'; // NEW
require_once __DIR__ . '/../Printing/PrintingDirectOrderService.php';   // EXISTING

class DirectOrderFactory {
    public static function getService($conn, string $itemType): DirectOrderInterface {
        switch (strtoupper($itemType)) {
            case 'CUSTOM_FRAME':
                return new CustomDirectOrderService($conn);
                
            case 'READY_MADE':
                return new ReadyMadeDirectOrderService($conn);
                
            case 'PRINTING':
                return new PrintingDirectOrderService($conn); 
            
            default:
                throw new Exception("Unknown item type: " . $itemType);
        }
    }
}