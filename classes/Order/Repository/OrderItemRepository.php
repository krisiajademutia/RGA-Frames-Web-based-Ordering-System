<?php
// classes/Order/Repository/OrderItemRepository.php

class OrderItemRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getItemsForOrder(int $order_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                i.*,
                rm.product_name AS ready_name, rm.width, rm.height, rm.product_price,
                fc.color_name, ft.type_name, fd.design_name,
                mt.mount_name, mt.additional_fee AS mount_extra,
                mc.matboard_color_name, mc.base_price AS matboard_base_price,
                cfp.custom_width, cfp.custom_height, cfp.calculated_price,
                poi.image_path, poi.width_inch AS print_width, poi.height_inch AS print_height,
                poi.total_inch AS print_total_inch, poi.quantity AS print_quantity,
                poi.sub_total AS print_sub_total,
                pt.paper_name, pt.pricing_logic
            FROM tbl_frame_order_items i
            LEFT JOIN tbl_ready_made_product rm       ON i.r_product_id = rm.r_product_id
            LEFT JOIN tbl_custom_frame_product cfp    ON i.c_product_id = cfp.c_product_id
            LEFT JOIN tbl_frame_colors fc             ON rm.frame_color_id = fc.frame_color_id
                                                     OR cfp.frame_color_id = fc.frame_color_id
            LEFT JOIN tbl_frame_types ft              ON rm.frame_type_id = ft.frame_type_id
                                                     OR cfp.frame_type_id = ft.frame_type_id
            LEFT JOIN tbl_frame_designs fd            ON rm.frame_design_id = fd.frame_design_id
                                                     OR cfp.frame_design_id = fd.frame_design_id
            LEFT JOIN tbl_mount_type mt               ON i.mount_type_id = mt.mount_type_id
            LEFT JOIN tbl_matboard_colors mc          ON i.primary_matboard_id = mc.matboard_color_id
            LEFT JOIN tbl_printing_order_items poi    ON i.printing_order_item_id = poi.printing_order_item_id
            LEFT JOIN tbl_paper_type pt               ON poi.paper_type_id = pt.paper_type_id
            WHERE i.order_id = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}