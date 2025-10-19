<?php

namespace App\Repositories;

use App\Models\UserMerchant;
use App\Models\UserMerchantAccountStatement;

class UserMerchantAccountStatementRepository
{
    /**
     * Create an opening account statement for a merchant
     *
     * @param UserMerchant $merchant
     * @return UserMerchantAccountStatement|null
     */
    public function createOpeningStatement(UserMerchant $merchant): ?UserMerchantAccountStatement
    {
        // Check if merchant already has an account statement
        if ($this->hasExistingStatement($merchant->id)) {
            return null;
        }

        $openingBalance = $merchant->balance ?? 0;

        return UserMerchantAccountStatement::create([
            'user_id' => $merchant->user_id,
            'user_merchant_id' => $merchant->id,
            'debit_amount' => $openingBalance > 0 ? $openingBalance : 0,
            'credit_amount' => $openingBalance < 0 ? abs($openingBalance) : 0,
            'balance' => $openingBalance,
            'transaction_type' => 'adjustment',
            'reference_type' => null,
            'reference_id' => null,
            'description' => "رصيد افتتاحي للتاجر {$merchant->name}",
            'transaction_date' => now(),
        ]);
    }

    /**
     * Check if merchant has existing account statements
     *
     * @param int $merchantId
     * @return bool
     */
    public function hasExistingStatement(int $merchantId): bool
    {
        return UserMerchantAccountStatement::where('user_merchant_id', $merchantId)
            ->exists();
    }

    /**
     * Get merchant's account statements
     *
     * @param int $merchantId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMerchantStatements(int $merchantId)
    {
        return UserMerchantAccountStatement::where('user_merchant_id', $merchantId)
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * Get merchant's current balance from last statement
     *
     * @param int $merchantId
     * @return float
     */
    public function getCurrentBalance(int $merchantId): float
    {
        $lastStatement = UserMerchantAccountStatement::where('user_merchant_id', $merchantId)
            ->orderBy('transaction_date', 'desc')
            ->first();

        return $lastStatement?->balance ?? 0;
    }

    /**
     * Create a new account statement
     *
     * @param array $data
     * @return UserMerchantAccountStatement
     */
    public function create(array $data): UserMerchantAccountStatement
    {
        return UserMerchantAccountStatement::create($data);
    }

    /**
     * Update statement for payment transaction
     *
     * @param int $merchantId
     * @param float $paymentAmount
     * @return bool
     */
    public function updateForPayment(int $merchantId, float $paymentAmount): bool
    {
        $lastStatement = UserMerchantAccountStatement::where('user_merchant_id', $merchantId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastStatement) {
            return false;
        }

        return $lastStatement->update([
            'balance' => $lastStatement->balance - $paymentAmount,
            'debit_amount' => $lastStatement->debit_amount + $paymentAmount,
        ]);
    }

    /**
     * Get latest statement for merchant
     *
     * @param int $merchantId
     * @return UserMerchantAccountStatement|null
     */
    public function getLatestStatement(int $merchantId): ?UserMerchantAccountStatement
    {
        return UserMerchantAccountStatement::where('user_merchant_id', $merchantId)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Update statement for order transaction
     *
     * @param int $merchantId
     * @param float $orderAmount
     * @return bool
     */
    public function updateForOrder(int $merchantId, float $orderAmount): bool
    {
        $lastStatement = $this->getLatestStatement($merchantId);

        if (!$lastStatement) {
            return false;
        }

        return $lastStatement->update([
            'balance' => $lastStatement->balance + $orderAmount,
            'credit_amount' => $lastStatement->credit_amount + $orderAmount,
        ]);
    }
}

