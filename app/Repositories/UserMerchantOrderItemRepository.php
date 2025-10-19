<?php

namespace App\Repositories;

use App\Models\UserMerchantOrderItem;

class UserMerchantOrderItemRepository
{
    public function __construct(
        protected UserMerchantProductRepository $productRepository
    ) {}
    /**
     * Create order items in bulk
     *
     * @param int $orderId
     * @param array $items
     * @return void
     */
    public function createOrderItems(int $orderId, array $items): void
    {
        foreach ($items as $itemData) {
            // Create order item
            UserMerchantOrderItem::create([
                'user_merchant_order_id' => $orderId,
                'user_merchant_product_id' => $itemData['user_merchant_product_id'],
                'unit' => $itemData['unit'],
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'total_price' => $itemData['total_price'],
            ]);

            // Update product price if it has changed
            $this->updateProductPriceIfChanged(
                $itemData['user_merchant_product_id'],
                $itemData['price']
            );
        }
    }

    /**
     * Update product price if it differs from the order item price
     *
     * @param int $productId
     * @param float $newPrice
     * @return bool
     */
    protected function updateProductPriceIfChanged(int $productId, float $newPrice): bool
    {
        return $this->productRepository->updatePriceIfChanged($productId, $newPrice);
    }

    /**
     * Get order items for an order
     *
     * @param int $orderId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOrderItems(int $orderId)
    {
        return UserMerchantOrderItem::where('user_merchant_order_id', $orderId)
            ->get();
    }

    /**
     * Calculate total price from items
     *
     * @param array $items
     * @return float
     */
    public function calculateTotalPrice(array $items): float
    {
        return collect($items)->sum('total_price');
    }

    /**
     * Update product price (can be called independently)
     *
     * @param int $productId
     * @param float $newPrice
     * @return bool
     */
    public function updateProductPrice(int $productId, float $newPrice): bool
    {
        return $this->updateProductPriceIfChanged($productId, $newPrice);
    }

    /**
     * Get the price difference between product and order item
     *
     * @param int $productId
     * @param float $orderPrice
     * @return float|null Returns the difference or null if product not found
     */
    public function getPriceDifference(int $productId, float $orderPrice): ?float
    {
        $productPrice = $this->productRepository->getPrice($productId);

        if ($productPrice === null) {
            return null;
        }

        return (float) $orderPrice - $productPrice;
    }
}

