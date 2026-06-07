<?php

namespace App\Filament\Resources\UserMerchantPaymentTransactions\Pages;

use App\Enums\PaymentTransactionStatus;
use App\Filament\Resources\UserMerchantPaymentTransactions\UserMerchantPaymentTransactionResource;
use App\Models\UserMerchant;
use App\Services\PaymentTransactionService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

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
        $oldStatus = $this->record->getOriginal('status');
        $newStatus = $this->data['status'];
        $oldAmount = (float) $this->record->getOriginal('amount');
        $newAmount = (float) $this->data['amount'];

        if (($oldStatus !== PaymentTransactionStatus::COMPLETED->value && $newStatus === PaymentTransactionStatus::COMPLETED->value)
            || ($oldAmount != $newAmount && $newStatus === PaymentTransactionStatus::COMPLETED->value)) {
            $merchant = UserMerchant::find($this->data['user_merchant_id']);

            if (! $merchant) {
                Notification::make()
                    ->title('خطأ')
                    ->body('التاجر غير موجود')
                    ->danger()
                    ->send();

                $this->halt();
            }

            $currentBalance = (float) ($merchant->balance ?? 0);

            if ($oldStatus === PaymentTransactionStatus::COMPLETED->value && $oldAmount != $newAmount) {
                $currentBalance += $oldAmount;
            }

            if ($newAmount > $currentBalance) {
                Notification::make()
                    ->title('خطأ في المبلغ')
                    ->body('مبلغ الدفع ($'.number_format($newAmount, 2).') أكبر من رصيد التاجر الحالي ($'.number_format($currentBalance, 2).')')
                    ->danger()
                    ->persistent()
                    ->send();

                $this->halt();
            }

            if ($newAmount <= 0) {
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
        $oldStatus = $this->record->getOriginal('status');
        $newStatus = $this->record->status;

        if ($oldStatus !== PaymentTransactionStatus::COMPLETED->value && $newStatus === PaymentTransactionStatus::COMPLETED) {
            app(PaymentTransactionService::class)->processPayment($this->record);
        }
    }
}
