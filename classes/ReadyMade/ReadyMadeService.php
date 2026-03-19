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

        if ($search === '') {
            return $all;
        }

        $term = strtolower(trim($search));
        return array_values(array_filter($all, function (array $frame) use ($term): bool {
            return str_contains(strtolower($frame['product_name'] ?? ''), $term)
                || str_contains(strtolower($frame['design_name']  ?? ''), $term)
                || str_contains(strtolower($frame['type_name']    ?? ''), $term)
                || str_contains(strtolower($frame['color_name']   ?? ''), $term);
        }));
    }

    public function getFrameById(int $id): ?array
    {
        return $this->repo->getById($id);
    }

    public function addToCart(int $customerId, int $productId, int $quantity): array
    {
        if ($quantity < 1) {
            return ['success' => false, 'message' => 'Quantity must be at least 1.'];
        }

        $product = $this->repo->getById($productId);

        if (!$product) {
            return ['success' => false, 'message' => 'Product not found.'];
        }

        if ((int)$product['stock'] < $quantity) {
            return [
                'success' => false,
                'message' => 'Only ' . $product['stock'] . ' unit(s) left in stock.',
            ];
        }

        $unitPrice = (float)$product['product_price'];
        $ok        = $this->repo->addToCart($customerId, $productId, $quantity, $unitPrice);

        return $ok
            ? ['success' => true,  'message' => 'Added to cart successfully!']
            : ['success' => false, 'message' => 'Failed to add to cart. Please try again.'];
    }

    public function getMountTypes(): array
    {
        return $this->repo->getMountTypes();
    }

    public function buildBuyNowPayload(int $productId, int $quantity): array
    {
        if ($quantity < 1) {
            return ['success' => false, 'message' => 'Quantity must be at least 1.'];
        }

        $product = $this->repo->getById($productId);

        if (!$product) {
            return ['success' => false, 'message' => 'Product not found.'];
        }

        if ((int)$product['stock'] < $quantity) {
            return [
                'success' => false,
                'message' => 'Only ' . $product['stock'] . ' unit(s) left in stock.',
            ];
        }

        $unitPrice  = (float)$product['product_price'];
        $totalPrice = $unitPrice * $quantity;

        return [
            'success'      => true,
            'payload'      => [
                'item_type'    => 'READY_MADE',
                'r_product_id' => $productId,
                'product_name' => $product['product_name'],
                'quantity'     => $quantity,
                'unit_price'   => $unitPrice,
                'total_price'  => $totalPrice,
            ],
        ];
    }
}