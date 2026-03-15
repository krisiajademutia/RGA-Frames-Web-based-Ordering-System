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
                i.sub_total AS sub_total,

                -- Ready-made product fields
                rm.product_name  AS ready_name,
                rm.width,
                rm.height,
                rm.product_price,
                rm.frame_design_id AS rm_frame_design_id,

                -- Custom frame product fields
                cfp.custom_width,
                cfp.custom_height,
                cfp.calculated_price,
                cfp.frame_design_id AS cfp_frame_design_id,

                -- Frame color (resolved from whichever product exists)
                fc.color_name,

                -- Frame type
                ft.type_name,

                -- Frame design name
                fd.design_name,

                -- Mount
                mt.mount_name,
                mt.additional_fee AS mount_extra,

                -- Primary matboard
                mc_pri.matboard_color_name,
                mc_pri.base_price          AS matboard_base_price,

                -- Secondary matboard
                mc_sec.matboard_color_name AS secondary_matboard_color_name,
                mc_sec.base_price          AS secondary_matboard_base_price,

                -- Printing order item
                poi.image_path,
                poi.width_inch   AS print_width,
                poi.height_inch  AS print_height,
                poi.quantity     AS print_quantity,
                poi.sub_total    AS print_sub_total,

                -- Paper type
                pt.paper_name

            FROM tbl_frame_order_items i

            -- Ready-made
            LEFT JOIN tbl_ready_made_product rm
                   ON i.r_product_id = rm.r_product_id

            -- Custom frame
            LEFT JOIN tbl_custom_frame_product cfp
                   ON i.c_product_id = cfp.c_product_id

            -- Frame color — use COALESCE to pick the right FK
            LEFT JOIN tbl_frame_colors fc
                   ON fc.frame_color_id = COALESCE(cfp.frame_color_id, rm.frame_color_id)

            -- Frame type — use COALESCE
            LEFT JOIN tbl_frame_types ft
                   ON ft.frame_type_id = COALESCE(cfp.frame_type_id, rm.frame_type_id)

            -- Frame design — use COALESCE
            LEFT JOIN tbl_frame_designs fd
                   ON fd.frame_design_id = COALESCE(cfp.frame_design_id, rm.frame_design_id)

            -- Mount type
            LEFT JOIN tbl_mount_type mt
                   ON i.mount_type_id = mt.mount_type_id

            -- Primary matboard color
            LEFT JOIN tbl_matboard_colors mc_pri
                   ON i.primary_matboard_id = mc_pri.matboard_color_id

            -- Secondary matboard color
            LEFT JOIN tbl_matboard_colors mc_sec
                   ON i.secondary_matboard_id = mc_sec.matboard_color_id

            -- Printing order item
            LEFT JOIN tbl_printing_order_items poi
                   ON i.printing_order_item_id = poi.printing_order_item_id

            -- Paper type
            LEFT JOIN tbl_paper_type pt
                   ON poi.paper_type_id = pt.paper_type_id

            WHERE i.order_id = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}