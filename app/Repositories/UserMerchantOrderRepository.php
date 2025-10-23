<?php

namespace App\Repositories;

use App\Models\UserMerchantOrder;

class UserMerchantOrderRepository
{
    /**
     * Generate next order number for a user
     *
     * @param int $userId
     * @return string
     */
    public function generateOrderNumber(int $userId): string
    {
        $lastOrder = UserMerchantOrder::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastOrder ? (int) $lastOrder->order_number + 1 : 1;
        
        return str_pad($nextNumber, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Generate next order number for a merchant
     *
     * @param int $merchantId
     * @return string
     */
    public function generateOrderNumberForMerchant(int $merchantId): string
    {
        $lastOrder = UserMerchantOrder::where('user_merchant_id', $merchantId)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastOrder ? (int) $lastOrder->order_number + 1 : 1;
        
        return str_pad($nextNumber, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Find order by ID
     *
     * @param int $orderId
     * @return UserMerchantOrder|null
     */
    public function find(int $orderId): ?UserMerchantOrder
    {
        return UserMerchantOrder::find($orderId);
    }

    /**
     * Get user's orders
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserOrders(int $userId)
    {
        return UserMerchantOrder::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get merchant's orders
     *
     * @param int $merchantId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMerchantOrders(int $merchantId)
    {
        return UserMerchantOrder::where('user_merchant_id', $merchantId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

