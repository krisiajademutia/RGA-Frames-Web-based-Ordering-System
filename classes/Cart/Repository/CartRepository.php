<?php

namespace Classes\Cart\Repository;

use mysqli;

class CartRepository implements CartRepositoryInterface
{
    public function __construct(private mysqli $conn) {}

    // ── Fetch raw rows + build display fields ───────────────────────────────
    public function fetchCartItems(int $customerId): array
    {
        $sql = "
            SELECT f.*,
                   fd.design_name,
                   fdi.image_name       AS design_image,
                   fc.color_name,
                   fs.dimension         AS frame_size,
                   COALESCE(p.width_inch,  c.custom_width,  r.width)  AS width_inch,
                   COALESCE(p.height_inch, c.custom_height, r.height) AS height_inch,
                   p.image_path         AS print_image,
                   r.product_name,
                   ft.type_name,
                   pt.paper_name,
                   mat.matboard_color_name,
                   mt.mount_name
            FROM tbl_frame_order_items f
            JOIN tbl_cart cart ON f.cart_id = cart.cart_id
            LEFT JOIN tbl_custom_frame_product c   ON f.c_product_id           = c.c_product_id
            LEFT JOIN tbl_ready_made_product   r   ON f.r_product_id           = r.r_product_id
            LEFT JOIN tbl_frame_designs        fd  ON (c.frame_design_id       = fd.frame_design_id OR r.frame_design_id = fd.frame_design_id)
            LEFT JOIN tbl_frame_design_images  fdi ON (fd.frame_design_id      = fdi.frame_design_id AND fdi.is_primary = 1)
            LEFT JOIN tbl_frame_colors         fc  ON (c.frame_color_id        = fc.frame_color_id  OR r.frame_color_id = fc.frame_color_id)
            LEFT JOIN tbl_frame_sizes          fs  ON (r.width = fs.width_inch AND r.height = fs.height_inch)
            LEFT JOIN tbl_frame_types          ft  ON (c.frame_type_id         = ft.frame_type_id   OR r.frame_type_id  = ft.frame_type_id)
            LEFT JOIN tbl_printing_order_items p   ON f.printing_order_item_id = p.printing_order_item_id
            LEFT JOIN tbl_paper_type           pt  ON p.paper_type_id          = pt.paper_type_id
            LEFT JOIN tbl_matboard_colors      mat ON f.primary_matboard_id    = mat.matboard_color_id
            LEFT JOIN tbl_mount_type           mt  ON f.mount_type_id          = mt.mount_type_id
            WHERE cart.customer_id = ? AND f.source_type = 'CART'
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return array_map([$this, 'buildDisplayFields'], $rows);
    }

    // ── Build display-ready fields from a raw DB row ────────────────────────
    private function buildDisplayFields(array $row): array
    {
        // Display image
        if (!empty($row['print_image'])) {
            $row['display_image'] = "../" . $row['print_image'];
        } elseif (!empty($row['design_image'])) {
            $row['display_image'] = "../uploads/" . $row['design_image'];
        } else {
            $row['display_image'] = null;
        }

        // Display name
        $design          = $row['design_name']  ?? $row['product_name'] ?? 'Frame';
        $size            = $row['frame_size']    ?? ((float)$row['width_inch'] . '"X' . (float)$row['height_inch'] . '"');
        $color           = $row['color_name']   ?? '';
        $row['display_name'] = trim("$design $size $color");

        // Flat id alias
        $row['id'] = $row['item_id'];

        // Expanded detail fields (for inline accordion)
        $row['detail_type']     = $row['type_name']           ?? '—';
        $row['detail_design']   = $row['design_name']         ?? '—';
        $row['detail_color']    = $row['color_name']          ?? '—';
        $row['detail_size']     = !empty($row['width_inch'])
            ? ((float)$row['width_inch'] . '" × ' . (float)$row['height_inch'] . '"')
            : '—';
        $row['detail_service']  = $row['service_type'] === 'FRAME&PRINT' ? 'Frame & Print' : 'Frame Only';
        $row['detail_paper']    = $row['paper_name']          ?? null;
        $row['detail_matboard'] = $row['matboard_color_name'] ?? null;
        $row['detail_mount']    = $row['mount_name']          ?? null;

        return $row;
    }

