<?php

namespace App\Filament\Resources\UserMerchantPaymentTransactions\Pages;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UserMerchantAccountEntry;
use App\Models\UserMerchantAccountStatement;
use App\Models\UserMerchant;
use App\Filament\Resources\UserMerchantPaymentTransactions\UserMerchantPaymentTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditUserMerchantPaymentTransaction extends EditRecord
{
    protected static string $resource = UserMerchantPaymentTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('عرض'),
            Actions\DeleteAction::make()
                ->label('حذف'),
        ];
    }

    protected function beforeSave(): void
    {
        // Get original and new values
        $oldStatus = $this->record->getOriginal('status');
        $newStatus = $this->data['status'];
        $oldAmount = $this->record->getOriginal('amount');
        $newAmount = $this->data['amount'];
        
        // Only validate if status is changing to completed or amount is changing
        if (($oldStatus !== 'completed' && $newStatus === 'completed') || ($oldAmount != $newAmount && $newStatus === 'completed')) {
            $merchantId = $this->data['user_merchant_id'];
            $paymentAmount = $newAmount;

            // Get merchant current balance
            $merchant = UserMerchant::find($merchantId);

            if (!$merchant) {
                Notification::make()
                    ->title('خطأ')
                    ->body('التاجر غير موجود')
                    ->danger()
                    ->send();
                
                $this->halt();
            }

            $currentBalance = $merchant->balance ?? 0;
            
            // If amount changed, calculate the difference impact
            if ($oldStatus === 'completed' && $oldAmount != $newAmount) {
                // Add back the old amount and check new amount
                $currentBalance = $currentBalance + $oldAmount;
            }

            // Check if payment amount exceeds merchant balance
            if ($paymentAmount > $currentBalance) {
                Notification::make()
                    ->title('خطأ في المبلغ')
                    ->body("مبلغ الدفع ($" . number_format($paymentAmount, 2) . ") أكبر من رصيد التاجر الحالي ($" . number_format($currentBalance, 2) . ")")
                    ->danger()
                    ->persistent()
                    ->send();
                
                $this->halt();
            }

            // Additional validation: payment amount must be positive
            if ($paymentAmount <= 0) {
                Notification::make()
                    ->title('خطأ في المبلغ')
                    ->body('مبلغ الدفع يجب أن يكون أكبر من صفر')
                    ->danger()
                    ->send();
                
                $this->halt();
            }
        }
    }

    protected function afterSave(): void
    {
        DB::transaction(function () {
            // Check if status changed to completed and no account entry exists
            $oldStatus = $this->record->getOriginal('status');
            $newStatus = $this->record->status;
            
            if ($oldStatus !== 'completed' && $newStatus === 'completed') {
                // Check if account entry already exists for this transaction
                $entryExists = UserMerchantAccountEntry::where('reference_type', \App\Models\UserMerchantPaymentTransaction::class)
                    ->where('reference_id', $this->record->id)
                    ->exists();
                
                if (!$entryExists) {
                    $this->createAccountEntry();
                    
                    // Check if account statement entry exists
                    $statementExists = UserMerchantAccountStatement::where('reference_type', \App\Models\UserMerchantPaymentTransaction::class)
                        ->where('reference_id', $this->record->id)
                        ->exists();
                    
                    if (!$statementExists) {
                        $this->createAccountStatement();
                    }
                }
            }
        });
    }

    /**
     * Create an automatic account entry for the payment transaction
     */
    protected function createAccountEntry(): void
    {
        // Get the merchant
        $merchant = UserMerchant::find($this->record->user_merchant_id);
        
        if (!$merchant) {
            return;
        }

        // Generate entry number
        $lastEntry = UserMerchantAccountEntry::orderBy('id', 'desc')->first();
        $nextNumber = $lastEntry ? (int) $lastEntry->entry_number + 1 : 1;
        $entryNumber = str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

        // Calculate new balance (payment creates a credit - reduces merchant's receivable)
        $currentBalance = $merchant->balance ?? 0;
        $newBalance = $currentBalance - $this->record->amount;

        // Create the account entry
        UserMerchantAccountEntry::create([
            'user_id' => $this->record->user_id,
            'user_merchant_id' => $this->record->user_merchant_id,
            'entry_number' => $entryNumber,
            'entry_type' => 'credit',
            'amount' => $this->record->amount,
            'debit_amount' => 0,
            'credit_amount' => $this->record->amount,
            'description' => "قيد دفعة رقم {$this->record->transaction_number} بقيمة $" . number_format($this->record->amount, 2),
            'reference_type' => \App\Models\UserMerchantPaymentTransaction::class,
            'reference_id' => $this->record->id,
            'balance_after' => $newBalance,
            'entry_date' => $this->record->payment_date ?? now(),
            'created_by' => Auth::id(),
        ]);

        // Update merchant balance
        $merchant->update([
            'balance' => $newBalance,
        ]);
    }

    /**
     * Create an account statement entry for the payment transaction
     */
    protected function createAccountStatement(): void
    {
        // Get the merchant
        $merchant = UserMerchant::find($this->record->user_merchant_id);
        
        if (!$merchant) {
            return;
        }

        // Get the last statement balance for this merchant
        $lastStatement = UserMerchantAccountStatement::where('user_merchant_id', $this->record->user_merchant_id)
            ->orderBy('id', 'desc')
            ->first();
        
        $previousBalance = $lastStatement ? $lastStatement->balance : 0;
        $newBalance = $previousBalance - $this->record->amount;

        // Create the account statement (credit reduces merchant's receivables)
        UserMerchantAccountStatement::create([
            'user_id' => $this->record->user_id,
            'user_merchant_id' => $this->record->user_merchant_id,
            'debit_amount' => 0,
            'credit_amount' => $this->record->amount,
            'balance' => $newBalance,
            'transaction_type' => 'payment',
            'reference_type' => \App\Models\UserMerchantPaymentTransaction::class,
            'reference_id' => $this->record->id,
            'description' => "دفعة رقم {$this->record->transaction_number} بقيمة $" . number_format($this->record->amount, 2),
            'transaction_date' => $this->record->payment_date ?? now(),
        ]);
    }
}
