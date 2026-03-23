<?php
namespace Classes\ReadyMade;

use Classes\ReadyMade\Repository\IReadyMadeRepository;

class ReadyMadeService
{
    private IReadyMadeRepository $repo;

    public function __construct(IReadyMadeRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getFrames(string $search = ''): array
    {
        $all = $this->repo->getAll();
        if ($search === '') return $all;
        
        $term = strtolower(trim($search));
        return array_values(array_filter($all, function (array $frame) use ($term): bool {
            return str_contains(strtolower($frame['product_name'] ?? ''), $term)
                || str_contains(strtolower($frame['design_name']  ?? ''), $term)
                || str_contains(strtolower($frame['type_name']    ?? ''), $term)
                || str_contains(strtolower($frame['color_name']   ?? ''), $term);
        }));
    }

    public function getFrameById(int $id): ?array { return $this->repo->getById($id); }
    public function getMatboardColors(): array { return $this->repo->getMatboardColors(); }
    public function getMatboardColorPrice(int $colorId): float { return $this->repo->getMatboardColorPrice($colorId); }
    public function getPaperTypes(): array { return $this->repo->getPaperTypes(); }
    public function getPaperTypeMultiplier(int $paperTypeId): float { return $this->repo->getPaperTypeMultiplier($paperTypeId); }
    public function getMountTypes(): array { return $this->repo->getMountTypes(); }
    public function getMountTypeFee(int $mountTypeId): float { return $this->repo->getMountTypeFee($mountTypeId); }
    public function decrementStock(int $productId, int $quantity): bool { return $this->repo->decrementStock($productId, $quantity); }

    public function addToCart(int $customerId, array $itemData, int $cartId): array
    {
        if (($itemData['quantity'] ?? 0) < 1) return ['success' => false, 'message' => 'Quantity must be at least 1.'];
        if (($itemData['r_product_id'] ?? 0) < 1) return ['success' => false, 'message' => 'Product not found.'];

        $ok = $this->repo->addToCart($customerId, $itemData, $cartId);

        return $ok
            ? ['success' => true,  'message' => 'Added to cart successfully!']
            : ['success' => false, 'message' => 'Failed to add to cart.'];
    }

    // --- THIS IS THE FUNCTION THAT FIXES THE CHECKOUT PAGE! ---
    public function buildBuyNowPayload(array $data): array
    {
        $productId = (int)($data['r_product_id'] ?? 0);
        $quantity  = (int)($data['quantity'] ?? 1);

        if ($quantity < 1) {
            return ['success' => false, 'message' => 'Quantity must be at least 1.'];
        }

        $product = $this->repo->getById($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found.'];
        }

        if ((int)$product['stock'] < $quantity) {
            return ['success' => false, 'message' => 'Only ' . $product['stock'] . ' unit(s) left in stock.'];
        }

        // We perfectly package all the data so customer_checkout.php can read it!
        return [
            'success'      => true,
            'payload'      => [
                'item_type'              => 'READY_MADE', // Forces checkout to recognize it as Ready-Made!
                'r_product_id'           => $productId,
                'product_name'           => $product['product_name'] ?? 'Unknown',
                'width'                  => $product['width'] ?? 0,
                'height'                 => $product['height'] ?? 0,
                'quantity'               => $quantity,
                'service_type'           => $data['service_type'] ?? 'FRAME_ONLY',
                'primary_matboard_id'    => $data['primary_matboard_id'] ?? null,
                'secondary_matboard_id'  => $data['secondary_matboard_id'] ?? null,
                'mount_type_id'          => $data['mount_type_id'] ?? null,
                'printing_order_item_id' => $data['printing_order_item_id'] ?? null,
                'base_price'             => $data['base_price'] ?? 0,
                'extra_price'            => $data['extra_price'] ?? 0,
                'sub_total'              => $data['sub_total'] ?? 0,
                'total_price'            => $data['total_price'] ?? 0
            ]
        ];
    }
}