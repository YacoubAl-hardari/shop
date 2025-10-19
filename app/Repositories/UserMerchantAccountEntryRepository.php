<?php

namespace App\Repositories;

use App\Models\UserMerchantAccountEntry;
use App\Models\UserMerchantPaymentTransaction;
use Illuminate\Support\Facades\Auth;

class UserMerchantAccountEntryRepository
{
    /**
     * Generate next entry number
     *
     * @return string
     */
    public function generateEntryNumber(): string
    {
        $lastEntry = UserMerchantAccountEntry::orderBy('id', 'desc')->first();
        $nextNumber = $lastEntry ? (int) $lastEntry->entry_number + 1 : 1;
        
        return str_pad($nextNumber, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Create account entry for payment transaction
     *
     * @param UserMerchantPaymentTransaction $transaction
     * @param float $balanceAfter
     * @return UserMerchantAccountEntry
     */
    public function createPaymentEntry(
        UserMerchantPaymentTransaction $transaction,
        float $balanceAfter
    ): UserMerchantAccountEntry {
        $entryNumber = $this->generateEntryNumber();

        return UserMerchantAccountEntry::create([
            'user_id' => $transaction->user_id,
            'user_merchant_id' => $transaction->user_merchant_id,
            'entry_number' => $entryNumber,
            'entry_type' => 'debit',
            'amount' => $transaction->amount,
            'debit_amount' => $transaction->amount,
            'credit_amount' => 0,
            'description' => "قيد دفعة رقم {$transaction->transaction_number} بقيمة $" . number_format($transaction->amount, 2),
            'reference_type' => UserMerchantPaymentTransaction::class,
            'reference_id' => $transaction->id,
            'balance_after' => $balanceAfter,
            'entry_date' => $transaction->payment_date ?? now(),
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Get merchant's account entries
     *
     * @param int $merchantId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMerchantEntries(int $merchantId)
    {
        return UserMerchantAccountEntry::where('user_merchant_id', $merchantId)
            ->orderBy('entry_date', 'desc')
            ->get();
    }

    /**
     * Create account entry for order
     *
     * @param \App\Models\UserMerchantOrder $order
     * @param float $balanceAfter
     * @return UserMerchantAccountEntry
     */
    public function createOrderEntry(
        \App\Models\UserMerchantOrder $order,
        float $balanceAfter
    ): UserMerchantAccountEntry {
        $entryNumber = $this->generateEntryNumber();

        return UserMerchantAccountEntry::create([
            'user_id' => $order->user_id,
            'user_merchant_id' => $order->user_merchant_id,
            'entry_number' => $entryNumber,
            'entry_type' => 'credit',
            'amount' => $order->total_price,
            'debit_amount' => 0,
            'credit_amount' => $order->total_price,
            'description' => "قيد طلب رقم {$order->order_number} بقيمة $" . number_format($order->total_price, 2),
            'reference_type' => \App\Models\UserMerchantOrder::class,
            'reference_id' => $order->id,
            'balance_after' => $balanceAfter,
            'entry_date' => now(),
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Create a new account entry
     *
     * @param array $data
     * @return UserMerchantAccountEntry
     */
    public function create(array $data): UserMerchantAccountEntry
    {
        return UserMerchantAccountEntry::create($data);
    }
}

