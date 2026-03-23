<?php
// classes/Checkout/Repository/OrderStrategies.php

// 1. THE INTERFACE (Contract for all Savers)
interface OrderItemSaverInterface {
    public function saveItem($conn, int $orderId, array $itemData, float $subTotal, int $qty): void;
}

// 2. CUSTOM FRAME SAVER (Single Responsibility)
class CustomFrameSaver implements OrderItemSaverInterface {
    public function saveItem($conn, int $orderId, array $itemData, float $subTotal, int $qty): void {
        $service_type   = $itemData['service_type'] ?? 'FRAME_ONLY';
        $f_type_id      = !empty($itemData['frame_type_id']) ? $itemData['frame_type_id'] : null;
        $f_design_id    = !empty($itemData['frame_design_id']) ? $itemData['frame_design_id'] : null;
        $f_color_id     = !empty($itemData['frame_color_id']) ? $itemData['frame_color_id'] : null;
        $custom_w       = (float)($itemData['width'] ?? 0);
        $custom_h       = (float)($itemData['height'] ?? 0);
        $mat1_id        = !empty($itemData['primary_matboard_id']) ? $itemData['primary_matboard_id'] : null;
        $mat2_id        = !empty($itemData['secondary_matboard_id']) ? $itemData['secondary_matboard_id'] : null;
        $mount_id       = !empty($itemData['mount_type_id']) ? $itemData['mount_type_id'] : null;
        
        $base_price     = (float)($itemData['base_price'] ?? 0);
        $extra_price    = (float)($itemData['extra_price'] ?? 0);
        $print_price    = (float)($itemData['print_price'] ?? 0);
        $item_subtotal  = (float)($itemData['sub_total'] ?? $subTotal);
        
        // A. Create Custom Frame Profile
        $stmtCF = $conn->prepare("
            INSERT INTO tbl_custom_frame_product 
            (frame_type_id, frame_design_id, frame_color_id, custom_width, custom_height, calculated_price) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmtCF->bind_param("iiiddd", $f_type_id, $f_design_id, $f_color_id, $custom_w, $custom_h, $item_subtotal);
        $stmtCF->execute();
        $c_product_id = $conn->insert_id;

        // B. Attached Print
        $printing_id = null;
        if ($service_type === 'FRAME&PRINT') {
            $paper_id = !empty($itemData['paper_type_id']) ? $itemData['paper_type_id'] : null;
            $img_path = $itemData['image_path'] ?? null;

            $stmtPrint = $conn->prepare("
                INSERT INTO tbl_printing_order_items 
                (order_id, paper_type_id, image_path, width_inch, height_inch, quantity, sub_total) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtPrint->bind_param("iisddid", $orderId, $paper_id, $img_path, $custom_w, $custom_h, $qty, $print_price);
            $stmtPrint->execute();
            $printing_id = $conn->insert_id;
        }
        
        // C. Link Main Order
        $stmtOrder = $conn->prepare("
            INSERT INTO tbl_frame_order_items 
            (order_id, source_type, frame_category, c_product_id, service_type, printing_order_item_id, primary_matboard_id, secondary_matboard_id, mount_type_id, quantity, base_price, extra_price, sub_total) 
            VALUES (?, 'ORDER', 'CUSTOM', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtOrder->bind_param("iisiiiiiddd", $orderId, $c_product_id, $service_type, $printing_id, $mat1_id, $mat2_id, $mount_id, $qty, $base_price, $extra_price, $item_subtotal);
        $stmtOrder->execute();
    }
}

// 3. PRINTING SAVER (Single Responsibility)
class PrintingSaver implements OrderItemSaverInterface {
    public function saveItem($conn, int $orderId, array $itemData, float $subTotal, int $qty): void {
        $p_paper_id = !empty($itemData['paper_type_id']) ? $itemData['paper_type_id'] : null;
        $p_width    = (float)($itemData['width'] ?? 0);
        $p_height   = (float)($itemData['height'] ?? 0);
        $p_image    = (string)($itemData['image_path'] ?? '');

        $stmtPrint = $conn->prepare("
            INSERT INTO tbl_printing_order_items 
            (order_id, paper_type_id, image_path, width_inch, height_inch, quantity, sub_total) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtPrint->bind_param("iisddid", $orderId, $p_paper_id, $p_image, $p_width, $p_height, $qty, $subTotal);
        $stmtPrint->execute();
    }
}

// 4. READY MADE SAVER (Single Responsibility)
class ReadyMadeSaver implements OrderItemSaverInterface {
    public function saveItem($conn, int $orderId, array $itemData, float $subTotal, int $qty): void {
        
        $r_product_id = !empty($itemData['r_product_id']) ? (int)$itemData['r_product_id'] : null;
        $service_type = $itemData['service_type'] ?? 'FRAME_ONLY';
        
        $priMat       = !empty($itemData['primary_matboard_id']) ? $itemData['primary_matboard_id'] : null;
        $secMat       = !empty($itemData['secondary_matboard_id']) ? $itemData['secondary_matboard_id'] : null;
        $mount        = !empty($itemData['mount_type_id']) ? $itemData['mount_type_id'] : null;
        $printId      = !empty($itemData['printing_order_item_id']) ? $itemData['printing_order_item_id'] : null;
        
        $basePrice    = (float)($itemData['base_price'] ?? 0);
        $extPrice     = (float)($itemData['extra_price'] ?? 0);

        $stmtRM = $conn->prepare("
            INSERT INTO tbl_frame_order_items 
            (order_id, source_type, frame_category, r_product_id, service_type, 
             primary_matboard_id, secondary_matboard_id, mount_type_id, 
             printing_order_item_id, quantity, base_price, extra_price, sub_total) 
            VALUES (?, 'ORDER', 'READY_MADE', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmtRM->bind_param("iisssssiddd", 
            $orderId, 
            $r_product_id, 
            $service_type, 
            $priMat, 
            $secMat, 
            $mount, 
            $printId,
            $qty, 
            $basePrice, 
            $extPrice, 
            $subTotal
        );
        
        $stmtRM->execute();

        // 🔥 FIX: Go back and update the printing item to link it to this final order!
        if (!empty($printId)) {
            $stmtPrintUpdate = $conn->prepare("
                UPDATE tbl_printing_order_items 
                SET order_id = ?, cart_id = NULL 
                WHERE printing_order_item_id = ?
            ");
            $stmtPrintUpdate->bind_param("ii", $orderId, $printId);
            $stmtPrintUpdate->execute();
        }
    }
}

// 5. THE FACTORY (Open/Closed Principle)
class ItemSaverFactory {
    public static function make(string $itemType): OrderItemSaverInterface {
        switch ($itemType) {
            case 'CUSTOM_FRAME': return new CustomFrameSaver();
            case 'PRINTING':     return new PrintingSaver();
            case 'READY_MADE':   return new ReadyMadeSaver();
            default: throw new Exception("Unknown item type: " . $itemType);
        }
    }
}