    // ── Delete single item ───────────────────────────────────────────────────
    public function deleteItem(int $itemId): void
    {
        $this->conn->begin_transaction();
        try {
            $find = $this->conn->prepare(
                "SELECT printing_order_item_id FROM tbl_frame_order_items WHERE item_id = ?"
            );
            $find->bind_param("i", $itemId);
            $find->execute();
            $row = $find->get_result()->fetch_assoc();

            $del = $this->conn->prepare("DELETE FROM tbl_frame_order_items WHERE item_id = ?");
            $del->bind_param("i", $itemId);
            $del->execute();

            if (!empty($row['printing_order_item_id'])) {
                $delP = $this->conn->prepare(
                    "DELETE FROM tbl_printing_order_items WHERE printing_order_item_id = ?"
                );
                $delP->bind_param("i", $row['printing_order_item_id']);
                $delP->execute();
            }

            $this->conn->commit();
        } catch (\Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // ── Delete selected items (only those belonging to this customer) ────────
    public function deleteSelectedItems(array $itemIds, int $customerId): void
    {
        if (empty($itemIds)) return;

        $this->conn->begin_transaction();
        try {
            $ph    = implode(',', array_fill(0, count($itemIds), '?'));
            $types = str_repeat('i', count($itemIds));

            $getP = $this->conn->prepare("
                SELECT f.item_id, f.printing_order_item_id
                FROM tbl_frame_order_items f
                JOIN tbl_cart c ON f.cart_id = c.cart_id
                WHERE f.item_id IN ($ph)
                  AND c.customer_id = ?
                  AND f.source_type = 'CART'
            ");
            $getP->bind_param($types . 'i', ...[...$itemIds, $customerId]);
            $getP->execute();
            $res = $getP->get_result();

            $validIds    = [];
            $printingIds = [];
            while ($row = $res->fetch_assoc()) {
                $validIds[] = $row['item_id'];
                if (!empty($row['printing_order_item_id'])) {
                    $printingIds[] = $row['printing_order_item_id'];
                }
            }

            if (!empty($validIds)) {
                $vPh = implode(',', array_fill(0, count($validIds), '?'));
                $vT  = str_repeat('i', count($validIds));

                $delF = $this->conn->prepare("DELETE FROM tbl_frame_order_items WHERE item_id IN ($vPh)");
                $delF->bind_param($vT, ...$validIds);
                $delF->execute();

                if (!empty($printingIds)) {
                    $pPh = implode(',', array_fill(0, count($printingIds), '?'));
                    $pT  = str_repeat('i', count($printingIds));
                    $delP = $this->conn->prepare(
                        "DELETE FROM tbl_printing_order_items WHERE printing_order_item_id IN ($pPh)"
                    );
                    $delP->bind_param($pT, ...$printingIds);
                    $delP->execute();
                }
            }

            $this->conn->commit();
        } catch (\Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // ── Delete all items for this customer ───────────────────────────────────
    public function deleteAllItems(int $customerId): void
    {
        $this->conn->begin_transaction();
        try {
            $getP = $this->conn->prepare(
                "SELECT printing_order_item_id
                 FROM tbl_frame_order_items f
                 JOIN tbl_cart c ON f.cart_id = c.cart_id
                 WHERE c.customer_id = ? AND f.source_type = 'CART'"
            );
            $getP->bind_param("i", $customerId);
            $getP->execute();
            $res  = $getP->get_result();
            $pIds = [];
            while ($row = $res->fetch_assoc()) {
                if ($row['printing_order_item_id']) $pIds[] = $row['printing_order_item_id'];
            }

            $delF = $this->conn->prepare(
                "DELETE f FROM tbl_frame_order_items f
                 JOIN tbl_cart c ON f.cart_id = c.cart_id
                 WHERE c.customer_id = ? AND f.source_type = 'CART'"
            );
            $delF->bind_param("i", $customerId);
            $delF->execute();

            if (!empty($pIds)) {
                $ph  = implode(',', array_fill(0, count($pIds), '?'));
                $pt  = str_repeat('i', count($pIds));
                $delP = $this->conn->prepare(
                    "DELETE FROM tbl_printing_order_items WHERE printing_order_item_id IN ($ph)"
                );
                $delP->bind_param($pt, ...$pIds);
                $delP->execute();
            }

            $this->conn->commit();
        } catch (\Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}