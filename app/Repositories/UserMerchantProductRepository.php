<?php

namespace App\Repositories;

use App\Models\UserMerchantProduct;

class UserMerchantProductRepository
{
    /**
     * Find product by ID
     *
     * @param int $productId
     * @return UserMerchantProduct|null
     */
    public function find(int $productId): ?UserMerchantProduct
    {
        return UserMerchantProduct::find($productId);
    }

    /**
     * Update product price
     *
     * @param int $productId
     * @param float $newPrice
     * @return bool
     */
    public function updatePrice(int $productId, float $newPrice): bool
    {
        $product = $this->find($productId);

        if (!$product) {
            return false;
        }

        return $product->update(['price' => $newPrice]);
    }

    /**
     * Update product price if it has changed
     *
     * @param int $productId
     * @param float $newPrice
     * @return bool Returns true if price was updated, false otherwise
     */
    public function updatePriceIfChanged(int $productId, float $newPrice): bool
    {
        $product = $this->find($productId);

        if (!$product) {
            return false;
        }

        // Compare prices (convert to float to handle decimal comparison)
        if ((float) $product->price !== (float) $newPrice) {
            return $product->update(['price' => $newPrice]);
        }

        return false;
    }

    /**
     * Get merchant's products
     *
     * @param int $merchantId
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMerchantProducts(int $merchantId, bool $activeOnly = false)
    {
        $query = UserMerchantProduct::where('user_merchant_id', $merchantId);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * Check if product price has changed
     *
     * @param int $productId
     * @param float $newPrice
     * @return bool|null Returns true if changed, false if same, null if product not found
     */
    public function hasPriceChanged(int $productId, float $newPrice): ?bool
    {
        $product = $this->find($productId);

        if (!$product) {
            return null;
        }

        return (float) $product->price !== (float) $newPrice;
    }

    /**
     * Get product current price
     *
     * @param int $productId
     * @return float|null
     */
    public function getPrice(int $productId): ?float
    {
        $product = $this->find($productId);
        
        return $product ? (float) $product->price : null;
    }
}

