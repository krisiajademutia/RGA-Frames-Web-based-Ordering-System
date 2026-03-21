<?php
// classes/Order/Repository/OrderItemRepository.php

class OrderItemRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getItemsForOrder(int $order_id) {
        // ---------------------------------------------------------
        // 1. FETCH ALL FRAME ITEMS (Ready-Made, Custom, Frame+Print)
        // ---------------------------------------------------------
        $stmt1 = $this->conn->prepare("
            SELECT
                i.*,
                i.sub_total AS sub_total,

                -- Ready-made product fields
                rm.product_name  AS ready_name,
                rm.width         AS width,
                rm.height        AS height,
                rm.product_price,
                rm.frame_design_id AS rm_frame_design_id,

                -- Custom frame product fields
                cfp.custom_width,
                cfp.custom_height,
                cfp.calculated_price,
                cfp.frame_design_id AS cfp_frame_design_id,

                -- Frame details
                fc.color_name,
                ft.type_name,
                fd.design_name,

                -- Mount
                mt.mount_name,
                mt.additional_fee AS mount_extra,

                -- Matboards
                mc_pri.matboard_color_name,
                mc_pri.base_price          AS matboard_base_price,
                mc_sec.matboard_color_name AS secondary_matboard_color_name,
                mc_sec.base_price          AS sec_matboard_base_price,

                -- PRINT DETAILS (EXACT names from your admin_order_details.php)
                poi.width_inch  AS print_width,
                poi.height_inch AS print_height,
                (poi.width_inch + poi.height_inch) AS print_total_inch,
                poi.sub_total   AS print_sub_total,
                poi.image_path,
                pt.paper_name

            FROM tbl_frame_order_items i

            LEFT JOIN tbl_ready_made_product rm    ON i.r_product_id = rm.r_product_id
            LEFT JOIN tbl_custom_frame_product cfp ON i.c_product_id = cfp.c_product_id

            LEFT JOIN tbl_frame_colors fc  ON fc.frame_color_id  = COALESCE(cfp.frame_color_id, rm.frame_color_id)
            LEFT JOIN tbl_frame_types ft   ON ft.frame_type_id   = COALESCE(cfp.frame_type_id, rm.frame_type_id)
            LEFT JOIN tbl_frame_designs fd ON fd.frame_design_id = COALESCE(cfp.frame_design_id, rm.frame_design_id)

            LEFT JOIN tbl_mount_type mt            ON i.mount_type_id = mt.mount_type_id
            LEFT JOIN tbl_matboard_colors mc_pri   ON i.primary_matboard_id = mc_pri.matboard_color_id
            LEFT JOIN tbl_matboard_colors mc_sec   ON i.secondary_matboard_id = mc_sec.matboard_color_id

            LEFT JOIN tbl_printing_order_items poi ON i.printing_order_item_id = poi.printing_order_item_id
            LEFT JOIN tbl_paper_type pt            ON poi.paper_type_id = pt.paper_type_id

            WHERE i.order_id = ?
        ");
        $stmt1->bind_param("i", $order_id);
        $stmt1->execute();
        $frames = $stmt1->get_result()->fetch_all(MYSQLI_ASSOC);


        // ---------------------------------------------------------
        // 2. FETCH STANDALONE PRINTING ITEMS
        // ---------------------------------------------------------
        $stmt2 = $this->conn->prepare("
            SELECT
                -- Core Item details
                poi.printing_order_item_id AS item_id,
                'PRINTING'   AS frame_category,
                NULL         AS r_product_id,
                NULL         AS c_product_id,
                'ORDER'      AS source_type,
                NULL         AS cart_id,
                poi.order_id AS order_id,
                'PRINT_ONLY' AS service_type,
                poi.printing_order_item_id AS printing_order_item_id,
                NULL         AS primary_matboard_id,
                NULL         AS secondary_matboard_id,
                NULL         AS mount_type_id,
                poi.quantity AS quantity,
                poi.sub_total AS sub_total, -- This fixes the main header price

                -- Prevent NULL errors for missing frame specs
                NULL AS ready_name,
                NULL AS width,
                NULL AS height,
                NULL AS product_price,
                NULL AS rm_frame_design_id,
                NULL AS custom_width,
                NULL AS custom_height,
                NULL AS calculated_price,
                NULL AS cfp_frame_design_id,
                NULL AS color_name,
                NULL AS type_name,
                NULL AS design_name,
                NULL AS mount_name,
                0    AS mount_extra,
                NULL AS matboard_color_name,
                0    AS matboard_base_price,
                NULL AS sec_matboard_color_name,
                0    AS sec_matboard_base_price,

                -- EXACT PRINT DETAILS (Matches line 275+ of admin_order_details)
                poi.width_inch  AS print_width,
                poi.height_inch AS print_height,
                (poi.width_inch + poi.height_inch) AS print_total_inch,
                poi.sub_total   AS print_sub_total,
                poi.image_path,
                pt.paper_name

            FROM tbl_printing_order_items poi
            LEFT JOIN tbl_paper_type pt ON poi.paper_type_id = pt.paper_type_id

            WHERE poi.order_id = ?
              AND poi.printing_order_item_id NOT IN (
                  SELECT IFNULL(printing_order_item_id, 0)
                  FROM tbl_frame_order_items
                  WHERE order_id = ?
              )
        ");
        $stmt2->bind_param("ii", $order_id, $order_id);
        $stmt2->execute();
        $prints = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

        return array_merge($frames, $prints);
    }
}