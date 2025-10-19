<?php

namespace App\Repositories;

use App\Models\UserMerchant;

class UserMerchantRepository
{
    /**
     * Find merchant by ID
     *
     * @param int $merchantId
     * @return UserMerchant|null
     */
    public function find(int $merchantId): ?UserMerchant
    {
        return UserMerchant::find($merchantId);
    }

    /**
     * Get merchant current balance
     *
     * @param int $merchantId
     * @return float
     */
    public function getBalance(int $merchantId): float
    {
        $merchant = $this->find($merchantId);
        return $merchant?->balance ?? 0;
    }

    /**
     * Check if merchant exists
     *
     * @param int $merchantId
     * @return bool
     */
    public function exists(int $merchantId): bool
    {
        return UserMerchant::where('id', $merchantId)->exists();
    }

    /**
     * Update merchant balance
     *
     * @param int $merchantId
     * @param float $newBalance
     * @return bool
     */
    public function updateBalance(int $merchantId, float $newBalance): bool
    {
        $merchant = $this->find($merchantId);
        
        if (!$merchant) {
            return false;
        }

        return $merchant->update(['balance' => $newBalance]);
    }

    /**
     * Validate if payment amount is valid for merchant
     *
     * @param int $merchantId
     * @param float $paymentAmount
     * @return array{valid: bool, error: string|null, currentBalance: float}
     */
    public function validatePaymentAmount(int $merchantId, float $paymentAmount): array
    {
        $merchant = $this->find($merchantId);

        if (!$merchant) {
            return [
                'valid' => false,
                'error' => 'التاجر غير موجود',
                'currentBalance' => 0
            ];
        }

        $currentBalance = $merchant->balance ?? 0;

        if ($paymentAmount <= 0) {
            return [
                'valid' => false,
                'error' => 'مبلغ الدفع يجب أن يكون أكبر من صفر',
                'currentBalance' => $currentBalance
            ];
        }

        if ($paymentAmount > $currentBalance) {
            return [
                'valid' => false,
                'error' => "مبلغ الدفع ($" . number_format($paymentAmount, 2) . ") أكبر من رصيد التاجر الحالي ($" . number_format($currentBalance, 2) . ")",
                'currentBalance' => $currentBalance
            ];
        }

        return [
            'valid' => true,
            'error' => null,
            'currentBalance' => $currentBalance
        ];
    }
}

