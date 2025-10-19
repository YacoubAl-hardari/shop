<?php

namespace App\Services;

use App\Models\UserMerchantPaymentTransaction;
use App\Enums\PaymentTransactionStatus;
use App\Repositories\UserMerchantRepository;
use App\Repositories\UserMerchantAccountEntryRepository;
use App\Repositories\UserMerchantAccountStatementRepository;
use Illuminate\Support\Facades\DB;

class PaymentTransactionService
{
    public function __construct(
        protected UserMerchantRepository $merchantRepository,
        protected UserMerchantAccountEntryRepository $accountEntryRepository,
        protected UserMerchantAccountStatementRepository $accountStatementRepository
    ) {}

    /**
     * Validate payment transaction
     *
     * @param int $merchantId
     * @param float $paymentAmount
     * @return array{valid: bool, error: string|null, currentBalance: float}
     */
    public function validatePayment(int $merchantId, float $paymentAmount): array
    {
        return $this->merchantRepository->validatePaymentAmount($merchantId, $paymentAmount);
    }

    /**
     * Process payment transaction (create entry and update statement)
     *
     * @param UserMerchantPaymentTransaction $transaction
     * @return void
     */
    public function processPayment(UserMerchantPaymentTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // Only process if transaction is completed
            if ($transaction->status !== PaymentTransactionStatus::COMPLETED) {
                return;
            }

            // Get current balance
            $currentBalance = $this->merchantRepository->getBalance($transaction->user_merchant_id);
            
            // Calculate new balance (payment reduces merchant's receivable)
            $newBalance = $currentBalance - $transaction->amount;

            // Create account entry
            $this->accountEntryRepository->createPaymentEntry($transaction, $newBalance);

            // Update merchant balance
            $this->merchantRepository->updateBalance($transaction->user_merchant_id, $newBalance);

            // Update account statement
            $this->accountStatementRepository->updateForPayment(
                $transaction->user_merchant_id,
                $transaction->amount
            );
        });
    }

    /**
     * Check if merchant exists
     *
     * @param int $merchantId
     * @return bool
     */
    public function merchantExists(int $merchantId): bool
    {
        return $this->merchantRepository->exists($merchantId);
    }
}

